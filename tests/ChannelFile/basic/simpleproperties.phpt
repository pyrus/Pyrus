--TEST--
ChannelFile: test basic channel.xml properties
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
require __DIR__ . '/../setupFiles/setupChannelFile.php.inc';
// $channel should be set up!
require __DIR__ . '/../../ChannelRegistry/AllRegistries/info/basic.template';
require __DIR__ . '/../../ChannelRegistry/AllRegistries/info/rest.template';
require __DIR__ . '/../../ChannelRegistry/AllRegistries/info/mirrors.template';


?>
===DONE===
--EXPECT--
===DONE===