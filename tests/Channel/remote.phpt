--TEST--
PEAR2_Pyrus_Channel::__construct() basic test
--SKIPIF--
<?php
$connection = @fsockopen('pear.php.net', 80);
if (!$connection) {
	echo 'Must have Internet access to test remote channel info retrieval.';
	fclose($connection);
}
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';

// From file_get_contents
$channel = new PEAR2_Pyrus_ChannelFile('http://pear.php.net/channel.xml');
// $channel should be set up!
require __DIR__ . '/../ChannelRegistry/AllRegistries/info/basic.template';

// From channel.xml url
$channel = new PEAR2_Pyrus_ChannelFile('http://pear.php.net/channel.xml', false);
// $channel should be set up!
require __DIR__ . '/../ChannelRegistry/AllRegistries/info/basic.template';

// From channel.xml with is_remote = true
$channel = new PEAR2_Pyrus_ChannelFile('http://pear.php.net/channel.xml', false, true);
// $channel should be set up!
require __DIR__ . '/../ChannelRegistry/AllRegistries/info/basic.template';

// From channel name, with is_remote = true
$channel = new PEAR2_Pyrus_ChannelFile('pear.php.net', false, true);
// $channel should be set up!
require __DIR__ . '/../ChannelRegistry/AllRegistries/info/basic.template';

?>
===DONE===
--EXPECT--
===DONE===