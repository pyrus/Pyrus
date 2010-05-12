--TEST--
Validate_PECL::validateName(), failure, package name != providesextension
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$pf = new \PEAR2\Pyrus\PackageFile\v2;
$pf->name = 'testing2';
$pf->providesextension = 'testing';
$pf->type = 'extsrc';
$pf->channel = 'pecl.php.net';
$pf->version['release'] = '1.0.0RC1';
$pf->stability['release'] = 'beta';
$pf->summary = 'hi';
$pf->description = 'hi';
$pf->date = date('Y-m-d');

$chan = new \PEAR2\Pyrus\ChannelFile\v1;
$chan->setValidationPackage('notfoo', '1.2');
$validate = new \PEAR2\Pyrus\Validator\PECL;
$validate->setPackageFile($pf);
$validate->setChannel($chan);

$test->assertEquals(true, $validate->validate(\PEAR2\Pyrus\Validate::PACKAGING), 'test 1');
$test->assertEquals(1, count($validate->getFailures()), 'failure count');
$test->assertEquals('Channel validator error: field "providesextension" - ' .
                    'package name "testing2" is different from extension name "testing"',
                    $validate->getFailures()->E_WARNING[0]->getMessage(), 'failure message');
?>
===DONE===
--EXPECT--
===DONE===