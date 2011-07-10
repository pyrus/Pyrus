--TEST--
\Pyrus\Installer::prepare(), plugin package, plugin mode not enabled
--FILE--
<?php
use Pyrus\Package;
include __DIR__ . '/../setup.php.inc';
$pf = new \Pyrus\PackageFile\v2;

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

$package = new \Pyrus\Package(false);
$xmlcontainer = new \Pyrus\PackageFile($pf);
$xml = new \Pyrus\Package\Xml(TESTDIR . '/package.xml', $package, $xmlcontainer);
$package->setInternalPackage($xml);

file_put_contents(TESTDIR . '/foobar', '<?xml version="1.0" encoding="UTF-8"?>
<task version="2.0" xmlns="http://pear2.php.net/dtd/customtask-2.0">
 <name>burm</name>
 <class>Fronky_Wonky_Burm</class>
 <autoloadpath></autoloadpath>
</task>');
mkdir(TESTDIR . '/Fronky/Wonky', 0755, true);
file_put_contents(TESTDIR . '/Fronky/Wonky/Burm.php', '<?php
class Fronky_Wonky_Burm extends \Pyrus\Task\Common {
    const TYPE = "simple";
    const PHASE = \Pyrus\Task\Common::PACKAGEANDINSTALL;
    static function validateXml(\Pyrus\PackageInterface $pkg, $xml, $fileXml, $file)
    {
        return true;
    }

    function startSession($fp, $dest)
    {
        return true;
    }
}');

class b extends \Pyrus\Installer
{
    static $installPackages = array();
}

class mylog implements Pyrus\LogInterface
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
Pyrus\Logger::attach($log);

b::begin();
b::prepare($package);
$test->assertEquals(0, count(b::$installPackages), 'should not prepare this plugin');
$test->assertEquals(array('Skipping plugin pear2.php.net/testing2
Plugins modify the installer and cannot be installed at the same time as regular packages.
Add the -p option to manage plugins, for example:
 php pyrus.phar install -p pear2.php.net/testing2'),
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
