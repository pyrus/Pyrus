--TEST--
Validate::validatePackageName(), package extends another package, failure
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$pf = new \PEAR2\Pyrus\PackageFile\v2;
$pf->name = 'testing2';
$pf->extends = 'testing';
$pf->version['release'] = '1.0.0';
$pf->stability['release'] = 'alpha';
$pf->date = '2009-05-10';

$validate = new \PEAR2\Pyrus\Validate;
$validate->setPackageFile($pf);
$test->assertEquals(true, $validate->validate(), 'test 1');
$test->assertEquals(2, count($validate->getFailures()), 'failure count');
$test->assertEquals('Channel validator error: field "package" - package testing2 extends package testing and so the name ' .
                    'should have a postfix equal to the major version like "testing1"',
                    $validate->getFailures()->E_WARNING[0]->getMessage(), 'failure message');
$test->assertEquals('Channel validator error: field "version" - first version number "1' .
                    '" must match the postfix of ' .
                    'package name "testing2" (2)',
                    $validate->getFailures()->E_WARNING[1]->getMessage(), 'failure message');
?>
===DONE===
--EXPECT--
===DONE===