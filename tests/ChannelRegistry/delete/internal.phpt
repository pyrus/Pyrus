--TEST--
PEAR2_Pyrus_ChannelRegistry::delete() delete internal channel
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/testit');
restore_include_path();
$c->saveConfig();
foreach (array('pear'=>'pear.php.net',
               'pear2'=>'pear2.php.net',
               'pecl'=>'pecl.php.net') as $alias=>$name) {
    $chan = $c->channelregistry->get($name);
    $thrown = false;
    try {
        $c->channelregistry->delete($chan);
    } catch(Exception $e) {
        $thrown = true;
    }
    $test->assertEquals(true, $c->channelregistry->exists($name), $name.' channel still exists');
    $test->assertEquals(true, $thrown, 'exception was thrown when trying to delete '.$name);
}

?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===