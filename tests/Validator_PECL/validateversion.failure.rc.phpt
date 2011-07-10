--TEST--
Validate_PECL::validateVersion(), failure, 1.0.0rc1
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$pf = new \Pyrus\PackageFile\v2;
$pf->name = 'testing2';
$pf->channel = 'pecl.php.net';
$pf->version['release'] = '1.0.0rc1';
$pf->stability['release'] = 'beta';
$pf->summary = 'hi';
$pf->description = 'hi';
$pf->date = date('Y-m-d');

$chan = new \Pyrus\ChannelFile\v1;
$chan->setValidationPackage('notfoo', '1.2');
$validate = new \Pyrus\Validator\PECL;
$validate->setPackageFile($pf);
$validate->setChannel($chan);

$test->assertEquals(false, $validate->validate(\Pyrus\Validate::PACKAGING), 'test 1');
$test->assertEquals(1, count($validate->getFailures()), 'failure count');
$test->assertEquals('Channel validator error: field "version" - Release Candidate versions must have ' .
                    'upper-case RC, not lower-case rc',
                    $validate->getFailures()->E_ERROR[0]->getMessage(), 'failure message');
?>
===DONE===
--EXPECT--
===DONE===