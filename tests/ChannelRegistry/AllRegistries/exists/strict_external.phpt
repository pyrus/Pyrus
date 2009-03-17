--TEST--
PEAR2_Pyrus_ChannelRegistry::exists() strict external channel check
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/../setup.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/testit');
restore_include_path();
$c->saveConfig();
$chan = new PEAR2_Pyrus_Channel(dirname(__DIR__).'/../sample_channel.xml');
$c->channelregistry->add($chan);
$test->assertEquals(true, $c->channelregistry->exists('pear.unl.edu', true), 'external channel using full name');
$test->assertEquals(false, $c->channelregistry->exists('unl', true), 'external channel alias should fail strict check');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===