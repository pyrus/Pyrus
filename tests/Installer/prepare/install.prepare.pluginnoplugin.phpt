--TEST--
\PEAR2\Pyrus\Installer::prepare(), plugin package, plugin mode not enabled
--FILE--
<?php
use PEAR2\Pyrus\Package;
include __DIR__ . '/../setup.php.inc';
$pf = new \PEAR2\Pyrus\PackageFile\v2;

$pf->name = 'testing2';
$pf->channel = 'pear2.php.net';
$pf->summary = 'testing';
$pf->description = 'hi description';
$pf->notes = 'my notes';
$pf->maintainer['cellog']->role('lead')->email('cellog@php.net')->active('yes')->name('Greg Beaver');
$pf->files['foobar'] = array(
    'attribs' => array('role' => 'customcommand'),
);
$pf->files['Fronky/Wonky/Burm.php'] = array(
    'attribs' => array('role' => 'php'),
);
$pf->setPackagefile(TESTDIR . '/package.xml');

$package = new \PEAR2\Pyrus\Package(false);
$xmlcontainer = new \PEAR2\Pyrus\PackageFile($pf);
$xml = new \PEAR2\Pyrus\Package\Xml(TESTDIR . '/package.xml', $package, $xmlcontainer);
$package->setInternalPackage($xml);

file_put_contents(TESTDIR . '/foobar', '<?xml version="1.0" encoding="UTF-8"?>
<task version="2.0" xmlns="http://pear2.php.net/dtd/customtask-2.0">
 <name>burm</name>
 <class>Fronky_Wonky_Burm</class>
 <autoloadpath></autoloadpath>
</task>');
mkdir(TESTDIR . '/Fronky/Wonky', 0755, true);
file_put_contents(TESTDIR . '/Fronky/Wonky/Burm.php', '<?php
class Fronky_Wonky_Burm extends \PEAR2\Pyrus\Task\Common {
    const TYPE = "simple";
    const PHASE = \PEAR2\Pyrus\Task\Common::PACKAGEANDINSTALL;
    static function validateXml(\PEAR2\Pyrus\PackageInterface $pkg, $xml, $fileXml, $file)
    {
        return true;
    }

    function startSession($fp, $dest)
    {
        return true;
    }
}');

class b extends \PEAR2\Pyrus\Installer
{
    static $installPackages = array();
}

class mylog implements PEAR2\Pyrus\LogInterface
{
    static $log = array();
    function log($level, $message)
    {
        if ($level == 0) {
            self::$log[] = $message;
        }
    }
}
$log = new mylog;
PEAR2\Pyrus\Logger::attach($log);

b::begin();
b::prepare($package);
$test->assertEquals(0, count(b::$installPackages), 'should not prepare this plugin');
$test->assertEquals(array('Skipping plugin pear2.php.net/testing2, use install -p/upgrade -p to manage plugins'),
                    $log::$log, 'log');
b::rollback();
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===