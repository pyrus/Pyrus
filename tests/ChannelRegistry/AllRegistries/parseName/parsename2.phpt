--TEST--
\PEAR2\Pyrus\ChannelRegistry\Base::parseName() 2
--FILE--
<?php
require dirname(__DIR__) . '/../setup.php.inc';
$creg = new \PEAR2\Pyrus\ChannelRegistry(TESTDIR);
$chan = $creg['pear2.php.net']->toChannelFile();
$chan->name = 'pear2.php.net/foo';
$chan->alias = 'pfoo';
$creg[] = $chan;

$test->assertEquals(array(
    'package' => 'foo',
    'channel' => 'pear2.php.net/foo',
), $creg->parseName('pear2.php.net/foo/foo'), 'pear2.php.net/foo/foo');

$test->assertEquals(array(
    'package' => 'foo',
    'channel' => 'pear2.php.net/foo',
), $creg->parseName('channel://pear2.php.net/foo/foo'), 'channel://pear2.php.net/foo/foo');

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===