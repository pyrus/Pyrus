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
PEAR2\Pyrus\Main::$options['packagingroot'] = TESTDIR . '/poo';
$test->assertFileNotExists(TESTDIR . '/poo' . TESTDIR . '/foo', 'before');
$creg = new PEAR2\Pyrus\ChannelRegistry\Pear1(TESTDIR . '/foo');
$test->assertFileExists(TESTDIR . '/poo' . TESTDIR . '/foo', 'after');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===