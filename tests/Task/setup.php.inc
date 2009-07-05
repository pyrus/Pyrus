<?php
include dirname(__FILE__) . '/../test_framework.php.inc';
@mkdir(MYDIR . DIRECTORY_SEPARATOR . 'testit');
set_include_path(MYDIR . DIRECTORY_SEPARATOR . 'testit');
$c = \pear2\Pyrus\Config::singleton(MYDIR.'/testit', MYDIR . '/testit/plugins/pearconfig.xml');
$c->bin_dir = MYDIR . '/testit/bin';
restore_include_path();
$c->saveConfig();

$pf = new \pear2\Pyrus\PackageFile\v2;

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
$pf->setPackagefile(MYDIR . '/testit/package.xml');

$package = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($pf);
$xml = new \pear2\Pyrus\Package\Xml(MYDIR . '/testit/package.xml', $package, $xmlcontainer);
$package->setInternalPackage($xml);
