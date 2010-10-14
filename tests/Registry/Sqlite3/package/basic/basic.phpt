--TEST--
Sqlite3 PackageFile v2: test basic package.xml properties
--FILE--
<?php
require __DIR__ . '/../../../setup.php.inc';
$c = getTestConfig();
require __DIR__ . '/../../../../PackageFile_v2/setupFiles/setupPackageFile.php.inc';
$reg = new \PEAR2\Pyrus\Registry\Sqlite3(TESTDIR);
$reg->replace($info); // use replace to preserve date/time
$reg = $reg->package[$package->channel . '/' . $package->name];

require __DIR__ . '/../../../AllRegistries/package/basic/basic.template';

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../../clean.php.inc';
?>
--EXPECT--
===DONE===