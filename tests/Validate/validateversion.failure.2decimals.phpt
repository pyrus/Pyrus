--TEST--
Validate::validateVersion(), failure, version is X.Y instead of X.Y.Z
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$pf = new \Pyrus\PackageFile\v2;
$pf->name = 'testing';
$pf->version['release'] = '2.0';
$pf->summary = 'hi';
$pf->description = 'hi';
$pf->date = '2009-05-10';

$chan = new \Pyrus\ChannelFile\v1;
$chan->setValidationPackage('notfoo', '1.2');
$validate = new \Pyrus\Validate;
$validate->setPackageFile($pf);
$validate->setChannel($chan);

$test->assertEquals(true, $validate->validate(), 'test 1');
$test->assertEquals(1, count($validate->getFailures()), 'failure count');
$test->assertEquals('Channel validator error: field "version" - A version number should have 3 decimals (x.y.z)',
                    $validate->getFailures()->E_WARNING[0]->getMessage(), 'failure message');
?>
===DONE===
--EXPECT--
===DONE===