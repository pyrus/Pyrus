<?php
require __DIR__ . '/../../setup.php.inc';
$c = getTestConfig();
require __DIR__ . '/../../../PackageFile_v2/setupFiles/setupPackageFile.php.inc';
$reg2 = new \Pyrus\Registry\Sqlite3(TESTDIR);
$reg = new \Pyrus\Registry\Pear1(TESTDIR);