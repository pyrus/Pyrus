--TEST--
Xml registry: install failure, read-only
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
try {
    mkdir(TESTDIR . '/.xmlregistry');
    $package = new Pyrus\PackageFile(new Pyrus\PackageFile\v2);
    $reg = new Pyrus\Registry\Xml(TESTDIR, true);
    $reg->install($package);
} catch (Pyrus\Registry\Exception $e) {
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