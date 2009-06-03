<?php
include __DIR__ . '/../test_framework.php.inc';
$pf = new PEAR2_Pyrus_PackageFile_v2;
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/testit', __DIR__ . '/testit/plugins/pearconfig.xml');
$c->bin_dir = __DIR__ . '/testit/bin';
restore_include_path();
$c->saveConfig();

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

    function run(array $answers, $section)
    {
        self::$captured[] = array('answers' => $answers, 'section' => $section);
    }

    function init($pkg, $lastversion)
    {
        self::$captured[] = array('init' => array($pkg->channel . '/' . $pkg->name, $lastversion));
    }
}

class fakefrontend
{
    static $promptmap = array();
    static $displayed = array();

    static function reset()
    {
        self::$promptmap = array();
        self::$displayed = array();
    }

    static function addPrompts($answers)
    {
        self::$promptmap[] = $answers;
    }

    function display($stuff)
    {
        self::$displayed[] = $stuff;
    }

    function confirmDialog($prompts)
    {
        self::$displayed[] = $prompts;
        return next(self::$promptmap);
    }
}