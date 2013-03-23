<?php
$language = elgg_extract('language', $vars, 'en');
$include_disabled_plugins = elgg_extract('include_disabled_plugins', $vars, false);
$skipInactive = !$include_disabled_plugins;

echo '<pre>';
$mt = microtime(true);

$analyzer = new TranslationDiagnosticAnalyzer();

$i = translation_diagnostic::getPhpFilesIterator($skipInactive);
$analyzer->analyze($i);

echo $analyzer->ouptutReport($language, $skipInactive);

echo sprintf("Time taken: %.4fs\n", microtime(true) - $mt);
echo '</pre>';
