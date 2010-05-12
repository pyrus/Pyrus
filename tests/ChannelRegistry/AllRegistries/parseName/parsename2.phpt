--TEST--
\PEAR2\Pyrus\ChannelRegistry\Base::parseName() 2
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/../setup.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$creg = new \PEAR2\Pyrus\ChannelRegistry(__DIR__ . '/testit');
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
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===