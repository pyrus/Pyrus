--TEST--
Custom file task: basic test
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
PEAR2_Pyrus_Installer::$options['install-plugins'] = true;

$test->assertTrue($package->isPlugin(), 'ensure the package registers as a plugin');

file_put_contents(__DIR__ . '/testit/foobar', '<?xml version="1.0" encoding="UTF-8"?>
<task version="2.0" xmlns="http://pear2.php.net/dtd/customtask-2.0">
 <name>burm</name>
 <classprefix>Fronky_Wonky</classprefix>
 <autoloadpath></autoloadpath>
</task>');
define('MYDIR', __DIR__);
mkdir(__DIR__ . '/testit/Fronky/Wonky', 0755, true);
file_put_contents(__DIR__ . '/testit/Fronky/Wonky/Burm.php', '<?php
class Fronky_Wonky_Burm extends PEAR2_Pyrus_Task_Common {
    const TYPE = "simple";
    const PHASE = PEAR2_Pyrus_Task_Common::PACKAGEANDINSTALL;
    static function validateXml(PEAR2_Pyrus_IPackage $pkg, $xml, $fileXml, $file)
    {
        return true;
    }

    function startSession($fp, $dest)
    {
        return true;
    }
}');

PEAR2_Pyrus_Installer::begin();
PEAR2_Pyrus_Installer::prepare($package);
PEAR2_Pyrus_Installer::commit();

$reg = new PEAR2_Pyrus_PluginRegistry(__DIR__ . '/testit/plugins');
$reg->scan();
$test->assertTrue(isset($reg->package['pear2.php.net/testing2']), 'custom task installed');
$task = $package->getTask('burm');
$test->assertEquals('Fronky_Wonky_Burm', $task, 'right task class retrieved');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===