<?php
require dirname(__FILE__) . '/../../setup.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = \PEAR2\Pyrus\Config::singleton(__DIR__.'/testit', __DIR__ . '/testit/plugins/pearconfig.xml');
$c->bin_dir = __DIR__ . '/testit/bin';
restore_include_path();
$c->saveConfig();
include __DIR__ . '/../../AllRegistries/setupPackageFile.php.inc';
$reg = new \PEAR2\Pyrus\Registry\Xml(__DIR__.'/testit');