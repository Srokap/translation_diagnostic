<?php
class translation_diagnostic {
	
	static function init() {
		elgg_register_event_handler('pagesetup', 'system', array(__CLASS__, 'pagesetup'));
		
		elgg_register_ajax_view('graphics/ajax_loader');
		elgg_register_ajax_view('translation_diagnostic/analysis');
		
		elgg_register_js('translation_diagnostic', elgg_get_config('wwwroot') . 'mod/' 
			. __CLASS__ . '/views/default/js/translation_diagnostic.js');
	}
	
	static function pagesetup() {
		if (elgg_get_context() == 'admin') {
			elgg_register_menu_item('page', array(
				'name' => 'translations/diagnostic',
				'href' => 'admin/translations/diagnostic',
				'text' => elgg_echo('admin:translations:diagnostic'),
				'context' => 'admin',
				'section' => 'develop'
			));
		}
	}
	
	/**
	 * @param bool $skipInactive
	 * @return TranslationDiagnosticFileFilterIterator
	 */
	static function getPhpFilesIterator($skipInactive = false) {
		$i = new RecursiveDirectoryIterator(elgg_get_config('path'), RecursiveDirectoryIterator::SKIP_DOTS);
		$i = new RecursiveIteratorIterator($i, RecursiveIteratorIterator::LEAVES_ONLY);
		$i = new RegexIterator($i, "/.*\.php/");
		$i = new TranslationDiagnosticFileFilterIterator($i, $skipInactive);
		return $i;
	}
}