--TEST--
Custom file task: basic test
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
\PEAR2\Pyrus\Main::$options['install-plugins'] = true;

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
class Fronky_Wonky_Burm extends \PEAR2\Pyrus\Task\Common {
    const TYPE = "simple";
    const PHASE = \PEAR2\Pyrus\Task\Common::PACKAGEANDINSTALL;
    static function validateXml(\PEAR2\Pyrus\PackageInterface $pkg, $xml, $fileXml, $file)
    {
        return true;
    }

    function startSession($fp, $dest)
    {
        return true;
    }
}');

\PEAR2\Pyrus\Installer::begin();
\PEAR2\Pyrus\Installer::prepare($package);
\PEAR2\Pyrus\Installer::commit();

$reg = new \PEAR2\Pyrus\PluginRegistry(TESTDIR . '/plugins');
$reg->scan();
$test->assertTrue(isset($reg->package['pear2.php.net/testing2']), 'custom task installed');
$task = \PEAR2\Pyrus\Task\Common::getTask('burm');
$test->assertEquals('Fronky_Wonky_Burm', $task, 'right task class retrieved');
$task = \PEAR2\Pyrus\Task\Common::getTask('foo:burm');
$test->assertEquals('Fronky_Wonky_Burm', $task, 'right task class retrieved 2');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===