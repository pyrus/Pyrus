--TEST--
\PEAR2\Pyrus\ChannelRegistry::get(), default channels don't exist
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/../setup.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = \PEAR2\Pyrus\Config::singleton(__DIR__.'/testit', __DIR__ . '/testit/plugins/pearconfig.xml');
restore_include_path();
$c->saveConfig();

include __DIR__ . '/defaultchannels.php.inc';
$chan = new PEAR2\Pyrus\ChannelRegistry(__DIR__ . '/testit', array('Foo'));
$test->assertEquals('pear.php.net', $chan->get('pear.php.net')->name, 'pear');
$test->assertEquals('pear2.php.net', $chan->get('pear2.php.net')->name, 'pear2');
$test->assertEquals('pecl.php.net', $chan->get('pecl.php.net')->name, 'pecl');
$test->assertEquals('doc.php.net', $chan->get('doc.php.net')->name, 'doc');
$test->assertEquals('__uri', $chan->get('__uri')->name, '__uri');

PEAR2\Pyrus\ChannelRegistry\Foo::$throw = true;
try {
    $chan = new PEAR2\Pyrus\ChannelRegistry(__DIR__ . '/testit', array('Foo'));
} catch (PEAR2\Pyrus\ChannelRegistry\Exception $e) {
    $test->assertEquals('Unable to initialize registry for path "' . __DIR__ . '/testit' . '"',
                        $e->getMessage(), 'message');
}
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===