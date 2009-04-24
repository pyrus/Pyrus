--TEST--
PEAR2_Pyrus_ChannelRegistry::delete() delete failure, channel has installed packages
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/../setup.php.inc';
include __DIR__ . '/../../../Registry/AllRegistries/setupPackageFile.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/testit');
restore_include_path();
$c->saveConfig();
$chan = new PEAR2_Pyrus_Channel(new PEAR2_Pyrus_ChannelFile(dirname(__DIR__).'/../sample_channel.xml'));
$c->channelregistry->add($chan);
$test->assertEquals(true, $c->channelregistry->exists('pear.unl.edu'), 'successfully added the channel');
$chan = $c->channelregistry->get('pear.unl.edu');

$info->channel = 'pear.unl.edu';
$c->registry->install($info);

try {
    $c->channelregistry->delete($chan);
    die('Should not have worked');
} catch (PEAR2_Pyrus_ChannelRegistry_Exception $e) {
    $test->assertEquals('Cannot delete channel pear.unl.edu, packages are installed', $e->getMessage(), 'error');
}
$test->assertEquals(true, $c->channelregistry->exists('pear.unl.edu'), 'not successfully deleted');

?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===