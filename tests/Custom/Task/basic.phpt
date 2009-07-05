--TEST--
Custom file task: basic test
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
\pear2\Pyrus\Main::$options['install-plugins'] = true;

$test->assertTrue($package->isPlugin(), 'ensure the package registers as a plugin');

file_put_contents(__DIR__ . '/testit/foobar', '<?xml version="1.0" encoding="UTF-8"?>
<task version="2.0" xmlns="http://pear2.php.net/dtd/customtask-2.0">
 <name>burm</name>
 <class>Fronky_Wonky_Burm</class>
 <autoloadpath></autoloadpath>
</task>');
define('MYDIR', __DIR__);
mkdir(__DIR__ . '/testit/Fronky/Wonky', 0755, true);
file_put_contents(__DIR__ . '/testit/Fronky/Wonky/Burm.php', '<?php
class Fronky_Wonky_Burm extends \pear2\Pyrus\Task\Common {
    const TYPE = "simple";
    const PHASE = \pear2\Pyrus\Task\Common::PACKAGEANDINSTALL;
    static function validateXml(\pear2\Pyrus\IPackage $pkg, $xml, $fileXml, $file)
    {
        return true;
    }

    function startSession($fp, $dest)
    {
        return true;
    }
}');

\pear2\Pyrus\Installer::begin();
\pear2\Pyrus\Installer::prepare($package);
\pear2\Pyrus\Installer::commit();

$reg = new \pear2\Pyrus\PluginRegistry(__DIR__ . '/testit/plugins');
$reg->scan();
$test->assertTrue(isset($reg->package['pear2.php.net/testing2']), 'custom task installed');
$task = \pear2\Pyrus\Task\Common::getTask('burm');
$test->assertEquals('Fronky_Wonky_Burm', $task, 'right task class retrieved');
$task = \pear2\Pyrus\Task\Common::getTask('foo:burm');
$test->assertEquals('Fronky_Wonky_Burm', $task, 'right task class retrieved 2');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===