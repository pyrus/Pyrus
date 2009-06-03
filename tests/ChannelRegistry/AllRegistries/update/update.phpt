--TEST--
PEAR2_Pyrus_ChannelRegistry::update() basic test
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/../setup.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/testit', __DIR__ . '/testit/plugins/pearconfig.xml');
restore_include_path();
$c->saveConfig();
$test->assertEquals(false, $c->channelregistry->exists('pear.unl.edu'), 'channel should not exist');
$chan = new PEAR2_Pyrus_Channel(new PEAR2_Pyrus_ChannelFile(dirname(__DIR__).'/../sample_channel.xml'));
$c->channelregistry->add($chan);
$test->assertEquals(true, $c->channelregistry->exists('pear.unl.edu'), 'successfully added the channel');

$test->assertEquals(80, $c->channelregistry->get('pear.unl.edu')->port, 'before');
$chan->port = 234;
$c->channelregistry->update($chan);
$test->assertEquals(234, $c->channelregistry->get('pear.unl.edu')->port, 'before');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===