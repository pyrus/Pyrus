--TEST--
package.xml v2.0 validator: validating, fail because of custom role not known
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
@mkdir(TESTDIR);
$pf = new \PEAR2\Pyrus\PackageFile\v2;

$pf->name = 'testing';
$pf->channel = 'pear2.php.net';
$pf->summary = 'testing';
$pf->description = 'hi description';
$pf->notes = 'my notes';
$pf->maintainer['cellog']->role('lead')->email('cellog@php.net')->active('yes')->name('Greg Beaver');
$pf->files['foobar'] = array(
    'attribs' => array('role' => 'foo'),
    'tasks:replace' => array('attribs' =>
                             array('from' => '@blah@', 'to' => 'version', 'type' => 'package-info'))
);
$pf->usesrole['foo']->package('Foo')->channel('pear2.php.net');
$pf->setPackagefile(TESTDIR . '/package.xml');

$package = new \PEAR2\Pyrus\Package(false);
$xmlcontainer = new \PEAR2\Pyrus\PackageFile($pf);
$xml = new \PEAR2\Pyrus\Package\Xml(TESTDIR . '/package.xml', $package, $xmlcontainer);
$package->setInternalPackage($xml);

try {
    $pf->getValidator()->validate($package);
    throw new Exception('passed and should have failed');
} catch (\PEAR2\Pyrus\PackageFile\Exception $e) {
    $test->assertEquals('Invalid package.xml', $e->getMessage(), 'basic message');
    $causes = array();
    $e->getCauseMessage($causes);
    $test->assertEquals('This package contains role "foo" and requires package "channel://pear2.php.net/Foo" to be used', $causes[1]['message'], 'blah');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===