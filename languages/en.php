<?php
$result = array(
	'admin:translations' => 'Translations',
	'admin:translations:diagnostic' => 'Translations diagnostic',
	'translation_diagnostic:form' => 'Options',
	'translation_diagnostic:results' => 'Results',
	'translation_diagnostic:results:initial_stub' => 'Select options and submit form above to perform analysis. May take significant time - please be patient.',
	'translation_diagnostic:error:request' => 'There was problem during request',
	'translation_diagnostic:language' => 'Language to analyze',
	'translation_diagnostic:disabled_plugins_only' => 'Include disabled plugins',
);
add_translation('en', $result);//let's be nice for 1.8 users
// return $result;//1.9 standard