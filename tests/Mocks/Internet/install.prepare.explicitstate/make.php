<?php
/**
 * Test cascade of preferred stability to a package and its dependencies only
 *
 * P1-1.1.0RC1 beta -> P2
 * P1-1.0.0         -> P2
 *
 * P2-1.2.0a1 alpha
 * P2-1.1.0RC3 beta
 * P2-1.0.0
 *
 * P3-1.1.0RC1 beta
 * P3-1.1.0
 * P3-1.0.0         -> P2 <= 1.0.0
 *
 * Install of P1-beta and P3 should install
 *
 *  - P1-1.1.0RC1
 *  - P2-1.1.0RC3
 *  - P3-1.1.0
 */

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
$pf->date = '2008-10-03';
$pf->maintainer['cellog']->role('lead')->email('cellog@php.net')->active('yes')->name('Greg Beaver');

$pf->setPackagefile(__DIR__ . '/package.xml');
$save = clone $pf;

$pf->dependencies['required']->package['pear2.php.net/P2']->save();

$pf_beta = clone $pf;
$pf_beta->version['release'] = '1.1.0RC1';
$pf_beta->stability['release'] = 'beta';
$pf_beta->date = '2008-11-03';

$p2 = clone $save;
$p2->name = 'P2';
$p2beta = clone $p2;
$p2beta->date = '2008-11-03';
$p2beta->version['release'] = '1.1.0RC3';
$p2beta->stability['release'] = 'beta';
$p2alpha = clone $p2;
$p2alpha->date = '2008-12-03';
$p2alpha->version['release'] = '1.2.0a1';
$p2alpha->stability['release'] = 'alpha';

$p3 = clone $save;
$p3->name = 'P3';
$p3beta = clone $p3;
$p3beta->stability['release'] = 'beta';
$p3beta->version['release'] = '1.1.0RC1';
$p3beta->date = '2008-11-03';

$p32 = clone $p3;
$p32->version['release'] = '1.0.0';
$p3->version['release'] = '1.1.0';

$p32->dependencies['required']->package['pear2.php.net/P2']->max('1.0.0');

$pf->files['glooby1'] =  array('role' => 'php');
$pf_beta->files['glooby1'] =  array('role' => 'php');
$p2->files['glooby2'] = array('role' => 'php');
$p2beta->files['glooby2'] = array('role' => 'php');
$p2alpha->files['glooby2'] = array('role' => 'php');
$p3->files['glooby3'] = array('role' => 'php');
$p3beta->files['glooby3'] = array('role' => 'php');
$p32->files['glooby3'] = array('role' => 'php');

file_put_contents(__DIR__ . '/package.xml', $pf);

$package1 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($pf);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package1, $xmlcontainer);
$package1->setInternalPackage($xml);
$package1->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package1, 'cellog');

$package2 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($pf_beta);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package2, $xmlcontainer);
$package2->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $pf_beta);
$package2->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package2, 'cellog');

$package2 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($p2);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package2, $xmlcontainer);
$package2->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p2);
$package2->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package2, 'cellog');

$package2 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($p2beta);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package2, $xmlcontainer);
$package2->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p2beta);
$package2->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package2, 'cellog');

$package2 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($p2alpha);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package2, $xmlcontainer);
$package2->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p2alpha);
$package2->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package2, 'cellog');

$package3 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($p32);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package3, $xmlcontainer);
$package3->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p32);
$package3->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package3, 'cellog');

$package3 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($p3);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package3, $xmlcontainer);
$package3->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p3);
$package3->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package3, 'cellog');

$package3 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($p3beta);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package3, $xmlcontainer);
$package3->setInternalPackage($xml);
file_put_contents(__DIR__ . '/package.xml', $p3beta);
$package3->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package3, 'cellog');

// clean up
unlink(dirname(__DIR__) . '/pearconfig.xml');
unlink(dirname(__DIR__) . '/.config');
for ($i = 1; $i <= 3; $i++) {
    unlink(__DIR__ . "/glooby$i");
}
unlink(__DIR__ . '/package.xml');
$dir = dirname(__DIR__) . '/.configsnapshots';
include __DIR__ . '/../../../clean.php.inc';
$dir = dirname(__DIR__) . '/.xmlregistry';
include __DIR__ . '/../../../clean.php.inc';
unlink(dirname(__DIR__) . '/.pear2registry');
rmdir(dirname(__DIR__) . '/temp');
$dir = dirname(__DIR__) . '/PEAR2/.xmlregistry';
include __DIR__ . '/../../../clean.php.inc';
unlink(dirname(__DIR__) . '/PEAR2/.pear2registry');
rmdir(dirname(__DIR__) . '/PEAR2/temp');

