--TEST--
Custom command: basic test
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
PEAR2_Pyrus_Installer::$options['install-plugins'] = true;

$test->assertTrue($package->isPlugin(), 'ensure the package registers as a plugin');

file_put_contents(__DIR__ . '/testit/foobar', '<?xml version="1.0" encoding="UTF-8"?>
<commands version="2.0" xmlns="http://pear2.php.net/dtd/customcommand-2.0">
 <command>
  <name>foobar</name>
  <class>Fronky_Wonky_Burm</class>
  <function>doDatThingy</function>
  <autoloadpath></autoloadpath>
  <summary>Install a package.  Use install --plugin to install plugins</summary>
  <shortcut>y</shortcut>
  <options>
   <option>
    <name>plugin</name>
    <shortopt>p</shortopt>
    <type><string/></type>
    <doc>Manage plugin installation only</doc>
   </option>
   <option>
    <name>packagingroot</name>
    <shortopt>r</shortopt>
    <type><string/></type>
    <doc>Install the package in a directory in preparation for packaging with tools like RPM</doc>
   </option>
   <option>
    <name>optionaldeps</name>
    <shortopt>o</shortopt>
    <type><bool/></type>
    <doc>Automatically download and install all optional dependencies</doc>
   </option>
   <option>
    <name>force</name>
    <shortopt>f</shortopt>
    <type><bool/></type>
    <doc>Force the installation to proceed independent of errors.  USE SPARINGLY.</doc>
   </option>
  </options>
  <arguments>
   <argument>
    <name>package</name>
    <multiple>1</multiple>
    <optional>0</optional>
    <doc>argument 1.</doc>
   </argument>
  </arguments>
  <doc>My extra doc
Is Pretty Sweet.</doc>
 </command>
</commands>');
define('MYDIR', __DIR__);
mkdir(__DIR__ . '/testit/Fronky/Wonky', 0755, true);
file_put_contents(__DIR__ . '/testit/Fronky/Wonky/Burm.php', '<?php
class Fronky_Wonky_Burm
{
    function doDatThingy($args, $opts)
    {
        var_dump($args, $opts);
    }
}');

PEAR2_Pyrus_Installer::begin();
PEAR2_Pyrus_Installer::prepare($package);
PEAR2_Pyrus_Installer::commit();



ob_start();
$cli = new PEAR2_Pyrus_ScriptFrontend_Commands();
$cli->run($args = array (__DIR__ . '/testit', 'foobar', '--force', '-r/hi/there', 'oof', 'da'));

$contents = ob_get_contents();
ob_end_clean();

$test->assertEquals('Using PEAR installation found at ' . __DIR__ . DIRECTORY_SEPARATOR . 'testit
array(1) {
  ["package"]=>
  array(2) {
    [0]=>
    string(3) "oof"
    [1]=>
    string(2) "da"
  }
}
array(5) {
  ["plugin"]=>
  NULL
  ["packagingroot"]=>
  string(9) "/hi/there"
  ["optionaldeps"]=>
  NULL
  ["force"]=>
  bool(true)
  ["help"]=>
  NULL
}
', $contents, 'command output');
$reg = new PEAR2_Pyrus_PluginRegistry(__DIR__ . '/testit/plugins');
$test->assertTrue(isset($reg->package['pear2.php.net/testing2']), 'custom command installed');

?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===