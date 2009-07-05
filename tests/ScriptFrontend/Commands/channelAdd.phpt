--TEST--
\pear2\Pyrus\ScriptFrontend\Commands::channelAdd()
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'testit')) {
    $dir = __DIR__ . '/testit';
    include __DIR__ . '/../../clean.php.inc';
}
mkdir(__DIR__ . '/testit');
$chan = \pear2\Pyrus\Config::current()->channelregistry['pecl.php.net'];
$newchan = $chan->toChannelFile();
$newchan->name = 'foobar';
$newchan->alias = 'fb';
file_put_contents(__DIR__ . '/testit/blah.xml', $newchan);
ob_start();
$cli = new \pear2\Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array (__DIR__ . '/testit', 'channel-add', __DIR__ . '/testit/blah.xml'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . __DIR__. DIRECTORY_SEPARATOR . 'testit' . "\n"
                    . "Adding channel from channel.xml:\n"
                    . "Adding channel foobar successful\n",
                    $contents,
                    'delete channel');
$chan = \pear2\Pyrus\Config::current()->channelregistry['foobar'];
$test->assertEquals('fb', $chan->alias, 'verify we got back what we added');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===