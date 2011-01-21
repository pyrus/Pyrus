<?php
include __DIR__ . '/../test_framework.php.inc';
$pf = new \PEAR2\Pyrus\PackageFile\v2;
$c = getTestConfig();

mkdir(TESTDIR . '/php');
file_put_contents(TESTDIR . '/php/foobar', '<?php
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
$pf->setPackagefile(TESTDIR . '/package.xml');

$package = new \PEAR2\Pyrus\Package(false);
$xmlcontainer = new \PEAR2\Pyrus\PackageFile($pf);
$xml = new \PEAR2\Pyrus\Package\Xml(TESTDIR . '/package.xml', $package, $xmlcontainer);
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