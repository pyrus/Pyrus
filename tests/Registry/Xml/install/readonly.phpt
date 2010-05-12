--TEST--
Xml registry: install failure, read-only
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
try {
    mkdir(__DIR__ . '/testit/.xmlregistry');
    $package = new PEAR2\Pyrus\PackageFile(new PEAR2\Pyrus\PackageFile\v2);
    $reg = new PEAR2\Pyrus\Registry\Xml(__DIR__ . '/testit', true);
    $reg->install($package);
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