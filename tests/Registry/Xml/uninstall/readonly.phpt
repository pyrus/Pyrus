--TEST--
Xml registry: uninstall failure, read-only
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
try {
    mkdir(TESTDIR . '/.xmlregistry');
    $reg = new PEAR2\Pyrus\Registry\Xml(TESTDIR, true);
    $reg->uninstall('foo', 'pear2.php.net');
    throw new Exception('Expected exception');
} catch (PEAR2\Pyrus\Registry\Exception $e) {
    $test->assertEquals('Cannot install package, registry is read-only', $e->getMessage(), 'read-only');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===