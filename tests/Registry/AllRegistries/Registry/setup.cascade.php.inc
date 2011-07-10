<?php
require __DIR__ . '/../../setup.php.inc';
@mkdir(TESTDIR . DIRECTORY_SEPARATOR . 'testit');
@mkdir(TESTDIR . DIRECTORY_SEPARATOR . 'testit2');
$reg2 = new \Pyrus\Registry(TESTDIR . '/testit2');
set_include_path(TESTDIR . PATH_SEPARATOR .
                 __DIR__ . DIRECTORY_SEPARATOR . 'testit2');
$c = \Pyrus\Config::singleton(TESTDIR . '/testit', TESTDIR. '/testit/plugins/notused.xml');
$c->bin_dir = TESTDIR . '/testit/bin';
restore_include_path();
$c->saveConfig();
require __DIR__ . '/../../../PackageFile_v2/setupFiles/setupPackageFile.php.inc';
$reg = new \Pyrus\Registry(TESTDIR . '/testit');
$reg->setParent($reg2);
