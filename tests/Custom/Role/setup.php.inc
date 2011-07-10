<?php
include __DIR__ . '/../../test_framework.php.inc';
$c = getTestConfig();
$c->plugins_dir = TESTDIR . '/plugins';

$pf = new \Pyrus\PackageFile\v2;

$pf->name = 'testing2';
$pf->channel = 'pear2.php.net';
$pf->summary = 'testing';
$pf->description = 'hi description';
$pf->notes = 'my notes';
$pf->maintainer['cellog']->role('lead')->email('cellog@php.net')->active('yes')->name('Greg Beaver');
$pf->files['foobar'] = array(
    'attribs' => array('role' => 'customrole'),
);
$pf->files['Fronky/Wonky/Burm.php'] = array(
    'attribs' => array('role' => 'php'),
);
$pf->setPackagefile(TESTDIR . '/package.xml');

$package = new \Pyrus\Package(false);
$xmlcontainer = new \Pyrus\PackageFile($pf);
$xml = new \Pyrus\Package\Xml(TESTDIR . '/package.xml', $package, $xmlcontainer);
$package->setInternalPackage($xml);
