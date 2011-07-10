--TEST--
Xml registry: info failure, package does not exist
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
try {
    $reg = new Pyrus\Registry\Xml(TESTDIR);
    $reg->info('foo', 'pear2.php.net', 'version');
    throw new Exception('Expected exception');
} catch (Pyrus\Registry\Exception $e) {
    $test->assertEquals('Unknown package pear2.php.net/foo', $e->getMessage(), 'does not exist');
}
try {
    mkdir(TESTDIR . '/.xmlregistry/packages/pear2.php.net/foo', 0777, true);
    $reg->info('foo', 'pear2.php.net', 'version');
    throw new Exception('Expected exception');
} catch (Pyrus\Registry\Exception $e) {
    $test->assertEquals('Cannot find registry for package pear2.php.net/foo', $e->getMessage(), 'dir exists, no package.xml');
} 
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===