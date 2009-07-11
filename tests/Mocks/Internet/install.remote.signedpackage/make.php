<?php

/**
 * To rebuild this test, you need to set the environment variable
 * CERT to the path to your CA-signed PKCS12 private key certificate.
 */

if (!isset($_ENV['CERT'])) {
    die('set the CERT environment variable to the /path/to/cert' . PATH_SEPARATOR . 'password');
}
if (!strpos($_ENV['CERT'], PATH_SEPARATOR)) {
    die('set the CERT environment variable to the /path/to/cert' . PATH_SEPARATOR . 'password');
}
$cert = explode(PATH_SEPARATOR, $_ENV['CERT']);
if (count($cert) != 2 || !file_exists($cert[0])) {
    die('set the CERT environment variable to the /path/to/cert' . PATH_SEPARATOR . 'password');
}

$certinfo = array();
$pkcs = openssl_pkcs12_read(file_get_contents($cert[0]), $certinfo, $cert[1]);
if (!$pkcs) {
    die('Invalid certificate: ' . $cert[0] . ', or invalid password');
}

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

for ($i = 1; $i <= 1; $i++) {
    file_put_contents(__DIR__ . "/glooby$i", 'hi');
}

$pf->name = 'P1';
$pf->channel = 'pear2.php.net';
$pf->summary = 'testing';
$pf->version['release'] = '1.0.0';
$pf->stability['release'] = 'stable';
$pf->description = 'hi description';
$pf->notes = 'my notes';
$pf->maintainer['cellog']->role('lead')->email('greg@chiaraquartet.net')->active('yes')->name('Greg Beaver');
$pf->files['glooby1'] = array('role' => 'php');

$pf->setPackagefile(__DIR__ . '/package.xml');

file_put_contents(__DIR__ . '/package.xml', $pf);

$package1 = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($pf);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/package.xml', $package1, $xmlcontainer);
$package1->setInternalPackage($xml);
$package1->archivefile = __DIR__ . '/package.xml';
$scs->saveRelease($package1, 'cellog', $cert[0], $cert[1]);

// clean up
unlink(dirname(__DIR__) . '/pearconfig.xml');
unlink(dirname(__DIR__) . '/.config');
for ($i = 1; $i <= 1; $i++) {
    unlink(__DIR__ . "/glooby$i");
}
unlink(__DIR__ . '/package.xml');
$dir = dirname(__DIR__) . '/.configsnapshots';
include __DIR__ . '/../../../clean.php.inc';
rmdir(dirname(__DIR__) . '/PEAR2/temp');
