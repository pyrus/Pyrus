--TEST--
\Pyrus\ChannelRegistry::get(), default channels don't exist
--FILE--
<?php
require dirname(__DIR__) . '/../setup.php.inc';
$c = getTestConfig();

include __DIR__ . '/defaultchannels.php.inc';
$chan = new Pyrus\ChannelRegistry(TESTDIR, array('Foo'));
$test->assertEquals('pear.php.net', $chan->get('pear.php.net')->name, 'pear');
$test->assertEquals('pear2.php.net', $chan->get('pear2.php.net')->name, 'pear2');
$test->assertEquals('pecl.php.net', $chan->get('pecl.php.net')->name, 'pecl');
$test->assertEquals('doc.php.net', $chan->get('doc.php.net')->name, 'doc');
$test->assertEquals('__uri', $chan->get('__uri')->name, '__uri');

Pyrus\ChannelRegistry\Foo::$throw = true;
try {
    $chan = new Pyrus\ChannelRegistry(TESTDIR, array('Foo'));
    throw new Exception('Expected exception.');
} catch (Pyrus\ChannelRegistry\Exception $e) {
    $test->assertEquals('Unable to initialize registry for path "' . TESTDIR . '"',
                        $e->getMessage(), 'message');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===