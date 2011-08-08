--TEST--
Validate::validateDate(), failure, not a real date
--FILE--
<?php
require __DIR__ . '/setup.php.inc';

$pf = new \Pyrus\PackageFile\v2;
$pf->name = 'testing2';
$pf->summary = 'hi';
$pf->description = 'hi';
$pf->date = 'xxxx-05-10';

$chan = new \Pyrus\ChannelFile\v1;
$chan->setValidationPackage('notfoo', '1.2');
$validate = new \Pyrus\Validate;
$validate->setPackageFile($pf);
$validate->setChannel($chan);

$test->assertEquals(false, $validate->validate(), 'test 1');
$test->assertEquals(1, count($validate->getFailures()), 'failure count');
$test->assertEquals('Channel validator error: field "date" - invalid release date "xxxx-05-10"',
                    $validate->getFailures()->E_ERROR[0]->getMessage(), 'failure message');
?>
===DONE===
--EXPECT--
===DONE===