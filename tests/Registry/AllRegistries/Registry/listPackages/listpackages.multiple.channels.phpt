--TEST--
Registry: test listPackages, multiple packages, multiple channels
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$dir = TESTDIR . DIRECTORY_SEPARATOR;
require __DIR__ . '/../../listpackages/multiple.channels.template';
$packages = $reg->listPackages('pear2.php.net', true);
sort($packages);
$test->assertEquals(array('HooHa', 'HooHa2', 'PEAR2_SimpleChannelServer'), $packages, 'after pear2 2');

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../../clean.php.inc';
?>
--EXPECT--
===DONE===