--TEST--
Validate::validateVersion(), failure, alpha, version = 0.3.4
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$pf = new PEAR2_Pyrus_PackageFile_v2;
$pf->name = 'testing';
$pf->version['release'] = '0.3.4';
$pf->stability['release'] = 'alpha';
$pf->summary = 'hi';
$pf->description = 'hi';
$pf->date = '2009-05-10';

$chan = new PEAR2_Pyrus_ChannelFile_v1;
$chan->setValidationPackage('notfoo', '1.2');
$validate = new PEAR2_Pyrus_Validate;
$validate->setPackageFile($pf);
$validate->setChannel($chan);

$test->assertEquals(true, $validate->validate(), 'test 1');
$test->assertEquals(0, count($validate->getFailures()), 'failure count');
?>
===DONE===
--EXPECT--
===DONE===