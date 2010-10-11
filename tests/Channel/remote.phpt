--TEST--
\PEAR2\Pyrus\Channel Remote channel retrieval
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../Mocks/Internet/remotechannel',
                       'http://pear.php.net/');
\PEAR2\Pyrus\Main::$downloadClass = 'Internet';

// From file_get_contents
try {
	$channel = new \PEAR2\Pyrus\ChannelFile('http://pear.php.net/channel.xml');
	throw new Exception("Expected exception");
} catch (\PEAR2\Pyrus\ChannelFile\Exception $e) {
    $test->assertEquals('Unable to open channel xml file http://pear.php.net/channel.xml or file was empty.', $e->getMessage(), 'Did not set isRemote argument.');
}

// From channel.xml url
try {
	$channel = new \PEAR2\Pyrus\ChannelFile('http://pear.php.net/channel.xml', false);
	throw new Exception("Expected exception");
} catch (\PEAR2\Pyrus\ChannelFile\Exception $e) {
    $test->assertEquals('Unable to open channel xml file http://pear.php.net/channel.xml or file was empty.', $e->getMessage(), 'Did not set isRemote argument.');
}

// From channel.xml with is_remote = true
$channel = new \PEAR2\Pyrus\ChannelFile('http://pear.php.net/channel.xml', false, true);
// $channel should be set up!
require __DIR__ . '/../ChannelRegistry/AllRegistries/info/basic.template';

// From channel name, with is_remote = true
$channel = new \PEAR2\Pyrus\ChannelFile('pear.php.net', false, true);
// $channel should be set up!
require __DIR__ . '/../ChannelRegistry/AllRegistries/info/basic.template';

?>
===DONE===
--EXPECT--
===DONE===