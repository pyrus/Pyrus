--TEST--
\Pyrus\ChannelRegistry\Pear1::__construct exceptions
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
try {
    $creg = new Pyrus\ChannelRegistry\Pear1(TESTDIR . '/non/existing/dir', true);
} catch (\Pyrus\ChannelRegistry\Exception $e) {
    $test->assertEquals('Cannot initialize PEAR1 channel registry, directory does not exist and registry is read-only',
                        $e->getMessage(), 'readonly test');
}
try {
    file_put_contents(TESTDIR . '/oops', 'hi');
    $creg = new Pyrus\ChannelRegistry\Pear1(TESTDIR . '/oops');
} catch (\Pyrus\ChannelRegistry\Exception $e) {
    $test->assertEquals('Cannot initialize PEAR1 channel registry, channel directory could not be initialized',
                        $e->getMessage(), 'readonly test');
}
try {
    mkdir(TESTDIR . '/foo');
    mkdir(TESTDIR . '/foo/php');
    mkdir(TESTDIR . '/foo/php/.channels');
    $creg = new Pyrus\ChannelRegistry\Pear1(TESTDIR . '/foo', true);
} catch (\Pyrus\ChannelRegistry\Exception $e) {
    $test->assertEquals('Cannot initialize PEAR1 channel registry, aliasdirectory does not exist and registry is read-only',
                        $e->getMessage(), 'readonly test');
}
try {
    file_put_contents(TESTDIR . '/foo/php/.channels/.alias', 'hi');
    $creg = new Pyrus\ChannelRegistry\Pear1(TESTDIR . '/foo');
} catch (\Pyrus\ChannelRegistry\Exception $e) {
    $test->assertEquals('Cannot initialize PEAR1 channel registry, channel aliasdirectory could not be initialized',
                        $e->getMessage(), 'readonly test');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===