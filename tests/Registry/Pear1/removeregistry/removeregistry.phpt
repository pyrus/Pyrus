--TEST--
Pear1 registry: removeRegistry()
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$dir = TESTDIR . DIRECTORY_SEPARATOR;
require __DIR__ . '/../../AllRegistries/listpackages/multiple.template';

if (!file_exists(TESTDIR . '/php/.lock')) {
    touch(TESTDIR . '/php/.lock');
}
$chan = new PEAR2\Pyrus\ChannelRegistry\Pear1(TESTDIR);
$poo = $chan->get('pear.php.net')->toChannelFile();
$poo->name = 'poo.php.net';
$poo->alias = 'poo';
$chan->add(new PEAR2\Pyrus\Channel($poo));

$test->assertFileExists(TESTDIR . '/php/.registry', 'Pear1 registry exists');
$test->assertFileExists(TESTDIR . '/php/.filemap', 'Pear1 filemap exists');
$test->assertFileExists(TESTDIR . '/php/.depdb', 'Pear1 depdb exists');
$test->assertFileExists(TESTDIR . '/php/.depdblock', 'Pear1 depdblock exists');
$test->assertFileExists(TESTDIR . '/php/.lock', 'Pear1 lock exists');
$test->assertFileExists(TESTDIR . '/php/.channels', 'Pear1 channel registry exists');

PEAR2\Pyrus\Registry\Pear1::removeRegistry(TESTDIR);

$test->assertFileNotExists(TESTDIR . '/php/.registry', 'Pear1 registry should not exist');
$test->assertFileNotExists(TESTDIR . '/php/.filemap', 'Pear1 filemap should not exist');
$test->assertFileNotExists(TESTDIR . '/php/.depdb', 'Pear1 depdb should not exist');
$test->assertFileNotExists(TESTDIR . '/php/.depdblock', 'Pear1 depdblock should not exist');
$test->assertFileNotExists(TESTDIR . '/php/.lock', 'Pear1 lock should not exist');
$test->assertFileNotExists(TESTDIR . '/php/.channels', 'Pear1 channel registry should not exist');

PEAR2\Pyrus\Registry\Pear1::removeRegistry(TESTDIR);

// for added coverage
try {
    $reg->begin();
    throw new Exception('should fail and did not');
} catch (PEAR2\Pyrus\Registry\Exception $e) {
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