--TEST--
Validate::validateVersion(), failure, extends, alpha, version = 2.0.1
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$pf = new PEAR2_Pyrus_PackageFile_v2;
$pf->name = 'testing2';
$pf->extends = 'testing';
$pf->version['release'] = '2.0.1';
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
$test->assertEquals(1, count($validate->getFailures()), 'failure count');
$test->assertEquals('Channel validator error: field "version" - bugfix versions (2.x.y where y > 0) should not be alpha or beta',
                    $validate->getFailures()->E_WARNING[0]->getMessage(), 'failure message');
?>
===DONE===
--EXPECT--
===DONE===