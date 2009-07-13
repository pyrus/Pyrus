--TEST--
\pear2\Pyrus\ChannelRegistry::parsedNameToString() basic test
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/../setup.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$creg = new \pear2\Pyrus\ChannelRegistry(__DIR__ . '/testit');
$cregp = new \pear2\Pyrus\ChannelRegistry(__DIR__ . '/testit/blahblah');
$chan = $cregp['pear2.php.net']->toChannelFile();
$chan->name = 'boo.example.com';
$chan->alias = 'boo';
$cregp[] = $chan;
$creg->setParent($cregp);

$test->assertEquals(array(
    'package' => 'foo',
    'channel' => 'boo.example.com',
), $creg->parseName('boo.example.com/foo'), 'boo.example.com/foo');

$test->assertEquals('channel://boo.example.com/foo',
                    $creg->parsedNameToString(array(
                        'package' => 'foo',
                        'channel' => 'boo.example.com',
                    )),
                    'right parsed thing');

// throw this in there for coverage reasons
$test->assertEquals('pear.php.net', $creg->getPearChannel()->name, '__call test');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===