<?php
/**
 * Create a dependency tree like so:
 *
 * P1 -> P2 >= 1.2.0
 * P3 -> P2 <= 1.1.0
 *
 * to test composite dep failure
 */

require __DIR__ . '/../../../../../autoload.php';

set_include_path(__DIR__);
$c = PEAR2_Pyrus_Config::singleton(dirname(__DIR__), dirname(__DIR__) . '/pearconfig.xml');
$c->bin_dir = __DIR__ . '/bin';
restore_include_path();
$c->saveConfig();

$chan = new PEAR2_SimpleChannelServer_Channel('pear2.php.net', 'unit test channel');
$scs = new PEAR2_SimpleChannelServer($chan, __DIR__, dirname(__DIR__) . '/PEAR2');

$scs->saveChannel();

$pf = new PEAR2_Pyrus_PackageFile_v2;

for ($i = 1; $i <= 3; $i++) {
    file_put_contents(__DIR__ . "/glooby$i", 'hi');
}

$pf->name = 'P1';
$pf->channel = 'pear2.php.net';
$pf->summary = 'testing';
$pf->version['release'] = '1.0.0';
$pf->stability['release'] = 'stable';
$pf->description = 'hi description';
$pf->notes = 'my notes';
$pf->maintainer['cellog']->role('lead')->email('cellog@php.net')->active('yes')->name('Greg Beaver');

$pf->setPackagefile(__DIR__ . '/package.xml');
$save = clone $pf;

$pf->dependencies['required']->package['pear2.php.net/P2']->min('1.2.0');
$pf->files['glooby1'] =  array('role' => 'php');

$p2 = clone $save;
$p2->name = 'P2';
$p2->version['release'] = '1.2.1';
$p2->stability['release'] = 'stable';
$p2->files['glooby2'] =  array('role' => 'php');

$p3 = clone $save;
$p3->name = 'P3';
$p3->files['glooby3'] =  array('role' => 'php');
$p3->dependencies['required']->package['pear2.php.net/P2']->max('1.1.0');
$p3->files['glooby3'] =  array('role' => 'php');

file_put_contents(__DIR__ . '/package.xml', $pf);

$package1 = new PEAR2_Pyrus_Package(false);
$xmlcontainer = new PEAR2_Pyrus_PackageFile($pf);
$xml = new PEAR2_Pyrus_Package_Xml(__DIR__ . '/package.xml', $package1, $xmlcontainer);
$package1->setInternalPackage($xml);
$package1->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package1, 'cellog');

$package2 = new PEAR2_Pyrus_Package(false);
$xmlcontainer = new PEAR2_Pyrus_PackageFile($p2);
$xml = new PEAR2_Pyrus_Package_Xml(__DIR__ . '/package.xml', $package2, $xmlcontainer);
$package2->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p2);
$package2->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package2, 'cellog');

$package3 = new PEAR2_Pyrus_Package(false);
$xmlcontainer = new PEAR2_Pyrus_PackageFile($p3);
$xml = new PEAR2_Pyrus_Package_Xml(__DIR__ . '/package.xml', $package3, $xmlcontainer);
$package3->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p3);
$package3->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package3, 'cellog');

// clean up
unlink(dirname(__DIR__) . '/pearconfig.xml');
unlink(dirname(__DIR__) . '/.config');
for ($i = 1; $i <= 3; $i++) {
    unlink(__DIR__ . "/glooby$i");
}
unlink(__DIR__ . '/package.xml');
$dir = dirname(__DIR__) . '/.xmlregistry';
include __DIR__ . '/../../../clean.php.inc';
$dir = dirname(__DIR__) . '/.configsnapshots';
include __DIR__ . '/../../../clean.php.inc';
unlink(dirname(__DIR__) . '/.pear2registry');
$dir = dirname(__DIR__) . '/PEAR2/.xmlregistry';
include __DIR__ . '/../../../clean.php.inc';
unlink(dirname(__DIR__) . '/PEAR2/.pear2registry');
rmdir(dirname(__DIR__) . '/PEAR2/temp');
rmdir(dirname(__DIR__) . '/temp');
