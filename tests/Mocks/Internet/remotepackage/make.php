<?php
/**
 * Test features of remote packages
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

for ($i = 1; $i <= 6; $i++) {
    file_put_contents(__DIR__ . "/glooby$i", 'hi');
}

$pf->name = 'GetMaintainers_Test';
$pf->channel = 'pear2.php.net';
$pf->summary = 'testing';
$pf->version['release'] = '1.0.0';
$pf->stability['release'] = 'stable';
$pf->description = 'hi description';
$pf->notes = 'my notes';
$pf->maintainer['cellog']->role('lead')->email('cellog@php.net')->active('yes')->name('Greg Beaver');
$pf->maintainer['foo1']->role('developer')->email('foo1@example.com')->active('yes')->name('Foo One');
$pf->maintainer['foo2']->role('developer')->email('foo2@example.com')->active('yes')->name('Foo Two');
$pf->maintainer['foo3']->role('contributor')->email('foo3@example.com')->active('yes')->name('Foo Three');
$pf->maintainer['foo4']->role('helper')->email('foo4@example.com')->active('yes')->name('Foo Four');

$pf->setPackagefile(__DIR__ . '/package.xml');
$pf->files['glooby1'] = array('role' => 'php');
file_put_contents(__DIR__ . '/package.xml', $pf);

$package1 = new PEAR2_Pyrus_Package(false);
$xmlcontainer = new PEAR2_Pyrus_PackageFile($pf);
$xml = new PEAR2_Pyrus_Package_Xml(__DIR__ . '/package.xml', $package1, $xmlcontainer);
$package1->setInternalPackage($xml);
$package1->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package1, 'cellog');

// clean up
unlink(dirname(__DIR__) . '/pearconfig.xml');
unlink(dirname(__DIR__) . '/.config');
for ($i = 1; $i <= 6; $i++) {
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
