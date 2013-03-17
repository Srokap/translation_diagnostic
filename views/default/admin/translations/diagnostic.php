<?php
echo '<pre>';
$i = translation_diagnostic::getPhpFilesIterator();
$mt = microtime(true);

function force_register_all_translations($language) {
	$basePath = elgg_get_config('path');
	$langFiles = array(
		$basePath.'install/languages/' . $language . '.php',
		$basePath.'languages/' . $language . '.php',
	);
	$plugins = elgg_get_plugin_ids_in_dir($basePath.'/mod/');
	// var_dump($plugins);
	foreach ($plugins as $plugin) {
		// var_dump($plugin);
		$path = $basePath.'mod/' . $plugin . '/languages/' . $language . '.php';
		if (file_exists($path)) {
			$langFiles[] = $path;
		}
	}
	foreach ($langFiles as $path) {
		$result = include_once($path);
		if (!$result) {
			continue;
		} elseif (is_array($result)) {
// 			var_dump($path);
			add_translation(basename($language, '.php'), $result);
		}
	}
}

/**
 * FInd elgg_echo invocations and extract together with parameters
 * @param string $contents
 * @return array
 */
function process_contents($contents) {
	$result = array();
	$phpTokens = token_get_all($contents);
	foreach ($phpTokens as $key => $row) {
		if (is_array($row)) {
			list($token, $content, $lineNumber) = $row;
// 			var_dump($row, $token==T_STRING, strpos($content, 'elgg_echo')!==false);
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
// 				echo '+';
			} 
// 			elseif(strpos($content, 'elgg_echo')!==false) {
// 				 var_dump('here', $row, $token, $content, $lineNumber);
// 			} elseif (strpos($content, 'APIException:APIAuthenticationFailed')!==false) {
// 				var_dump('after', token_name($phpTokens[$key-2][0]), $phpTokens[$key-2], $phpTokens[$key-1], $row);
// 			}
		}
	}
	unset($phpTokens);
	return $result;
}

function split_simple_matches($hits) {
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

// $result = process_contents(file_get_contents('D:/srokap/git/Srokap/Elgg\mod\web_services\lib\web_services.php'));
// var_dump($result);
// $result = split_simple_matches($result);
// var_dump($result);
// return;

$stats = array();

$cnt = 0;
$totalS = 0;
$totalC = 0;
foreach ($i as $filePath => $val) {
	$contents = file_get_contents($filePath);
	if (strpos($contents, 'elgg_echo') !== false) {
// 		echo "$filePath\n";
		$result = process_contents($contents);
		$result = split_simple_matches($result);
		list($s, $c) = $result;
		foreach ($s as $row) {
			$key = $row[1];
// 			if ($key == 'APIException:APIAuthenticationFailed') {
// 				var_dump('survived');
// 			}
			if (!isset($stats[$key])) {
				$stats[$key] = 0;
			}
			$stats[$key] ++;
		}
		$totalS += count($s);
		$totalC += count($c);
// 		var_dump(count($s), count($c));
		$cnt++;
// 		if ($cnt>40) {
// 			break;
// 		}
	}
}
var_dump($totalS, $totalC);
// var_dump(isset($stats['APIException:APIAuthenticationFailed']));
arsort($stats);
// var_dump(isset($stats['APIException:APIAuthenticationFailed']));
// var_dump($stats);

//unused translations
global $CONFIG;
//TODO registerALL possible languages, override cache
force_register_all_translations('en');
$defined = array_keys($CONFIG->translations["en"]);
$used = array_keys($stats);
// sort($defined);
// sort($used);

$diff = array_diff($defined, $used);
$missing = array_diff($used, $defined);
sort($diff);
sort($missing);
var_dump(count($defined), count($used), count($diff));
echo "Potentially unused:\n";
print_r($diff);
echo "Missing:\n";
print_r($missing);

echo sprintf("Time taken: %.4fs\n", microtime(true) - $mt);
echo '</pre>';
