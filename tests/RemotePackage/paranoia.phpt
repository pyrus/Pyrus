--TEST--
\pear2\Pyrus\Channel\RemotePackage paranoia settings
--SKIPIF--
<?php
if (!extension_loaded('openssl')) die('SKIP openssl required');
?>
--FILE--
<?php
define('MYDIR', __DIR__);
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../Mocks/Internet/paranoid',
                       'http://pear2.php.net/');
\pear2\Pyrus\Main::$downloadClass = 'Internet';
class Logger implements pear2\Pyrus\ILog
{
    public $log = array();
    function log($level, $message)
    {
        if (!$level) $this->log[] = $message;
    }
}
$logger = new Logger;
pear2\Pyrus\Logger::attach($logger);

$remote = \pear2\Pyrus\Config::current()->channelregistry['pear2.php.net']->remotepackage['P1'];
$fake = new pear2\Pyrus\PackageFile\v2;
$fakedep = $fake->dependencies['required']->package['pear2.php.net/P1'];

pear2\Pyrus\Installer::begin();
pear2\Pyrus\Installer::prepare(new pear2\Pyrus\Package('P1-1.0.0'));
pear2\Pyrus\Installer::commit();

pear2\Pyrus\Main::$paranoid = 1;
pear2\Pyrus\Main::$options['upgrade'] = true;
$remote->figureOutBestVersion($fakedep);
$test->assertEquals('2.0.0', $remote->version['release'], 'paranoid 1');

// this allows us to test API of X.Y as well as X
pear2\Pyrus\Installer::begin();
pear2\Pyrus\Installer::prepare(new pear2\Pyrus\Package('P1-1.0.1'));
pear2\Pyrus\Installer::commit();

$remote = \pear2\Pyrus\Config::current()->channelregistry['pear2.php.net']->remotepackage['P1'];
pear2\Pyrus\Main::$paranoid = 2;
$remote->figureOutBestVersion($fakedep);
$test->assertEquals('1.1.0', $remote->version['release'], 'paranoid 2');

$remote = \pear2\Pyrus\Config::current()->channelregistry['pear2.php.net']->remotepackage['P1'];
pear2\Pyrus\Main::$paranoid = 3;
$remote->figureOutBestVersion($fakedep);
$test->assertEquals('1.0.3', $remote->version['release'], 'paranoid 3');

$remote = \pear2\Pyrus\Config::current()->channelregistry['pear2.php.net']->remotepackage['P1'];
pear2\Pyrus\Main::$paranoid = 4;
$remote->figureOutBestVersion($fakedep);
$test->assertEquals('1.0.2', $remote->version['release'], 'paranoid 4');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===