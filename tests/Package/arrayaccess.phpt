--TEST--
PEAR2_Pyrus_Package: test array access for accessing files
--FILE--
<?php
define('MYDIR', __DIR__);
include __DIR__ . '/setup.php.inc';
$pf = new PEAR2_Pyrus_PackageFile_v2;

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
$pf->setPackagefile(__DIR__ . '/testit/package.xml');

$package = new PEAR2_Pyrus_Package(false);
$xmlcontainer = new PEAR2_Pyrus_PackageFile($pf);
$xml = new PEAR2_Pyrus_Package_Xml(__DIR__ . '/testit/package.xml', $package, $xmlcontainer);
$package->setInternalPackage($xml);

file_put_contents(__DIR__ . '/testit/foobar', 'hi there');

// does nothing
$package['blah'] = 1;
// does nothing
unset($package['blah']);

$test->assertTrue(isset($package['foobar']), 'isset(foobar)');
$test->assertFalse(isset($package['notset']), 'isset(notset)');
$test->assertEquals(array (
  'attribs' => 
  array (
    'role' => 'php',
    'name' => 'foobar',
  ),
  'tasks:replace' => 
  array (
    'attribs' => 
    array (
      'from' => '@blah@',
      'to' => 'version',
      'type' => 'package-info',
    ),
  ),
)
, $package['foobar'], 'contents');
$test->assertEquals('hi there', $package['contents://foobar'], 'contents://foobar');
$test->assertFalse($package->isRemote(), 'isRemote');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===