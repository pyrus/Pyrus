--TEST--
PEAR2_Pyrus_ChannelRegistry_Xml::delete() delete internal channel
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
foreach (array('pear.php.net',
               'pear2.php.net',
               'pecl.php.net',
               '__uri') as $name) {
    $chan = $creg->get($name);
    $thrown = false;
    try {
        $creg->delete($chan);
        throw new Exception('delete succeeded and should have failed');
    } catch(PEAR2_Pyrus_ChannelRegistry_Exception $e) {
        $test->assertEquals('Cannot delete default channel ' . $name, $e->getMessage(), $name . ' message');
    }
    $test->assertEquals(true, $creg->exists($name), $name.' channel still exists');
}

?>
===DONE===
--CLEAN--
<?php
$dir = dirname(__DIR__) . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===