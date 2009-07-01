--TEST--
Custom file role: basic test
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
PEAR2_Pyrus::$options['install-plugins'] = true;

$test->assertTrue($package->isPlugin(), 'ensure the package registers as a plugin');

file_put_contents(__DIR__ . '/testit/foobar', '<?xml version="1.0" encoding="UTF-8"?>
<role version="2.0" xmlns="http://pear2.php.net/dtd/customrole-2.0">
 <name>burm</name>
 <class>Fronky_Wonky_Burm</class>
 <autoloadpath></autoloadpath>
 <releasetypes>php</releasetypes>
 <releasetypes>extsrc</releasetypes>
 <releasetypes>extbin</releasetypes>
 <releasetypes>zendextsrc</releasetypes>
 <releasetypes>zendextbin</releasetypes>
 <installable>1</installable>
 <locationconfig>www_dir</locationconfig>
 <honorsbaseinstall />
 <unusualbaseinstall>1</unusualbaseinstall>
 <executable />
 <configvar>
  <name>burm_dir</name>
  <type>string</type>
  <default><![CDATA[<?php $default = md5(MYDIR);]]></default>
  <doc>The Burm config variable</doc>
  <prompt>Burm</prompt>
  <group>My Group</group>
  <configtype>system</configtype>
 </configvar>
 <configvar>
  <name>userburm</name>
  <type>set</type>
  <default>hi</default>
  <doc>The Burm config variable</doc>
  <validset>hi</validset>
  <validset>bye</validset>
  <validset>ok</validset>
  <prompt>Burm</prompt>
  <group>My Group</group>
  <configtype>user</configtype>
 </configvar>
</role>');
define('MYDIR', __DIR__);
mkdir(__DIR__ . '/testit/Fronky/Wonky', 0755, true);
file_put_contents(__DIR__ . '/testit/Fronky/Wonky/Burm.php', '<?php
class Fronky_Wonky_Burm extends PEAR2_Pyrus_Installer_Role_Common {}');

PEAR2_Pyrus_Installer::begin();
PEAR2_Pyrus_Installer::prepare($package);
PEAR2_Pyrus_Installer::commit();

$reg = new PEAR2_Pyrus_PluginRegistry(__DIR__ . '/testit/plugins');
$reg->scan();
$test->assertTrue(isset($reg->package['pear2.php.net/testing2']), 'custom role installed');
$test->assertEquals(md5(__DIR__), PEAR2_Pyrus_Config::current()->burm_dir, 'burm_dir default value');
$test->assertEquals('hi', PEAR2_Pyrus_Config::current()->userburm, 'userburm default value');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===