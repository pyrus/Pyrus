--TEST--
\pear2\Pyrus\Channel Remote channel retrieval
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../Mocks/Internet/remotechannel',
                       'http://pear.php.net/');
\pear2\Pyrus\Main::$downloadClass = 'Internet';
// From file_get_contents
$channel = new \pear2\Pyrus\ChannelFile('http://pear.php.net/channel.xml');
// $channel should be set up!
require __DIR__ . '/../ChannelRegistry/AllRegistries/info/basic.template';

// From channel.xml url
$channel = new \pear2\Pyrus\ChannelFile('http://pear.php.net/channel.xml', false);
// $channel should be set up!
require __DIR__ . '/../ChannelRegistry/AllRegistries/info/basic.template';

// From channel.xml with is_remote = true
$channel = new \pear2\Pyrus\ChannelFile('http://pear.php.net/channel.xml', false, true);
// $channel should be set up!
require __DIR__ . '/../ChannelRegistry/AllRegistries/info/basic.template';

// From channel name, with is_remote = true
$channel = new \pear2\Pyrus\ChannelFile('pear.php.net', false, true);
// $channel should be set up!
require __DIR__ . '/../ChannelRegistry/AllRegistries/info/basic.template';

?>
===DONE===
--EXPECT--
===DONE===