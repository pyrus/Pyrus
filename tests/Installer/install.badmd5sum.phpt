--TEST--
\Pyrus\Installer::commit() file with bad md5sum
--FILE--
<?php
include __DIR__ . '/../test_framework.php.inc';
$package = new \Pyrus\Package(__DIR__.'/../Mocks/badmd5sum/package.xml');
$c = getTestConfig();

\Pyrus\Installer::begin();
\Pyrus\Installer::prepare($package);
try {
    \Pyrus\Installer::commit();
    throw new Exception('worked and should fail');
} catch (Pyrus\Installer\Exception $e) {
    $test->assertEquals('bad md5sum for file src' . DIRECTORY_SEPARATOR . 'foo.php', $e->getMessage(), 'error');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===