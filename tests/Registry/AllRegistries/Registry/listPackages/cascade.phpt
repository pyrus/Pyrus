--TEST--
Registry: test listPackages, cascading registries
--FILE--
<?php
require __DIR__ . '/../setup.cascade.php.inc';
$dir = __DIR__ . DIRECTORY_SEPARATOR . 'testit' . DIRECTORY_SEPARATOR;
$reg2->install($info);
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
$dir = dirname(__DIR__) . '/testit';
include __DIR__ . '/../../../../clean.php.inc';
$dir = dirname(__DIR__) . '/testit2';
include __DIR__ . '/../../../../clean.php.inc';
?>
--EXPECT--
===DONE===