--TEST--
Xml registry: removeRegistry()
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$dir = TESTDIR . DIRECTORY_SEPARATOR;
require __DIR__ . '/../../AllRegistries/listpackages/multiple.template';

$chan = new PEAR2\Pyrus\ChannelRegistry\Xml(TESTDIR);
$poo = $chan->get('pear.php.net')->toChannelFile();
$poo->name = 'poo.php.net';
$poo->alias = 'poo';
$chan->add(new PEAR2\Pyrus\Channel($poo));

$test->assertFileExists(TESTDIR . '/.xmlregistry', 'Xml registry exists');
$test->assertFileExists(TESTDIR . '/.xmlregistry/channels', 'Xml channel registry exists');
$test->assertFileExists(TESTDIR . '/.xmlregistry/packages', 'Xml package registry exists');

PEAR2\Pyrus\Registry\Xml::removeRegistry(TESTDIR);

$test->assertFileNotExists(TESTDIR . '/.xmlregistry', 'Xml registry exists');

PEAR2\Pyrus\Registry\Xml::removeRegistry(TESTDIR);

// for added coverage
try {
    $reg->begin();
    throw new Exception('should fail and did not');
} catch (PEAR2\Pyrus\Registry\Exception $e) {
    $reg->rollback();
    $test->assertEquals('internal error: file transaction must be started before registry transaction',
                        $e->getMessage(), 'error');
}

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===