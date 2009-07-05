--TEST--
Validate::validateVersion(), failure, alpha, version = 0.9.0RC1/beta1/b1/a1/alpha1
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$pf = new \pear2\Pyrus\PackageFile\v2;
$pf->name = 'testing';
$pf->version['release'] = '1.0.0a1';
$pf->stability['release'] = 'stable';
$pf->summary = 'hi';
$pf->description = 'hi';
$pf->date = '2009-05-10';

$chan = new \pear2\Pyrus\ChannelFile\v1;
$chan->setValidationPackage('notfoo', '1.2');

$validate = new \pear2\Pyrus\Validate;
$validate->setPackageFile($pf);
$validate->setChannel($chan);

$test->assertEquals(true, $validate->validate(), 'test 1');
$test->assertEquals(1, count($validate->getFailures()), 'failure count');
$test->assertEquals('Channel validator error: field "version" - version "1.0.0a1" ' .
                    'or any RC/beta/alpha version cannot be stable',
                    $validate->getFailures()->E_WARNING[0]->getMessage(), 'failure message 1');

$pf->version['release'] = '1.0.0b1';
$validate = new \pear2\Pyrus\Validate;
$validate->setPackageFile($pf);
$validate->setChannel($chan);

$test->assertEquals(true, $validate->validate(), 'test 2');
$test->assertEquals(1, count($validate->getFailures()), 'failure count');
$test->assertEquals('Channel validator error: field "version" - version "1.0.0b1" ' .
                    'or any RC/beta/alpha version cannot be stable',
                    $validate->getFailures()->E_WARNING[0]->getMessage(), 'failure message 2');

$pf->version['release'] = '1.0.0RC1';
$validate = new \pear2\Pyrus\Validate;
$validate->setPackageFile($pf);
$validate->setChannel($chan);

$test->assertEquals(true, $validate->validate(), 'test 3');
$test->assertEquals(1, count($validate->getFailures()), 'failure count');
$test->assertEquals('Channel validator error: field "version" - version "1.0.0RC1" ' .
                    'or any RC/beta/alpha version cannot be stable',
                    $validate->getFailures()->E_WARNING[0]->getMessage(), 'failure message 3');

$pf->version['release'] = '1.0.0beta1';
$validate = new \pear2\Pyrus\Validate;
$validate->setPackageFile($pf);
$validate->setChannel($chan);

$test->assertEquals(true, $validate->validate(), 'test 4');
$test->assertEquals(1, count($validate->getFailures()), 'failure count');
$test->assertEquals('Channel validator error: field "version" - version "1.0.0beta1" ' .
                    'or any RC/beta/alpha version cannot be stable',
                    $validate->getFailures()->E_WARNING[0]->getMessage(), 'failure message 4');

$pf->version['release'] = '1.0.0alpha1';
$validate = new \pear2\Pyrus\Validate;
$validate->setPackageFile($pf);
$validate->setChannel($chan);

$test->assertEquals(true, $validate->validate(), 'test 5');
$test->assertEquals(1, count($validate->getFailures()), 'failure count');
$test->assertEquals('Channel validator error: field "version" - version "1.0.0alpha1" ' .
                    'or any RC/beta/alpha version cannot be stable',
                    $validate->getFailures()->E_WARNING[0]->getMessage(), 'failure message 5');
?>
===DONE===
--EXPECT--
===DONE===