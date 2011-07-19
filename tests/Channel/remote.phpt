--TEST--
\Pyrus\Channel Remote channel retrieval
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../Mocks/Internet/remotechannel',
                       'http://pear.php.net/');
\Pyrus\Main::$downloadClass = 'Internet';

// From channel.xml
$channel = new \Pyrus\ChannelFile('http://pear.php.net/channel.xml');
// $channel should be set up!
require __DIR__ . '/../ChannelRegistry/AllRegistries/info/basic.template';

// From channel name
$channel = new \Pyrus\ChannelFile('pear.php.net');
// $channel should be set up!
require __DIR__ . '/../ChannelRegistry/AllRegistries/info/basic.template';

?>
===DONE===
--EXPECT--
===DONE===