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
$c = PEAR2_Pyrus_Config::singleton(dirname(__DIR__), dirname(__DIR__) . '/pearconfig.xml');
$c->bin_dir = __DIR__ . '/bin';
restore_include_path();
$c->saveConfig();

$chan = new PEAR2_SimpleChannelServer_Channel('pear2.php.net', 'unit test channel');
$scs = new PEAR2_SimpleChannelServer($chan, __DIR__, dirname(__DIR__));

$scs->saveChannel();

$pf = new PEAR2_Pyrus_PackageFile_v2;

$pf->name = 'P1';
$pf->channel = 'pear2.php.net';
$pf->summary = 'testing';
$pf->version['release'] = '1.0.0';
$pf->stability['release'] = 'stable';
$pf->description = 'hi description';
$pf->notes = 'my notes';
$pf->maintainer['cellog']->role('lead')->email('cellog@php.net')->active('yes')->name('Greg Beaver');
$pf->files['glooby'] = array('role' => 'php');

$pf->setPackagefile(__DIR__ . '/package.xml');
$save = clone $pf;

$pf->dependencies['required']->package['pear2.php.net/P2']->min('1.2.0');

$p2_3 = clone $save;
$p2_3->name = 'P2';
$p2_3->version['release'] = '1.2.3';
$p2_3->dependencies['required']->package['pear2.php.net/P3']->save();
$p2_3->dependencies['required']->package['pear2.php.net/P5']->save();

$p2_2 = clone $save;
$p2_2->name = 'P2';
$p2_2->version['release'] = '1.2.2';
$p2_2->dependencies['required']->package['pear2.php.net/P3']->save();

$p3 = clone $save;
$p3->name = 'P3';

$p4 = clone $save;
$p4->name = 'P4';
$p4->dependencies['required']->package['pear2.php.net/P2']->min('1.2.0')->exclude('1.2.3');

$p5 = $save;
$p5->name = 'P5';
$p5->stability['release'] = 'beta';
$p5->version['release'] = '0.9.0';


file_put_contents(__DIR__ . '/package.xml', $pf);

$package1 = new PEAR2_Pyrus_Package(false);
$xmlcontainer = new PEAR2_Pyrus_PackageFile($pf);
$xml = new PEAR2_Pyrus_Package_Xml(__DIR__ . '/package.xml', $package1, $xmlcontainer);
$package1->setInternalPackage($xml);
$scs->saveRelease($package1, 'cellog');

$package2_1_2_3 = new PEAR2_Pyrus_Package(false);
$xmlcontainer = new PEAR2_Pyrus_PackageFile($p2_3);
$xml = new PEAR2_Pyrus_Package_Xml(__DIR__ . '/package.xml', $package2_1_2_3, $xmlcontainer);
$package2_1_2_3->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p2_3);
$scs->saveRelease($package2_1_2_3, 'cellog');

$package2_1_2_2 = new PEAR2_Pyrus_Package(false);
$xmlcontainer = new PEAR2_Pyrus_PackageFile($p2_2);
$xml = new PEAR2_Pyrus_Package_Xml(__DIR__ . '/package.xml', $package2_1_2_2, $xmlcontainer);
$package2_1_2_2->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p2_2);
$scs->saveRelease($package2_1_2_2, 'cellog');

$package3 = new PEAR2_Pyrus_Package(false);
$xmlcontainer = new PEAR2_Pyrus_PackageFile($p3);
$xml = new PEAR2_Pyrus_Package_Xml(__DIR__ . '/package.xml', $package3, $xmlcontainer);
$package3->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p3);
$scs->saveRelease($package3, 'cellog');

$package4 = new PEAR2_Pyrus_Package(false);
$xmlcontainer = new PEAR2_Pyrus_PackageFile($p4);
$xml = new PEAR2_Pyrus_Package_Xml(__DIR__ . '/package.xml', $package4, $xmlcontainer);
$package4->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p4);
$scs->saveRelease($package4, 'cellog');

$package5 = new PEAR2_Pyrus_Package(false);
$xmlcontainer = new PEAR2_Pyrus_PackageFile($p5);
$xml = new PEAR2_Pyrus_Package_Xml(__DIR__ . '/package.xml', $package5, $xmlcontainer);
$package5->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p5);
$scs->saveRelease($package5, 'cellog');

// clean up
unlink(dirname(__DIR__) . '/pearconfig.xml');
unlink(dirname(__DIR__) . '/.config');
foreach (new DirectoryIterator(dirname(__DIR__) . '/.configsnapshots') as $file) {
    if ($file->isDot()) {
        continue;
    }
    unlink($file->getPathName());
}
foreach (new DirectoryIterator(dirname(__DIR__) . '/.xmlregistry/channels') as $file) {
    if ($file->isDot()) {
        continue;
    }
    unlink($file->getPathName());
}
rmdir(dirname(__DIR__) . '/.xmlregistry/channels');
rmdir(dirname(__DIR__) . '/.xmlregistry');
rmdir(dirname(__DIR__) . '/.configsnapshots');
