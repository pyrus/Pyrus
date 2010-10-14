--TEST--
\PEAR2\Pyrus\ChannelRegistry::delete() delete internal channel
--FILE--
<?php
require dirname(__DIR__) . '/../setup.php.inc';
$c = getTestConfig();

foreach (array('pear.php.net',
               'pear2.php.net',
               'pecl.php.net',
               '__uri') as $name) {
    $chan = $c->channelregistry->get($name);
    $thrown = false;
    $test->assertEquals(true, $c->channelregistry->exists($name), $name.' channel exists before');
    try {
        $c->channelregistry->delete($chan);
        throw new Exception('delete succeeded and should have failed');
    } catch(\PEAR2\Pyrus\ChannelRegistry\Exception $e) {
        $test->assertEquals('Cannot delete default channel ' . $name, $e->getMessage(), $name . ' message');
    }
    $test->assertEquals(true, $c->channelregistry->exists($name), $name.' channel still exists');
}

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===
