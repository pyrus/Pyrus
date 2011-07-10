<?php
/**
 * Test features of remote packages
 */

require __DIR__ . '/../../../../../autoload.php';

set_include_path(__DIR__);
$c = \Pyrus\Config::singleton(dirname(__DIR__), dirname(__DIR__) . '/pearconfig.xml');
$c->bin_dir = __DIR__ . '/bin';
restore_include_path();
$c->saveConfig();

$chan = new PEAR2\SimpleChannelServer\Channel('pear2.php.net', 'unit test channel');
$scs = new PEAR2\SimpleChannelServer\Main($chan, __DIR__, dirname(__DIR__) . '/PEAR2');

$scs->saveChannel();

$pf = new \Pyrus\PackageFile\v2;

for ($i = 1; $i <= 2; $i++) {
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

$pf2 = clone $pf;
$pf2->name = 'P2';
$pf2->dependencies['required']->php->min = '10000.345.56';

$pf->files['glooby1'] = array('role' => 'php');
$pf2->files['glooby2'] = array('role' => 'php');

file_put_contents(__DIR__ . '/package.xml', $pf);
$package1 = new \Pyrus\Package(false);
$xmlcontainer = new \Pyrus\PackageFile($pf);
$xml = new \Pyrus\Package\Xml(__DIR__ . '/package.xml', $package1, $xmlcontainer);
$package1->setInternalPackage($xml);
$package1->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package1, 'cellog');

$pf->version['release'] = '1.1.0a1';
$pf->stability['release'] = 'alpha';
file_put_contents(__DIR__ . '/package.xml', $pf);
$package1 = new \Pyrus\Package(false);
$xmlcontainer = new \Pyrus\PackageFile($pf);
$xml = new \Pyrus\Package\Xml(__DIR__ . '/package.xml', $package1, $xmlcontainer);
$package1->setInternalPackage($xml);
$package1->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package1, 'cellog');

file_put_contents(__DIR__ . '/package.xml', $pf2);
$package1 = new \Pyrus\Package(false);
$xmlcontainer = new \Pyrus\PackageFile($pf2);
$xml = new \Pyrus\Package\Xml(__DIR__ . '/package.xml', $package1, $xmlcontainer);
$package1->setInternalPackage($xml);
$package1->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package1, 'cellog');

// clean up
unlink(dirname(__DIR__) . '/pearconfig.xml');
unlink(dirname(__DIR__) . '/.config');
for ($i = 1; $i <= 2; $i++) {
    unlink(__DIR__ . "/glooby$i");
}
unlink(__DIR__ . '/package.xml');
foreach (new DirectoryIterator(dirname(__DIR__) . '/.configsnapshots') as $file) {
    if ($file->isDot()) {
        continue;
    }
    unlink($file->getPathName());
}
rmdir(dirname(__DIR__) . '/.configsnapshots');
