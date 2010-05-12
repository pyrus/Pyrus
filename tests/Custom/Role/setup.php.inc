<?php
include dirname(__FILE__) . '/../../test_framework.php.inc';
@mkdir(__DIR__ . DIRECTORY_SEPARATOR . 'testit');
set_include_path(__DIR__ . DIRECTORY_SEPARATOR . 'testit');
$c = \PEAR2\Pyrus\Config::singleton(__DIR__ . '/testit', __DIR__ . '/testit/plugins/pearconfig.xml');
$c->bin_dir = __DIR__ . '/testit/bin';
$c->plugins_dir = __DIR__ . '/testit/plugins';
restore_include_path();
$c->saveConfig();

$pf = new \PEAR2\Pyrus\PackageFile\v2;

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
$pf->setPackagefile(__DIR__ . '/testit/package.xml');

$package = new \PEAR2\Pyrus\Package(false);
$xmlcontainer = new \PEAR2\Pyrus\PackageFile($pf);
$xml = new \PEAR2\Pyrus\Package\Xml(__DIR__ . '/testit/package.xml', $package, $xmlcontainer);
$package->setInternalPackage($xml);
