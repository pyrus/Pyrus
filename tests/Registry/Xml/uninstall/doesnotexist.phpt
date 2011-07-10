--TEST--
Xml registry: uninstall failure, package does not exist
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
try {
    $reg = new Pyrus\Registry\Xml(TESTDIR);
    $reg->uninstall('foo', 'pear2.php.net');
} catch (Pyrus\Registry\Exception $e) {
    $test->assertEquals('Cannot find registry for package pear2.php.net/foo', $e->getMessage(), 'does not exist');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===