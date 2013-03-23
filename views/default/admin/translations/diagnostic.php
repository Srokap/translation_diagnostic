<?php
$language = get_input('language');

$body = elgg_view_form('translation_diagnostic/select', array(
	'action' => '#',
), array(
	'language' => $language,
));

echo elgg_view_module('main', elgg_echo('translation_diagnostic:form'), $body);

echo '<br>';

$body = '';
$body .= elgg_view('graphics/ajax_loader', array(
	'id' => 'translation-diagnostic-loader'
));
$body .= '<div id="translation-diagnostic-result">';

if ($language) {
	$body .= elgg_view('translation_diagnostic/analysis', array(
		'language' => $language,
	));
} else {
	$body .= elgg_echo('translation_diagnostic:results:initial_stub');
}

$body .= '</div>';

echo elgg_view_module('main', elgg_echo('translation_diagnostic:results'), $body);
