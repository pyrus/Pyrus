--TEST--
\PEAR2\Pyrus\ScriptFrontend\Commands::channelDiscover() Discover channel twice
--FILE--
<?php
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'testit')) {
    $dir = __DIR__ . '/testit';
    include __DIR__ . '/../../clean.php.inc';
}
require __DIR__ . '/setup.php.inc';

set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = \PEAR2\Pyrus\Config::singleton(__DIR__.'/testit', __DIR__ . '/testit/plugins/pearconfig.xml');
$c->bin_dir = __DIR__ . '/testit/bin';
restore_include_path();
$c->saveConfig();

require __DIR__ . '/../../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/testit',
                       'http://pear.unl.edu/');
\PEAR2\Pyrus\Main::$downloadClass = 'Internet';
file_put_contents(__DIR__ . '/testit/channel.xml', '<?xml version="1.0" encoding="UTF-8"?>
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
$cli->run($args = array (__DIR__ . '/testit', 'channel-discover', 'pear.unl.edu'));
$test->assertEquals(true, isset(\PEAR2\Pyrus\Config::current()->channelregistry['pear.unl.edu']),
                    'after discover of pear.unl.edu');

try {
    $cli->run($args = array (__DIR__ . '/testit', 'channel-discover', 'pear.unl.edu'));
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
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===