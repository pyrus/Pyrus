--TEST--
\pear2\Pyrus\Installer::prepare(), plugin package, plugin mode not enabled
--FILE--
<?php
use pear2\Pyrus\Package;
define('MYDIR', __DIR__);
include __DIR__ . '/../setup.php.inc';
$pf = new \pear2\Pyrus\PackageFile\v2;

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
$pf->setPackagefile(__DIR__ . '/testit/package.xml');

$package = new \pear2\Pyrus\Package(false);
$xmlcontainer = new \pear2\Pyrus\PackageFile($pf);
$xml = new \pear2\Pyrus\Package\Xml(__DIR__ . '/testit/package.xml', $package, $xmlcontainer);
$package->setInternalPackage($xml);

file_put_contents(__DIR__ . '/testit/foobar', '<?xml version="1.0" encoding="UTF-8"?>
<task version="2.0" xmlns="http://pear2.php.net/dtd/customtask-2.0">
 <name>burm</name>
 <class>Fronky_Wonky_Burm</class>
 <autoloadpath></autoloadpath>
</task>');
mkdir(__DIR__ . '/testit/Fronky/Wonky', 0755, true);
file_put_contents(__DIR__ . '/testit/Fronky/Wonky/Burm.php', '<?php
class Fronky_Wonky_Burm extends \pear2\Pyrus\Task\Common {
    const TYPE = "simple";
    const PHASE = \pear2\Pyrus\Task\Common::PACKAGEANDINSTALL;
    static function validateXml(\pear2\Pyrus\PackageInterface $pkg, $xml, $fileXml, $file)
    {
        return true;
    }

    function startSession($fp, $dest)
    {
        return true;
    }
}');

class b extends \pear2\Pyrus\Installer
{
    static $installPackages = array();
}

class mylog implements pear2\Pyrus\LogInterface
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
pear2\Pyrus\Logger::attach($log);

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
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===