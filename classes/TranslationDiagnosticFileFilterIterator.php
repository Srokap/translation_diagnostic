<?php
class TranslationDiagnosticFileFilterIterator extends FilterIterator {
	
	function __construct($iterator, $skipInactive = false) {
		if ($skipInactive) {
			$pluginsDirs = elgg_get_plugin_ids_in_dir(elgg_get_config('path') . 'mod/');
			$actives = elgg_get_plugins('active');
			foreach ($actives as $plugin) {
				$pluginsDirs = array_diff($pluginsDirs, array($plugin->getID()));
			}
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