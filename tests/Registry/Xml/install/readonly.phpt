--TEST--
Xml registry: install failure, read-only
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
try {
    mkdir(TESTDIR . '/.xmlregistry');
    $package = new PEAR2\Pyrus\PackageFile(new PEAR2\Pyrus\PackageFile\v2);
    $reg = new PEAR2\Pyrus\Registry\Xml(TESTDIR, true);
    $reg->install($package);
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