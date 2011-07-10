--TEST--
\Pyrus\ChannelRegistry\Pear1::delete() delete failure, channel has installed packages
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
include __DIR__ . '/../../../Registry/AllRegistries/setupPackageFile.php.inc';
$chan = new \Pyrus\Channel(new \Pyrus\ChannelFile(dirname(__DIR__).'/../sample_channel.xml'));
$creg->add($chan);
$test->assertEquals(true, $creg->exists('pear.unl.edu'), 'successfully added the channel');
$chan = $creg->get('pear.unl.edu');

$info->channel = 'pear.unl.edu';
$creg->getRegistry()->install($info);

try {
    $creg->delete($chan);
    throw new Exception('passed and shouldn\'t');
} catch (\Pyrus\ChannelRegistry\Exception $e) {
    $test->assertEquals('Cannot delete channel pear.unl.edu, packages are installed', $e->getMessage(), 'error');
}
$test->assertEquals(true, $creg->exists('pear.unl.edu'), 'not successfully deleted');

$chan = new \Pyrus\Channel(new \Pyrus\ChannelFile(dirname(__DIR__).'/../sample_channel.xml'));
$chan->name = 'foo';
$test->assertEquals(true, $creg->delete($chan), 'non-existing');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===