--TEST--
Validate::validateVersion(), failure, invalid version
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$pf = new \Pyrus\PackageFile\v2;
$pf->name = 'testing2';
$pf->version['release'] = '2.0.0#';
$pf->summary = 'hi';
$pf->description = 'hi';
$pf->date = '2009-05-10';

$chan = new \Pyrus\ChannelFile\v1;
$chan->setValidationPackage('notfoo', '1.2');
$validate = new \Pyrus\Validate;
$validate->setPackageFile($pf);
$validate->setChannel($chan);

$test->assertEquals(false, $validate->validate(), 'test 1');
$test->assertEquals(1, count($validate->getFailures()), 'failure count');
$test->assertEquals('Channel validator error: field "version" - Invalid version number "2.0.0#"',
                    $validate->getFailures()->E_ERROR[0]->getMessage(), 'failure message');
?>
===DONE===
--EXPECT--
===DONE===