<?php
/**
 * Create a dependency tree like so:
 *
 * P1 -> P2 >= 1.2.0 (1.2.3 is latest version)
 *
 * P2 1.2.3 -> P3
 *          -> P5
 *
 * P2 1.2.2 -> P3
 *
 * P3
 *
 * P4 -> P2 != 1.2.3
 *
 * P5
 *
 * This causes a conflict when P1 and P4 are installed that must resolve to installing:
 *
 * P1
 * P2 1.2.2
 * P3
 * P4
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

$pf->dependencies['required']->package['pear2.php.net/P2']->save();
$pf->dependencies['optional']->package['pear2.php.net/P3']->save();
$pf->dependencies['optional']->package['pear2.php.net/P4']->save();
$pf->dependencies['group']->mygroup->hint('this is a group');
$pf->dependencies['group']->mygroup->package['pear2.php.net/P5']->save();
$pf->files['glooby1'] =  array('role' => 'php');

$p2 = clone $save;
$p2->name = 'P2';
$p2->files['glooby2'] =  array('role' => 'php');

$p3 = clone $save;
$p3->name = 'P3';
$p3->files['glooby3'] =  array('role' => 'php');

$p4 = clone $save;
$p4->name = 'P4';
$p4->files['glooby4'] =  array('role' => 'php');

$p5 = clone $save;
$p5->name = 'P5';
$p5->files['glooby5'] =  array('role' => 'php');

$p6 = $save;
$p6->name = 'P6';
$p6->files['glooby6'] =  array('role' => 'php');
$p6->dependencies['optional']->package['pear2.php.net/P2']->save();
$p6->dependencies['optional']->package['pear2.php.net/P3']->save();


file_put_contents(__DIR__ . '/package.xml', $pf);

$package1 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($pf);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package1, $xmlcontainer);
$package1->setInternalPackage($xml);
$package1->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package1, 'cellog');

$package2 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($p2);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package2, $xmlcontainer);
$package2->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p2);
$package2->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package2, 'cellog');

$package3 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($p3);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package3, $xmlcontainer);
$package3->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p3);
$package3->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package3, 'cellog');

$package4 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($p4);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package4, $xmlcontainer);
$package4->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p4);
$package4->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package4, 'cellog');

$package5 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($p5);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package5, $xmlcontainer);
$package5->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p5);
$package5->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package5, 'cellog');

$package6 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($p6);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package6, $xmlcontainer);
$package6->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p6);
$package6->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package6, 'cellog');

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
