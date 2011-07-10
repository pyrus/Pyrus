<?php
require __DIR__ . '/../../setup.php.inc';
@mkdir(TESTDIR);
set_include_path(TESTDIR);
$c = \Pyrus\Config::singleton(TESTDIR, TESTDIR . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'pearconfig.xml');
$c->bin_dir = TESTDIR . DIRECTORY_SEPARATOR . 'bin';
restore_include_path();
$c->saveConfig();
include __DIR__ . '/../../AllRegistries/setupPackageFile.php.inc';
$reg = new \Pyrus\Registry\Sqlite3(TESTDIR);