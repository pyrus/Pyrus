--TEST--
Registry: test listPackages, cascading registries
--FILE--
<?php
require __DIR__ . '/../setup.cascade.php.inc';
$dir = TESTDIR . DIRECTORY_SEPARATOR . 'testit' . DIRECTORY_SEPARATOR;
$reg2->replace($info);
$packages = $reg->listPackages('pear2.php.net', false);
sort($packages);
$test->assertEquals(array('PEAR2_SimpleChannelServer'), $packages, 'test cascade');
$packages = $reg->listPackages('pear2.php.net', true);
sort($packages);
$test->assertEquals(array(), $packages, 'test cascade onlyMain');

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../../clean.php.inc';
?>
--EXPECT--
===DONE===