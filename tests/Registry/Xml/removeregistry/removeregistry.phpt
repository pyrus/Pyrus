--TEST--
Xml registry: removeRegistry()
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$dir = __DIR__ . DIRECTORY_SEPARATOR . 'testit' . DIRECTORY_SEPARATOR;
require __DIR__ . '/../../AllRegistries/listpackages/multiple.template';

$chan = new PEAR2\Pyrus\ChannelRegistry\Xml(__DIR__ . '/testit');
$poo = $chan->get('pear.php.net')->toChannelFile();
$poo->name = 'poo.php.net';
$poo->alias = 'poo';
$chan->add(new PEAR2\Pyrus\Channel($poo));

$test->assertFileExists(__DIR__ . '/testit/.xmlregistry', 'Xml registry exists');
$test->assertFileExists(__DIR__ . '/testit/.xmlregistry/channels', 'Xml channel registry exists');
$test->assertFileExists(__DIR__ . '/testit/.xmlregistry/packages', 'Xml package registry exists');

PEAR2\Pyrus\Registry\Xml::removeRegistry(__DIR__ . '/testit');

$test->assertFileNotExists(__DIR__ . '/testit/.xmlregistry', 'Xml registry exists');

PEAR2\Pyrus\Registry\Xml::removeRegistry(__DIR__ . '/testit');

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
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===