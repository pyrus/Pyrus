<?php
require __DIR__ . '/../../setup.php.inc';
@mkdir(TESTDIR);
set_include_path(TESTDIR);
$c = \PEAR2\Pyrus\Config::singleton(TESTDIR, TESTDIR . '/plugins/pearconfig.xml');
$c->bin_dir = TESTDIR . '/bin';
restore_include_path();
$c->saveConfig();
require __DIR__ . '/../../../PackageFile_v2/setupFiles/setupPackageFile.php.inc';
$reg2 = new \PEAR2\Pyrus\Registry\Sqlite3(TESTDIR);
$reg = new \PEAR2\Pyrus\Registry\Pear1(TESTDIR);