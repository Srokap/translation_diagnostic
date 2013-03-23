<?php
class TranslationDiagnosticAnalyzer {
	
	/**
	 * @var array
	 */
	protected $stats;
	
	/**
	 * @var int
	 */
	protected $totalS;
	
	/**
	 * @var array
	 */
	protected $matchesSimple;
	
	/**
	 * @var int
	 */
	protected $totalC;
	
	/**
	 * @var array
	 */
	protected $matchesComplex;
	
	const T_PLUGINS_ALL = 0;
	const T_PLUGINS_ACTIVE = 1;
	const T_PLUGINS_INACTIVE = 2;
	
	/**
	 * @return array
	 */
	public static function getPluginIds($type) {
		$pluginsDirs = false;
		
		switch ($type) {
			case self::T_PLUGINS_INACTIVE:
				$pluginsDirs = elgg_get_plugin_ids_in_dir(elgg_get_config('path') . 'mod/');
				$actives = elgg_get_plugins('active');
				foreach ($actives as $plugin) {
					$pluginsDirs = array_diff($pluginsDirs, array($plugin->getID()));
				}
				break;
			case self::T_PLUGINS_ACTIVE:
				$pluginsDirs = elgg_get_plugins('active');
				foreach ($pluginsDirs as $key => $plugin) {
					$pluginsDirs[$key] = $plugin->getID();
				}
				break;
			case self::T_PLUGINS_ALL:
				$pluginsDirs = elgg_get_plugin_ids_in_dir(elgg_get_config('path') . 'mod/');
				break;
							
		}
		return $pluginsDirs;
	}
	
	/**
	 * @param Iterator $i
	 * @return array
	 */
	public function analyze(Iterator $i) {
		$this->stats = array();
		
		$cnt = 0;
		$this->totalS = 0;
		$this->totalC = 0;
		$this->matchesSimple = array();
		$this->matchesComplex = array();
		foreach ($i as $filePath => $val) {
			$contents = file_get_contents($filePath);
			if (strpos($contents, 'elgg_echo') !== false) {
				$result = $this->processFileContents($contents);
				$result = $this->splitSimpleMatches($result);
				list($s, $c) = $result;
				foreach ($s as $row) {
					$key = $row[1];
					if (!isset($this->stats[$key])) {
						$this->stats[$key] = 0;
					}
					$this->stats[$key] ++;
				}
				$this->totalS += count($s);
				$this->totalC += count($c);
				$this->matchesSimple = array_merge($this->matchesSimple, $s);
				$this->matchesComplex = array_merge($this->matchesComplex, $c);
				$cnt++;
			}
		}
		arsort($this->stats);// sort by frequency of use
		return array($this->stats, $this->totalS, $this->totalC);
	}
	
	/**
	 * @return string
	 */
	protected function printComplexMatchesReport() {
		$result = '';
		
		$result .= "Complex cases: \n";
		$result .= print_r($this->matchesComplex, true);
		
		return $result;
	}
	
	/**
	 * @param string $language
	 */
	public function ouptutReport($language, $skipInactive) {
		$result = '';
		
		$result .= "Language: " . $language . "\n";
		$result .= "Skipped inactive plugins: " . ($skipInactive ? 'yes' : 'no') . "\n";
		$result .= "Simple use cases: " . $this->totalS . "\n";
		$result .= "Complex use cases: " . $this->totalC . "\n";
		$result .= "\n";
		
		$this->forceRegisterAllTranslations($language, $skipInactive);
		$translations = elgg_get_config('translations');
		$defined = (array)array_keys($translations[$language]);
		$used = array_keys($this->stats);
		// sort($defined);
		// sort($used);
		
		$diff = array_diff($defined, $used);
		$missing = array_diff($used, $defined);
		$defAndUsed = array_intersect($used, $defined);
		sort($diff);
		sort($missing);
		
// 		$result .= $this->printComplexMatchesReport();
		
		$result .= "Translation definitions count: " . count($defined) . "\n";
		$result .= "Translations recognized as used: " . count($defAndUsed) . "\n";
		$result .= "Tokens recognized as used: " . count($used) . "\n";
		$result .= "Potentially unused tokens: " . count($diff) . "\n";
		$result .= "\n";
		
		// echo "Potentially unused:\n";
		// print_r($diff);
		$result .= "Translation tokens definitely missing definition (" . count($missing) . "):\n";
		$result .= print_r($missing, true);
		return $result;
	}
	
	/**
	 * Makes sure to register all possible translations despite cache
	 * @param unknown_type $language
	 */
	public function forceRegisterAllTranslations($language, $skipInactive = false) {
		$basePath = elgg_get_config('path');
		$langFiles = array(
			$basePath.'install/languages/' . $language . '.php',
			$basePath.'languages/' . $language . '.php',
		);
		$plugins = self::getPluginIds($skipInactive ? self::T_PLUGINS_ACTIVE : self::T_PLUGINS_ALL);
// 		var_dump($plugins);
		foreach ($plugins as $plugin) {
			$path = $basePath.'mod/' . $plugin . '/languages/' . $language . '.php';
			if (file_exists($path)) {
				$langFiles[] = $path;
			}
		}
		foreach ($langFiles as $path) {
			if (!file_exists($path)) {
				continue;
			}
			$result = include_once($path);
			if (!$result) {
				continue;
			} elseif (is_array($result)) {
				add_translation(basename($language, '.php'), $result);
			}
		}
	}
	
	/**
	 * Find elgg_echo invocations and extract together with parameters
	 * @param string $contents
	 * @return array
	 */
	public function processFileContents($contents) {
		$result = array();
		$phpTokens = token_get_all($contents);
		foreach ($phpTokens as $key => $row) {
			if (is_array($row)) {
				list($token, $content, $lineNumber) = $row;
				if ($token==T_STRING && strpos($content, 'elgg_echo')!==false) {
					$hit = array();
					$i = 1;
					$hit[] = $phpTokens[$key + 1];
					$key2 = $key + 2;
					while ($i>0 && count($hit)<200) {
						$t = $phpTokens[$key2];
						if ($t == '(') {
							$i++;
						} elseif($t == ')') {
							$i--;
						}
						$hit[] = $t;
						$key2++;
					}
					$result[] = array(
						$row,
						$hit
					);
				}
			}
		}
		unset($phpTokens);
		return $result;
	}
	
	/**
	 * @param array $hits
	 * @return array
	 */
	public function splitSimpleMatches($hits) {
		$simple = array();
		$complex = array();
		foreach ($hits as $hit) {
			list($call, $params) = $hit;
			if ($params[1][0] == T_CONSTANT_ENCAPSED_STRING && ($params[2] == ')' || $params[2] == ',')) {
				//one text parameter case regardless of the parameters to be printf'ed
				$simple[] = array($call, trim($params[1][1], '\'"'));
			} else {
				$complex[] = $hit;
			}
		}
		unset($hits);
		return array(
			$simple,
			$complex
		);
	}
}