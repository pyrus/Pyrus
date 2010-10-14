--TEST--
\PEAR2\Pyrus\Installer::commit() file with bad md5sum
--FILE--
<?php
include __DIR__ . '/../test_framework.php.inc';
$package = new \PEAR2\Pyrus\Package(__DIR__.'/../Mocks/badmd5sum/package.xml');
$c = getTestConfig();

\PEAR2\Pyrus\Installer::begin();
\PEAR2\Pyrus\Installer::prepare($package);
try {
    \PEAR2\Pyrus\Installer::commit();
    throw new Exception('worked and should fail');
} catch (PEAR2\Pyrus\Installer\Exception $e) {
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