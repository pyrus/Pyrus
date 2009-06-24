--TEST--
PEAR2_Pyrus_Channel_RemotePackage::getMaintainers()
--FILE--
<?php

define('MYDIR', __DIR__);
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../Mocks/Internet/remotepackage',
                       'http://pear2.php.net/');
PEAR2_Pyrus::$downloadClass = 'Internet';
$remote = new PEAR2_Pyrus_Channel_Remotepackage(PEAR2_Pyrus_Config::current()->channelregistry['pear2.php.net'],
                                                'stable');
$remote = $remote['GetMaintainers_Test'];
$remote->version['release'] = '1.0.0'; // choose the version we are accessing
// first, test with REST1.2
$res = array();
foreach ($remote->maintainer as $maintainer) {
    $res[$maintainer->user] = array($maintainer->email, $maintainer->name, $maintainer->role);
}
asort($res);
$test->assertEquals(array (
  'cellog' => 
  array (
    0 => 'cellog@php.net',
    1 => 'Greg Beaver',
    2 => 'lead',
  ),
  'foo1' => 
  array (
    0 => 'foo1@example.com',
    1 => 'Foo One',
    2 => 'developer',
  ),
  'foo2' => 
  array (
    0 => 'foo2@example.com',
    1 => 'Foo Two',
    2 => 'developer',
  ),
  'foo3' => 
  array (
    0 => 'foo3@example.com',
    1 => 'Foo Three',
    2 => 'contributor',
  ),
  'foo4' => 
  array (
    0 => 'foo4@example.com',
    1 => 'Foo Four',
    2 => 'helper',
  ),
), $res, 'maintainers REST1.2');

// next, test with REST1.0
unset(PEAR2_Pyrus_Config::current()->channelregistry['pear2.php.net']->protocols->rest['REST1.2']);
$remote = new PEAR2_Pyrus_Channel_Remotepackage(PEAR2_Pyrus_Config::current()->channelregistry['pear2.php.net'],
                                                'stable');
$remote = $remote['GetMaintainers_Test'];
$remote->version['release'] = '1.0.0'; // choose the version we are accessing
$res = array();
foreach ($remote->maintainer as $maintainer) {
    $res[$maintainer->user] = array($maintainer->email, $maintainer->name, $maintainer->role);
}
asort($res);
$test->assertEquals(array (
  'cellog' => 
  array (
    0 => 'cellog@php.net',
    1 => 'Greg Beaver',
    2 => 'lead',
  ),
  'foo1' => 
  array (
    0 => 'foo1@example.com',
    1 => 'Foo One',
    2 => 'developer',
  ),
  'foo2' => 
  array (
    0 => 'foo2@example.com',
    1 => 'Foo Two',
    2 => 'developer',
  ),
  'foo3' => 
  array (
    0 => 'foo3@example.com',
    1 => 'Foo Three',
    2 => 'contributor',
  ),
  'foo4' => 
  array (
    0 => 'foo4@example.com',
    1 => 'Foo Four',
    2 => 'helper',
  ),
), $res, 'maintainers REST1.0');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===