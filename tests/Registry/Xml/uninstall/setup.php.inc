<?php
require __DIR__ . '/../../setup.php.inc';
@mkdir(TESTDIR);
set_include_path(TESTDIR);
$c = \PEAR2\Pyrus\Config::singleton(TESTDIR, TESTDIR . '/plugins/pearconfig.xml');
$c->bin_dir = TESTDIR . '/bin';
restore_include_path();
$c->saveConfig();
include __DIR__ . '/../../AllRegistries/setupPackageFile.php.inc';
$reg = new \PEAR2\Pyrus\Registry\Xml(TESTDIR);