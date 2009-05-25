--TEST--
PEAR2_Pyrus_ScriptFrontend_Commands::channelDiscover() https
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'testit')) {
    $dir = __DIR__ . '/testit';
    include __DIR__ . '/../../clean.php.inc';
}
mkdir(__DIR__ . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/testit', __DIR__ . '/testit/pearconfig.xml');
$c->bin_dir = __DIR__ . '/testit/bin';
restore_include_path();
$c->saveConfig();

require __DIR__ . '/../../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/testit',
                       'https://pear.unl.edu/');
PEAR2_Pyrus_REST::$downloadClass = 'Internet';
PEAR2_Pyrus_ScriptFrontend_Commands::$downloadClass = 'Internet';
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
$test->assertEquals(false, isset(PEAR2_Pyrus_Config::current()->channelregistry['pear.unl.edu']),
                    'before discover of pear.unl.edu');
ob_start();
$cli = new PEAR2_Pyrus_ScriptFrontend_Commands();
$cli->run($args = array (__DIR__ . '/testit', 'channel-discover', 'pear.unl.edu'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . __DIR__. DIRECTORY_SEPARATOR . 'testit' . "\n"
                    . "Discovery of channel pear.unl.edu successful\n",
                     $contents,
                    'list packages');

$test->assertEquals(true, isset(PEAR2_Pyrus_Config::current()->channelregistry['pear.unl.edu']),
                    'after discover of pear.unl.edu');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===