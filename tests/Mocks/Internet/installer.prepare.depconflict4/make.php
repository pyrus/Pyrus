<?php
/**
 * In order to test excluding all possible versions
 * 
 * Create a dependency tree like so:
 *
 * P1 -> P2 min 1.1.0, exclude 1.2.0
 * P3 -> P2 max 1.3.0, exclude 1.2.3
 *
 * P2 only has releases for 1.0.0, 1.2.0, 1.2.3, and 1.3.1
 *
 * to test composite dep failure
 */

require __DIR__ . '/../../../../../autoload.php';

set_include_path(__DIR__);
$c = \pear2\Pyrus\Config::singleton(dirname(__DIR__), dirname(__DIR__) . '/pearconfig.xml');
$c->bin_dir = __DIR__ . '/bin';
restore_include_path();
$c->saveConfig();

$chan = new PEAR2_SimpleChannelServer_Channel('pear2.php.net', 'unit test channel');
$scs = new PEAR2_SimpleChannelServer($chan, __DIR__, dirname(__DIR__) . '/PEAR2');

$scs->saveChannel();

$pf = new \pear2\Pyrus\PackageFile\v2;

for ($i = 1; $i <= 6; $i++) {
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

$pf->dependencies['required']->package['pear2.php.net/P2']->min('1.1.0')->exclude('1.2.0')->exclude('1.2.3')
    ->recommended('1.3.1')->max('2.0.0');
$pf->files['glooby1'] =  array('role' => 'php');

$p2_1 = clone $save;
$p2_1->name = 'P2';
$p2_1->version['release'] = '1.0.0';
$p2_1->stability['release'] = 'stable';
$p2_1->files['glooby2'] =  array('role' => 'php');

$p2_2 = clone $save;
$p2_2->name = 'P2';
$p2_2->version['release'] = '1.2.0';
$p2_2->stability['release'] = 'stable';
$p2_2->files['glooby3'] =  array('role' => 'php');

$p2_3 = clone $save;
$p2_3->name = 'P2';
$p2_3->version['release'] = '1.2.3';
$p2_3->stability['release'] = 'stable';
$p2_3->files['glooby4'] =  array('role' => 'php');

$p2_4 = clone $save;
$p2_4->name = 'P2';
$p2_4->version['release'] = '1.3.1';
$p2_4->stability['release'] = 'stable';
$p2_4->files['glooby5'] =  array('role' => 'php');

$p3 = clone $save;
$p3->name = 'P3';
$p3->files['glooby6'] =  array('role' => 'php');
$p3->dependencies['required']->package['pear2.php.net/P2']->max('1.3.0')->exclude('1.2.3')->min('1.0.0');

file_put_contents(__DIR__ . '/package.xml', $pf);

$package1 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($pf);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package1, $xmlcontainer);
$package1->setInternalPackage($xml);
$package1->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package1, 'cellog');

$package2 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($p2_1);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package2, $xmlcontainer);
$package2->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p2_1);
$package2->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package2, 'cellog');

$package2 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($p2_2);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package2, $xmlcontainer);
$package2->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p2_2);
$package2->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package2, 'cellog');

$package2 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($p2_3);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package2, $xmlcontainer);
$package2->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p2_3);
$package2->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package2, 'cellog');

$package2 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($p2_4);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package2, $xmlcontainer);
$package2->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p2_4);
$package2->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package2, 'cellog');

$package3 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($p3);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package3, $xmlcontainer);
$package3->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p3);
$package3->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package3, 'cellog');

// clean up
unlink(dirname(__DIR__) . '/pearconfig.xml');
unlink(dirname(__DIR__) . '/.config');
for ($i = 1; $i <= 6; $i++) {
    unlink(__DIR__ . "/glooby$i");
}
unlink(__DIR__ . '/package.xml');
$dir = dirname(__DIR__) . '/.configsnapshots';
include __DIR__ . '/../../../clean.php.inc';
$dir = dirname(__DIR__) . '/.xmlregistry';
include __DIR__ . '/../../../clean.php.inc';
unlink(dirname(__DIR__) . '/.pear2registry');
$dir = dirname(__DIR__) . '/PEAR2/.xmlregistry';
include __DIR__ . '/../../../clean.php.inc';
unlink(dirname(__DIR__) . '/PEAR2/.pear2registry');
rmdir(dirname(__DIR__) . '/PEAR2/temp');
rmdir(dirname(__DIR__) . '/temp');
