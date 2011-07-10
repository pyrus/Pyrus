<?php
require __DIR__ . '/../../setup.php.inc';
$c = getTestConfig();
$c->configSnapshot();
require __DIR__ . '/../../../PackageFile_v2/setupFiles/setupPackageFile.php.inc';
$reg = new \Pyrus\Registry\Xml(TESTDIR);
