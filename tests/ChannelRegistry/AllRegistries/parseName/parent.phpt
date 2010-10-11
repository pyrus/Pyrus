--TEST--
\PEAR2\Pyrus\ChannelRegistry::parseName() with parent
--FILE--
<?php
require dirname(__DIR__) . '/../setup.php.inc';
$creg = new \PEAR2\Pyrus\ChannelRegistry(TESTDIR);
$cregp = new \PEAR2\Pyrus\ChannelRegistry(TESTDIR . '/blahblah');
$chan = $cregp['pear2.php.net']->toChannelFile();
$chan->name = 'boo.example.com';
$chan->alias = 'boo';
$cregp[] = $chan;
$creg->setParent($cregp);

$test->assertEquals(array(
    'package' => 'foo',
    'channel' => 'boo.example.com',
), $creg->parseName('boo.example.com/foo'), 'boo.example.com/foo');

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===