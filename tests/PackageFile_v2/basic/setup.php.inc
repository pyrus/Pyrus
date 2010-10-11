<?php
require __DIR__ . '/../setup.php.inc';
@mkdir(TESTDIR);
set_include_path(TESTDIR);
$c = \PEAR2\Pyrus\Config::singleton(TESTDIR, TESTDIR . '/plugins/pearconfig.xml');
$c->bin_dir = TESTDIR . '/bin';
restore_include_path();
$c->saveConfig();
require __DIR__ . '/../setupFiles/setupPackageFile.php.inc';
$reg = $package; // simulate registry package using packagefile