<?php
require __DIR__ . '/../../setup.php.inc';
$c = getTestConfig();
include __DIR__ . '/../../AllRegistries/setupPackageFile.php.inc';
$reg = new \Pyrus\Registry\Xml(TESTDIR);