--TEST--
Validate::validatePackageName(), package extends another package
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$pf = new PEAR2_Pyrus_PackageFile_v2;
$pf->name = 'testing2';
$pf->extends = 'testing';
$pf->version['release'] = '2.0.0';

$chan = new PEAR2_Pyrus_ChannelFile_v1;
$chan->setValidationPackage('notfoo', '1.2');
$validate = new PEAR2_Pyrus_Validate;
$validate->setPackageFile($pf);
$validate->setChannel($chan);
$test->assertEquals(true, $validate->validatePackageName(), 'test 1');
?>
===DONE===
--EXPECT--
===DONE===