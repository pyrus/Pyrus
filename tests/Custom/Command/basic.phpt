--TEST--
Custom command: basic test
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
\Pyrus\Main::$options['install-plugins'] = true;

$test->assertTrue($package->isPlugin(), 'ensure the package registers as a plugin');

file_put_contents(TESTDIR . '/foobar', '<?xml version="1.0" encoding="UTF-8"?>
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
mkdir(TESTDIR . '/Fronky/Wonky', 0755, true);
file_put_contents(TESTDIR . '/Fronky/Wonky/Burm.php', '<?php
class Fronky_Wonky_Burm
{
    function doDatThingy($frontend, $args, $opts)
    {
        var_dump(get_class($frontend), $args, $opts);
    }
}');

\Pyrus\Installer::begin();
\Pyrus\Installer::prepare($package);
\Pyrus\Installer::commit();



ob_start();
$cli = new \Pyrus\ScriptFrontend\Commands();
$cli->run($args = array (TESTDIR, 'foobar', '--force', '-r/hi/there', 'oof', 'da'));

$contents = ob_get_contents();
ob_end_clean();

$test->assertEquals('Using PEAR installation found at ' . TESTDIR . '
string(' . strlen('Pyrus\ScriptFrontend\Commands') . ') "Pyrus\ScriptFrontend\Commands"
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
$reg = new \Pyrus\PluginRegistry(TESTDIR . '//plugins');
$test->assertTrue(isset($reg->package['pear2.php.net/testing2']), 'custom command installed');

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===