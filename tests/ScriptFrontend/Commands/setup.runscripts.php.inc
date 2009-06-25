<?php
define('MYDIR', __DIR__);
require __DIR__ . '/setup.php.inc';
$pf = new PEAR2_Pyrus_PackageFile_v2;

mkdir(__DIR__ . '/testit/php');
file_put_contents(__DIR__ . '/testit/php/foobar', '<?php
class foobar_postinstall extends fake {}');
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

class fake
{
    public static $captured = array();

    static function reset()
    {
        self::$captured = array();
    }

    function run2(array $answers, $section)
    {
        self::$captured[] = array('answers' => $answers, 'section' => $section);
    }

    function init2($pkg, $lastversion)
    {
        self::$captured[] = array('init' => array($pkg->channel . '/' . $pkg->name, $lastversion));
    }
}

$postinstall = $package->files['foobar']->postinstallscript->add();

$postinstall->paramgroup['first']->param['paramname']->type('string')->prompt('paramname');
$postinstall->paramgroup['first']->param['paramname2']->type('string')->prompt('paramname2')->default('foo');
$postinstall->paramgroup['first']->instructions = "testing\nthis thing";

$postinstall->paramgroup['second']->save();

$postinstall->paramgroup['third']->param['paramname']->type('string')->prompt('2 paramname');
$postinstall->paramgroup['third']->param['paramname2']->type('string')->prompt('2 paramname2');

$postinstall->paramgroup['fourth']->param['paramname']->type('string')->prompt('3 paramname');
$postinstall->paramgroup['fourth']->param['paramname2']->type('string')->prompt('3 paramname2');

PEAR2_Pyrus_Config::current()->registry->package[] = $package;
