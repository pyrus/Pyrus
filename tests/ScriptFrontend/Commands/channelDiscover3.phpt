--TEST--
\PEAR2\Pyrus\ScriptFrontend\Commands::channelDiscover() Discover channel twice
--FILE--
<?php
require __DIR__ . '/setup.php.inc';

$c = getTestConfig();

require __DIR__ . '/../../Mocks/Internet.php';

Internet::addDirectory(TESTDIR,
                       'http://pear.unl.edu/');
\PEAR2\Pyrus\Main::$downloadClass = 'Internet';
file_put_contents(TESTDIR . '/channel.xml', '<?xml version="1.0" encoding="UTF-8"?>
<channel version="1.0" xmlns="http://pear.php.net/channel-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink" xsi:schemaLocation="http://pear.php.net/dtd/channel-1.0 http://pear.php.net/dtd/channel-1.0.xsd">
 <name>pear.unl.edu</name>
 <suggestedalias>salty</suggestedalias>
 <summary>Simple PEAR Channel</summary>
 <servers>
  <primary>
   <rest>
    <baseurl type="REST1.0">http://foo/rest/</baseurl>
    <baseurl type="REST1.1">http://foo/rest/</baseurl>
    <baseurl type="REST1.3">http://foo/rest/</baseurl>
   </rest>
  </primary>
 </servers>
</channel>');

ob_start();
$cli = new \PEAR2\Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array (TESTDIR, 'channel-discover', 'pear.unl.edu'));
$test->assertEquals(true, isset(\PEAR2\Pyrus\Config::current()->channelregistry['pear.unl.edu']),
                    'after discover of pear.unl.edu');

try {
    $cli->run($args = array (TESTDIR, 'channel-discover', 'pear.unl.edu'));
    throw new \Exception('Should have thrown a channel registry exception.');
} catch(\Exception $e) {
    $test->assertEquals('Error: channel pear.unl.edu has already been discovered', $e->getMessage(), 'exception thrown on existing channel');
}
$contents = ob_get_contents();
ob_end_clean();

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===