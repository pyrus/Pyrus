--TEST--
Xml registry: uninstall failure, read-only
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
try {
    mkdir(__DIR__ . '/testit/.xmlregistry');
    $reg = new PEAR2\Pyrus\Registry\Xml(__DIR__ . '/testit', true);
    $reg->uninstall('foo', 'pear2.php.net');
} catch (PEAR2\Pyrus\Registry\Exception $e) {
    $test->assertEquals('Cannot install package, registry is read-only', $e->getMessage(), 'read-only');
}
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===