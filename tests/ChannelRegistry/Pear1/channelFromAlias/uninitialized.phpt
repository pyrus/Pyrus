--TEST--
\PEAR2\Pyrus\ChannelRegistry\Pear1::channelFromAlias, uninitialized registry
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$creg = new PEAR2\Pyrus\ChannelRegistry\Pear1(dirname(__DIR__) . '/testit/foo');
$test->assertEquals('pear.php.net', $creg->channelFromAlias('pear'), 'pear');
$test->assertEquals('pear2.php.net', $creg->channelFromAlias('pear2'), 'pear2');
$test->assertEquals('doc.php.net', $creg->channelFromAlias('phpdocs'), 'phpdocs');
$test->assertEquals('pecl.php.net', $creg->channelFromAlias('pecl'), 'pecl');
?>
===DONE===
--CLEAN--
<?php
$dir = dirname(__DIR__) . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===