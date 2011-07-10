--TEST--
\Pyrus\Channel: iteration over remote REST information, package list
--FILE--
<?php

include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../../Mocks/Internet/installer.prepare.dep.versionconflict',
                       'http://pear2.php.net/');
\Pyrus\Main::$downloadClass = 'Internet';
$chan = \Pyrus\Config::current()->channelregistry['pear2.php.net'];
$names = array();
foreach ($chan->remotepackages as $package) {
    $names[] = $package->name;
}
sort($names);

$test->assertEquals(array('P1', 'P2', 'P3', 'P4', 'P5'), $names, 'package names');
$releases = array();
foreach ($chan->remotepackage['P2'] as $version => $release) {
    $releases[$version] = $release;
}
$test->assertEquals(array('1.2.3' => array(
                                           'stability' => 'stable',
                                           'minimumphp' => '5.2.0'),
                          '1.2.2' => array(
                                           'stability' => 'stable',
                                           'minimumphp' => '5.2.0'),
                          '0.9.0' => array(
                                           'stability' => 'beta',
                                           'minimumphp' => '5.2.0')
                          ),
                    $releases, 'release information');

$names = array();
foreach ($chan->remotepackages['stable'] as $package) {
    if ($package->name == 'P2') {
        $testp = $package;
    }
    $names[] = $package->name;
}
sort($names);

$test->assertEquals(array('P1', 'P2', 'P3', 'P4', 'P5'), $names, 'package names stable');

$releases = array();
foreach ($testp as $version => $release) {
    $releases[$version] = $release;
}
$test->assertEquals(array('1.2.3' => array(
                                           'stability' => 'stable',
                                           'minimumphp' => '5.2.0'),
                          '1.2.2' => array(
                                           'stability' => 'stable',
                                           'minimumphp' => '5.2.0'),
                          ),
                    $releases, 'release stable information');


$names = array();
foreach ($chan->remotepackages['beta'] as $package) {
    if ($package->name == 'P2') {
        $testp = $package;
    }
    $names[] = $package->name;
}
sort($names);

$test->assertEquals(array('P1', 'P2', 'P3', 'P4', 'P5'), $names, 'package names beta');

$releases = array();
foreach ($testp as $version => $release) {
    $releases[$version] = $release;
}

$releases = array();
foreach ($testp as $version => $release) {
    $releases[$version] = $release;
}
$test->assertEquals(array('1.2.3' => array(
                                           'stability' => 'stable',
                                           'minimumphp' => '5.2.0'),
                          '1.2.2' => array(
                                           'stability' => 'stable',
                                           'minimumphp' => '5.2.0'),
                          '0.9.0' => array(
                                           'stability' => 'beta',
                                           'minimumphp' => '5.2.0')
                          ),
                    $releases, 'release beta information');

$releases = array();
foreach ($chan->remotepackages['stable']->getPackage('P2') as $version => $release) {
    $releases[$version] = $release;
}
$test->assertEquals(array('1.2.3' => array(
                                           'stability' => 'stable',
                                           'minimumphp' => '5.2.0'),
                          '1.2.2' => array(
                                           'stability' => 'stable',
                                           'minimumphp' => '5.2.0'),
                          ),
                    $releases, 'getPackage release stable information');

$releases = array();
foreach ($chan->remotepackages['beta']->getPackage('P2') as $version => $release) {
    $releases[$version] = $release;
}
$test->assertEquals(array('1.2.3' => array(
                                           'stability' => 'stable',
                                           'minimumphp' => '5.2.0'),
                          '1.2.2' => array(
                                           'stability' => 'stable',
                                           'minimumphp' => '5.2.0'),
                          '0.9.0' => array(
                                           'stability' => 'beta',
                                           'minimumphp' => '5.2.0')
                          ),
                    $releases, 'getPackage release beta information');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===