<?php
class translation_diagnostic {
	
	static function init() {
		elgg_register_event_handler('pagesetup', 'system', array(__CLASS__, 'pagesetup'));
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
	 * @return RegexIterator
	 */
	static function getPhpFilesIterator() {
		$i = new RecursiveDirectoryIterator(elgg_get_config('path'), RecursiveDirectoryIterator::SKIP_DOTS);
		$i = new RecursiveIteratorIterator($i, RecursiveIteratorIterator::LEAVES_ONLY);
		$i = new RegexIterator($i, "/.*\.php/");
		return $i;
	}
	
}