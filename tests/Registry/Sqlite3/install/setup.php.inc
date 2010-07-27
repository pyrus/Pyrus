<?php
require dirname(__FILE__) . '/../../setup.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = \PEAR2\Pyrus\Config::singleton(__DIR__.DIRECTORY_SEPARATOR.'testit', __DIR__ . DIRECTORY_SEPARATOR . 'testit'.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'pearconfig.xml');
$c->bin_dir = __DIR__ . DIRECTORY_SEPARATOR . 'testit' . DIRECTORY_SEPARATOR . 'bin';
restore_include_path();
$c->saveConfig();
include __DIR__ . '/../../AllRegistries/setupPackageFile.php.inc';
$reg = new \PEAR2\Pyrus\Registry\Sqlite3(__DIR__.DIRECTORY_SEPARATOR.'testit');