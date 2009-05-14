--TEST--
PEAR2_Pyrus_ChannelRegistry_Xml::delete() delete failure, channel has installed packages
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
include __DIR__ . '/../../../Registry/AllRegistries/setupPackageFile.php.inc';
$chan = new PEAR2_Pyrus_Channel(new PEAR2_Pyrus_ChannelFile(dirname(__DIR__).'/../sample_channel.xml'));
$creg->add($chan);
$test->assertEquals(true, $creg->exists('pear.unl.edu'), 'successfully added the channel');
$chan = $creg->get('pear.unl.edu');

$info->channel = 'pear.unl.edu';
$creg->getRegistry()->install($info);

try {
    $creg->delete($chan);
    die('Should not have worked');
} catch (PEAR2_Pyrus_ChannelRegistry_Exception $e) {
    $test->assertEquals('Cannot delete channel pear.unl.edu, packages are installed', $e->getMessage(), 'error');
}
$test->assertEquals(true, $creg->exists('pear.unl.edu'), 'not successfully deleted');

?>
===DONE===
--CLEAN--
<?php
$dir = dirname(__DIR__) . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===