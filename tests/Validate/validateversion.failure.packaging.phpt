--TEST--
Validate::validateVersion(), failure, packaging rc instead of RC
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$pf = new PEAR2_Pyrus_PackageFile_v2;
$pf->name = 'testing2';
$pf->version['release'] = '1.0.0rc1';
$pf->stability['release'] = 'beta';
$pf->summary = 'hi';
$pf->description = 'hi';
$pf->date = '2009-05-10';

$chan = new PEAR2_Pyrus_ChannelFile_v1;
$chan->setValidationPackage('notfoo', '1.2');
$validate = new PEAR2_Pyrus_Validate;
$validate->setPackageFile($pf);
$validate->setChannel($chan);

$test->assertEquals(false, $validate->validate(PEAR2_Pyrus_Validate::PACKAGING), 'test 1');
$test->assertEquals(1, count($validate->getFailures()), 'failure count');
$test->assertEquals('Channel validator error: field "version" - Release Candidate versions ' .
                    'must have capital RC, not lower-case rc',
                    $validate->getFailures()->E_ERROR[0]->getMessage(), 'failure message');
?>
===DONE===
--EXPECT--
===DONE===