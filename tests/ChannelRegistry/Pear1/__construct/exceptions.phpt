--TEST--
\pear2\Pyrus\ChannelRegistry\Pear1::__construct exceptions
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
try {
    $creg = new pear2\Pyrus\ChannelRegistry\Pear1(dirname(__DIR__) . '/testit/non/existing/dir', true);
} catch (\pear2\Pyrus\ChannelRegistry\Exception $e) {
    $test->assertEquals('Cannot initialize PEAR1 channel registry, directory does not exist and registry is read-only',
                        $e->getMessage(), 'readonly test');
}
try {
    file_put_contents(dirname(__DIR__) . '/testit/oops', 'hi');
    $creg = new pear2\Pyrus\ChannelRegistry\Pear1(dirname(__DIR__) . '/testit/oops');
} catch (\pear2\Pyrus\ChannelRegistry\Exception $e) {
    $test->assertEquals('Cannot initialize PEAR1 channel registry, channel directory could not be initialized',
                        $e->getMessage(), 'readonly test');
}
try {
    mkdir(dirname(__DIR__) . '/testit/foo');
    mkdir(dirname(__DIR__) . '/testit/foo/php');
    mkdir(dirname(__DIR__) . '/testit/foo/php/.channels');
    $creg = new pear2\Pyrus\ChannelRegistry\Pear1(dirname(__DIR__) . '/testit/foo', true);
} catch (\pear2\Pyrus\ChannelRegistry\Exception $e) {
    $test->assertEquals('Cannot initialize PEAR1 channel registry, aliasdirectory does not exist and registry is read-only',
                        $e->getMessage(), 'readonly test');
}
try {
    file_put_contents(dirname(__DIR__) . '/testit/foo/php/.channels/.alias', 'hi');
    $creg = new pear2\Pyrus\ChannelRegistry\Pear1(dirname(__DIR__) . '/testit/foo');
} catch (\pear2\Pyrus\ChannelRegistry\Exception $e) {
    $test->assertEquals('Cannot initialize PEAR1 channel registry, channel aliasdirectory could not be initialized',
                        $e->getMessage(), 'readonly test');
}
?>
===DONE===
--CLEAN--
<?php
$dir = dirname(__DIR__) . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===