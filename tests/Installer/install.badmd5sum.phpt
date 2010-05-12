--TEST--
\PEAR2\Pyrus\Installer::commit() file with bad md5sum
--FILE--
<?php
include dirname(__FILE__) . '/../test_framework.php.inc';
$package = new \PEAR2\Pyrus\Package(__DIR__.'/../Mocks/badmd5sum/package.xml');
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = \PEAR2\Pyrus\Config::singleton(__DIR__.'/testit', __DIR__ . '/testit/plugins/pearconfig.xml');
$c->bin_dir = __DIR__ . '/testit/bin';
$c->saveConfig();

\PEAR2\Pyrus\Installer::begin();
\PEAR2\Pyrus\Installer::prepare($package);
try {
    \PEAR2\Pyrus\Installer::commit();
    throw new Exception('worked and should fail');
} catch (PEAR2\Pyrus\Installer\Exception $e) {
    $test->assertEquals('bad md5sum for file src/foo.php', $e->getMessage(), 'error');
}
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===