<?php
/**
 * Create a parent package and split it up on upgrade
 *
 * This is to test the new ugprade mechanism in Pyrus that obsoletes the need for
 * a special subpackage dependency
 */

require __DIR__ . '/../../../../../autoload.php';

set_include_path(__DIR__);
$c = \PEAR2\Pyrus\Config::singleton(dirname(__DIR__), dirname(__DIR__) . '/pearconfig.xml');
$c->bin_dir = __DIR__ . '/bin';
restore_include_path();
$c->saveConfig();

$chan = new PEAR2\SimpleChannelServer\Channel('pear2.php.net', 'unit test channel');
$scs = new PEAR2\SimpleChannelServer\Main($chan, __DIR__, dirname(__DIR__) . '/PEAR2');

$scs->saveChannel();

$pf = new \PEAR2\Pyrus\PackageFile\v2;

for ($i = 1; $i <= 5; $i++) {
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

$pf->files['glooby1'] =  array('role' => 'php');
$pf->files['glooby2'] =  array('role' => 'php');
$pf->files['glooby3'] =  array('role' => 'php');
$pf->files['glooby4'] =  array('role' => 'php');
$pf->files['glooby5'] =  array('role' => 'php');

$pf2 = clone $save;
$pf2->files['glooby1'] = array('role' => 'php');
$pf2->version['release'] = '1.1.0';
$pf2->dependencies['required']->package['pear2.php.net/P2']->save();
$pf2->dependencies['required']->package['pear2.php.net/P3']->save();
$pf2->dependencies['required']->package['pear2.php.net/P4']->save();
$pf2->dependencies['required']->package['pear2.php.net/P5']->save();

$p2 = clone $save;
$p2->name = 'P2';
$p2->files['glooby2'] =  array('role' => 'php');

$p3 = clone $save;
$p3->name = 'P3';
$p3->files['glooby3'] =  array('role' => 'php');

$p4 = clone $save;
$p4->name = 'P4';
$p4->files['glooby4'] =  array('role' => 'php');

$p5 = $save;
$p5->name = 'P5';
$p5->files['glooby5'] =  array('role' => 'php');

file_put_contents(__DIR__ . '/package.xml', $pf);

$package1 = new \PEAR2\Pyrus\Package(false);
$xmlcontainer = new \PEAR2\Pyrus\PackageFile($pf);
$xml = new \PEAR2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package1, $xmlcontainer);
$package1->setInternalPackage($xml);
$package1->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package1, 'cellog');

$package2 = new \PEAR2\Pyrus\Package(false);
$xmlcontainer = new \PEAR2\Pyrus\PackageFile($pf2);
$xml = new \PEAR2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package2, $xmlcontainer);
$package2->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $pf2);
$package2->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package2, 'cellog');

$package2 = new \PEAR2\Pyrus\Package(false);
$xmlcontainer = new \PEAR2\Pyrus\PackageFile($p2);
$xml = new \PEAR2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package2, $xmlcontainer);
$package2->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p2);
$package2->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package2, 'cellog');

$package3 = new \PEAR2\Pyrus\Package(false);
$xmlcontainer = new \PEAR2\Pyrus\PackageFile($p3);
$xml = new \PEAR2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package3, $xmlcontainer);
$package3->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p3);
$package3->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package3, 'cellog');

$package4 = new \PEAR2\Pyrus\Package(false);
$xmlcontainer = new \PEAR2\Pyrus\PackageFile($p4);
$xml = new \PEAR2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package4, $xmlcontainer);
$package4->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p4);
$package4->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package4, 'cellog');

$package5 = new \PEAR2\Pyrus\Package(false);
$xmlcontainer = new \PEAR2\Pyrus\PackageFile($p5);
$xml = new \PEAR2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package5, $xmlcontainer);
$package5->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p5);
$package5->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package5, 'cellog');

// clean up
unlink(dirname(__DIR__) . '/pearconfig.xml');
unlink(dirname(__DIR__) . '/.config');
for ($i = 1; $i <= 5; $i++) {
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
