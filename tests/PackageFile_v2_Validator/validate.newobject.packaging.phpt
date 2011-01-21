--TEST--
package.xml v2.0 validator: validating from-object (not from parsed package.xml)
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
@mkdir(TESTDIR);
$pf = new \PEAR2\Pyrus\PackageFile\v2;

$pf->name = 'testing2';
$pf->{'extends'} = 'testing';
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
$pf->setPackagefile(TESTDIR . '/package.xml');

file_put_contents(TESTDIR . '/foobar', '<?php
interface testing
{
    function thing();
}
class blahblah extends another implements ArrayAccess, testing
{
    /**
     * @nodep stdClass
     */
    function thing()
    {
        $a = new stdClass;
        $b::thing();
        blahblah::thing();
        parent::thing();
        $my[\'thing\'] = ($t + 1);
        $a = <<<AA
        hi
AA;
    }
}

function foo(){}
');
file_put_contents(TESTDIR . '/glooby', 'foo');

$package = new \PEAR2\Pyrus\Package(false);
$xmlcontainer = new \PEAR2\Pyrus\PackageFile($pf);
$xml = new \PEAR2\Pyrus\Package\Xml(TESTDIR . '/package.xml', $package, $xmlcontainer);
$package->setInternalPackage($xml);

$test->assertEquals(\PEAR2\Pyrus\Validate::PACKAGING, $pf->getValidator()->validate($package, \PEAR2\Pyrus\Validate::PACKAGING), 'validate');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===