<?php

require __DIR__ . '/../../../../../autoload.php';

set_include_path(__DIR__);
$c = \pear2\Pyrus\Config::singleton(dirname(__DIR__), dirname(__DIR__) . '/pearconfig.xml');
$c->bin_dir = __DIR__ . '/bin';
restore_include_path();
$c->saveConfig();

$chan = new pear2\SimpleChannelServer\Channel('pear2.php.net', 'unit test channel');
$scs = new pear2\SimpleChannelServer\Main($chan, __DIR__, dirname(__DIR__) . '/PEAR2');

$scs->saveChannel();

$pf = new \pear2\Pyrus\PackageFile\v2;

for ($i = 1; $i <= 4; $i++) {
    file_put_contents(__DIR__ . "/glooby$i", 'hi');
}

$pf->name = 'P1';
$pf->channel = 'pear2.php.net';
$pf->summary = 'testing';
$pf->version['release'] = '1.0.0';
$pf->version['api'] = '1';
$pf->stability['release'] = 'stable';
$pf->stability['release'] = 'stable';
$pf->description = 'hi description';
$pf->notes = 'my notes';
$pf->maintainer['cellog']->role('lead')->email('cellog@php.net')->active('yes')->name('Greg Beaver');

$pf->setPackagefile(__DIR__ . '/package.xml');
$save = clone $pf;

$pf->files['glooby1'] =  array('role' => 'php');
$pf->dependencies['group']->default->hint = 'default install group';
$pf->dependencies['group']->default->package['pear2.php.net/P3']->save();

file_put_contents(__DIR__ . '/package.xml', $pf);
$package1 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($pf);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package1, $xmlcontainer);
$package1->setInternalPackage($xml);
$package1->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package1, 'cellog');

$pf = clone $save;
$pf->files['glooby2'] = array('role' => 'php');
$pf->name = 'P2';
$pf->dependencies['group']->group->hint = 'install group';
$pf->dependencies['group']->group->package['pear2.php.net/P4']->save();
file_put_contents(__DIR__ . '/package.xml', $pf);

file_put_contents(__DIR__ . '/package.xml', $pf);
$package1 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($pf);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package1, $xmlcontainer);
$package1->setInternalPackage($xml);
$package1->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package1, 'cellog');

$pf = clone $save;
$pf->name = 'P3';
$pf->files['glooby3'] = array('role' => 'php');
file_put_contents(__DIR__ . '/package.xml', $pf);

$package1 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($pf);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package1, $xmlcontainer);
$package1->setInternalPackage($xml);
$package1->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package1, 'cellog');

$pf = clone $save;
$pf->name = 'P4';
$pf->files['glooby4'] = array('role' => 'php');
file_put_contents(__DIR__ . '/package.xml', $pf);
$package1 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($pf);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package1, $xmlcontainer);
$package1->setInternalPackage($xml);
$package1->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package1, 'cellog');

// clean up
unlink(dirname(__DIR__) . '/pearconfig.xml');
unlink(dirname(__DIR__) . '/.config');
for ($i = 1; $i <= 4; $i++) {
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
