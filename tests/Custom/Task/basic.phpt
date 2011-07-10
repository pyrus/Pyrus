--TEST--
Custom file task: basic test
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
\Pyrus\Main::$options['install-plugins'] = true;

$test->assertTrue($package->isPlugin(), 'ensure the package registers as a plugin');

file_put_contents(TESTDIR . '/foobar', '<?xml version="1.0" encoding="UTF-8"?>
<task version="2.0" xmlns="http://pear2.php.net/dtd/customtask-2.0">
 <name>burm</name>
 <class>Fronky_Wonky_Burm</class>
 <autoloadpath></autoloadpath>
</task>');
define('MYDIR', __DIR__);
mkdir(TESTDIR . '/Fronky/Wonky', 0755, true);
file_put_contents(TESTDIR . '/Fronky/Wonky/Burm.php', '<?php
class Fronky_Wonky_Burm extends \Pyrus\Task\Common {
    const TYPE = "simple";
    const PHASE = \Pyrus\Task\Common::PACKAGEANDINSTALL;
    static function validateXml(\Pyrus\PackageInterface $pkg, $xml, $fileXml, $file)
    {
        return true;
    }

    function startSession($fp, $dest)
    {
        return true;
    }
}');

\Pyrus\Installer::begin();
\Pyrus\Installer::prepare($package);
\Pyrus\Installer::commit();

$reg = new \Pyrus\PluginRegistry(TESTDIR . '/plugins');
$reg->scan();
$test->assertTrue(isset($reg->package['pear2.php.net/testing2']), 'custom task installed');
$task = \Pyrus\Task\Common::getTask('burm');
$test->assertEquals('Fronky_Wonky_Burm', $task, 'right task class retrieved');
$task = \Pyrus\Task\Common::getTask('foo:burm');
$test->assertEquals('Fronky_Wonky_Burm', $task, 'right task class retrieved 2');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===