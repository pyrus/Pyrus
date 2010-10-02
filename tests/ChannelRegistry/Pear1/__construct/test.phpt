--TEST--
\PEAR2\Pyrus\ChannelRegistry\Pear1::__construct packagingroot
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) === 'WIN') {
    die('skip cannot combine file paths like this on windows');
}
?>
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
PEAR2\Pyrus\Main::$options['packagingroot'] = dirname(__DIR__) . '/testit/poo';
$test->assertFileNotExists(dirname(__DIR__) . '/testit/poo' . dirname(__DIR__) . '/testit/foo', 'before');
$creg = new PEAR2\Pyrus\ChannelRegistry\Pear1(dirname(__DIR__) . '/testit/foo');
$test->assertFileExists(dirname(__DIR__) . '/testit/poo' . dirname(__DIR__) . '/testit/foo', 'after');
?>
===DONE===
--CLEAN--
<?php
$dir = dirname(__DIR__) . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===