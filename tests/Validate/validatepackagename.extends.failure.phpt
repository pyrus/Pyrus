--TEST--
Validate::validatePackageName(), package extends another package, failure
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$pf = new PEAR2_Pyrus_PackageFile_v2;
$pf->name = 'testing2';
$pf->extends = 'testing';
$pf->version['release'] = '1.0.0';

$validate = new PEAR2_Pyrus_Validate;
$validate->setPackageFile($pf);
$test->assertEquals(true, $validate->validatePackageName(), 'test 1');
$test->assertEquals(1, count($validate->getFailures()), 'failure count');
$test->assertEquals('Channel validator error: field "package" - package testing2 extends package testing and so the name ' .
                    'should have a postfix equal to the major version like "testing1"',
                    $validate->getFailures()->E_WARNING[0]->getMessage(), 'failure message');

$validate = new PEAR2_Pyrus_Validate;
$pf->extends = 'oops';
$pf->version['release'] = '2.0.0';
$validate->setPackageFile($pf);
$test->assertEquals(true, $validate->validatePackageName(), 'test 1');
$test->assertEquals(1, count($validate->getFailures()), 'failure count');
$test->assertEquals('Channel validator error: field "package" - package testing2 extends package oops and so the name ' .
                    'must be an extension like "oops2"',
                    $validate->getFailures()->E_WARNING[0]->getMessage(), 'failure message oops');
?>
===DONE===
--EXPECT--
===DONE===