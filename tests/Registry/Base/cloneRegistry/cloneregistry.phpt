--TEST--
Registry base: test cloneRegistry() for Pear1 -> Sqlite3 registry
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$dir = __DIR__ . DIRECTORY_SEPARATOR . 'testit' . DIRECTORY_SEPARATOR;
require __DIR__ . '/../../AllRegistries/listpackages/multiple.channels.template';


$packages = $reg2->listPackages('pear2.php.net');
sort($packages);
$test->assertEquals(array(), $packages, 'before pear2');
$packages = $reg2->listPackages('pear.php.net');
sort($packages);
$test->assertEquals(array(), $packages, 'before pear');

// $reg has 4 installed packages
$reg2->cloneRegistry($reg);

$packages = $reg2->listPackages('pear2.php.net');
sort($packages);
$test->assertEquals(array('HooHa', 'HooHa2', 'PEAR2_SimpleChannelServer'), $packages, 'after pear2');
$packages = $reg2->listPackages('pear.php.net');
sort($packages);
$test->assertEquals(array('HooHa2'), $packages, 'after pear');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===