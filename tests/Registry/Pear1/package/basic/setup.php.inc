<?php
require __DIR__ . '/../../../setup.php.inc';
$c = getTestConfig();
require __DIR__ . '/../../../../PackageFile_v2/setupFiles/setupPackageFile.php.inc';
$reg = new \PEAR2\Pyrus\Registry\Pear1(TESTDIR);
$reg->replace($info); // use replace to preserve date/time
$reg = $reg->package[$package->channel . '/' . $package->name];
