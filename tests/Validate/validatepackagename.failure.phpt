--TEST--
Validate::validatePackageName(), package extends another package
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$pf = new \PEAR2\Pyrus\PackageFile\v2;
$pf->name = 'testing2$';
$pf->version['release'] = '2.0.0';
$chan = new \PEAR2\Pyrus\ChannelFile\v1;
$chan->setValidationPackage('notfoo', '1.2');
$validate = new \PEAR2\Pyrus\Validate;
$validate->setPackageFile($pf);
$validate->setChannel($chan);
$test->assertEquals(false, $validate->validatePackageName(), 'test 1');
$test->assertEquals(1, count($validate->getFailures()), 'failure count');
$test->assertEquals('Channel validator error: field "package" - package name "testing2$" is invalid',
                    $validate->getFailures()->E_ERROR[0]->getMessage(), 'failure message');
?>
===DONE===
--EXPECT--
===DONE===