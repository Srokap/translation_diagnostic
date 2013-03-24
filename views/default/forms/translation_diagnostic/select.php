<?php
elgg_load_js('translation_diagnostic');

$language = elgg_extract('language', $vars, elgg_get_config('site')->language);

echo '<p>';
echo '<label>' . elgg_echo('translation_diagnostic:language') . '</label> ';
echo elgg_view('input/dropdown', array(
	'name' => 'language',
	'value' => get_input('language', $language),
	'options_values' => get_installed_translations(),
));
echo '</p>';

echo '<p>';
echo '<label>' . elgg_echo('translation_diagnostic:disabled_plugins_only') . '</label> ';
echo elgg_view('input/dropdown', array(
	'name' => 'include_disabled_plugins',
	'value' => get_input('include_disabled_plugins', 0),
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	),
));
echo '</p>';

echo elgg_view('input/submit', array(
	'name' => 'submit',
	'value' => elgg_echo('search:go'),
));

