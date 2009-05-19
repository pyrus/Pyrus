--TEST--
PEAR2_Pyrus_Channel: iteration over remote REST information, package list
--FILE--
<?php

include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../../Mocks/Internet/installer.prepare.dep.versionconflict',
                       'http://pear2.php.net/');
PEAR2_Pyrus_REST::$downloadClass = 'Internet';
$chan = PEAR2_Pyrus_Config::current()->channelregistry['pear2.php.net'];
$names = array();
foreach ($chan->remotepackages as $package) {
    $names[] = $package->name;
}
sort($names);

$test->assertEquals(array('P1', 'P2', 'P3', 'P4', 'P5'), $names, 'package names');
$releases = array();
foreach ($chan->remotepackage['P2'] as $release) {
    $releases[] = $release;
}
$test->assertEquals(array('1.2.3' => array(
                                           'stability' => 'stable',
                                           'minimumphp' => '5.2.0'),
                          '1.2.2' => array(
                                           'stability' => 'stable',
                                           'minimumphp' => '5.2.0')),
                    $releases, 'release information');

$names = array();
foreach ($chan->remotepackages['stable'] as $package) {
    $names[] = $package;
}
sort($names);

$test->assertEquals(array('P1', 'P2', 'P3', 'P4'), $names, 'package names stable');

$names = array();
foreach ($chan->remotepackages['beta'] as $package) {
    $names[] = $package;
}
sort($names);

$test->assertEquals(array('P5'), $names, 'package names beta');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===