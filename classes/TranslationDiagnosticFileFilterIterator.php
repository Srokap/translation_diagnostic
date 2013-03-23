<?php
class TranslationDiagnosticFileFilterIterator extends FilterIterator {
	
	function __construct($iterator, $skipInactive = false) {
		if ($skipInactive) {
			$pluginsDirs = TranslationDiagnosticAnalyzer::getPluginIds(TranslationDiagnosticAnalyzer::T_PLUGINS_INACTIVE);
			foreach ($pluginsDirs as $pluginDir) {
				$this->blacklist[] = 'mod/' . $pluginDir . '/.*';
			}
// 			var_dump($this->blacklist);
		}
		
		parent::__construct($iterator);
	}
	
	protected $blacklist = array(
		'\..*',
		'cache/.*',
		'documentation/.*',
		'vendors/.*',
	);
	
	function accept () {
		//TODO blacklisting documentation, disabled plugins and installation script
		$file = $this->current();
		if ($file instanceof SplFileInfo) {
			$path = $file->getPathname();
			$path = str_replace('\\', '/', $path);
			$path = substr($path, strlen(elgg_get_config('path')));
			foreach ($this->blacklist as $pattern) {
				if (preg_match("#^$pattern$#", $path)) {
// 					var_dump($path);
					return false;
				}
			}
			return true;
		}
		return false;
	}
}