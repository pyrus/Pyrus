<?php
include dirname(__FILE__) . '/../test_framework.php.inc';
$pf = new \PEAR2\Pyrus\PackageFile\v2;

$pf->name = 'testing2';
$pf->channel = 'pear2.php.net';
$pf->summary = 'testing';
$pf->description = 'hi description';
$pf->notes = 'my notes';
$pf->maintainer['cellog']->role('lead')->email('cellog@php.net')->active('yes')->name('Greg Beaver');
$pf->files['foobar'] = array(
    'attribs' => array('role' => 'php'),
    'tasks:replace' => array('attribs' =>
                             array('from' => '@blah@', 'to' => 'version', 'type' => 'package-info'))
);
$pf->files['glooby'] = array('role' => 'php');
$pf->release[0]->installAs('foobar', 'blah/blah');
$pf->release[0]->ignore('glooby');
$pf->setPackagefile(__DIR__ . '/testit/package.xml');

$package = new \PEAR2\Pyrus\Package(false);
$xmlcontainer = new \PEAR2\Pyrus\PackageFile($pf);
$xml = new \PEAR2\Pyrus\Package\Xml(__DIR__ . '/testit/package.xml', $package, $xmlcontainer);
$package->setInternalPackage($xml);
