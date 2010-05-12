--TEST--
\PEAR2\Pyrus\ChannelRegistry::delete() delete internal channel
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/../setup.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = \PEAR2\Pyrus\Config::singleton(__DIR__.'/testit', __DIR__ . '/testit/plugins/pearconfig.xml');
restore_include_path();
$c->saveConfig();
foreach (array('pear.php.net',
               'pear2.php.net',
               'pecl.php.net',
               '__uri') as $name) {
    $chan = $c->channelregistry->get($name);
    $thrown = false;
    $test->assertEquals(1, $c->channelregistry->exists($name), $name.' channel exists before');
    try {
        $c->channelregistry->delete($chan);
        throw new Exception('delete succeeded and should have failed');
    } catch(\PEAR2\Pyrus\ChannelRegistry\Exception $e) {
        $test->assertEquals('Cannot delete default channel ' . $name, $e->getMessage(), $name . ' message');
    }
    $test->assertEquals(1, $c->channelregistry->exists($name), $name.' channel still exists');
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