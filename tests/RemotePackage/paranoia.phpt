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

$remote = \pear2\Pyrus\Config::current()->channelregistry['pear2.php.net']->remotepackage['P1'];
$fake = new pear2\Pyrus\PackageFile\v2;
$fakedep = $fake->dependencies['required']->package['pear2.php.net/P1'];

pear2\Pyrus\Installer::begin();
pear2\Pyrus\Installer::prepare(new pear2\Pyrus\Package('P1-1.0.0'));
pear2\Pyrus\Installer::commit();

pear2\Pyrus\Main::$paranoid = 1;
pear2\Pyrus\Main::$options['upgrade'] = true;
pear2\Pyrus\Logger::attach($logger);
$remote->figureOutBestVersion($fakedep);
$test->assertEquals('2.0.0', $remote->version['release'], 'paranoid 1');
pear2\Pyrus\Logger::detach($logger);
$test->assertEquals(array(), $logger->log, 'paranoid 1 log');
$logger->log = array(
);

// this allows us to test API of X.Y as well as X
pear2\Pyrus\Installer::begin();
pear2\Pyrus\Installer::prepare(new pear2\Pyrus\Package('P1-1.0.1'));
pear2\Pyrus\Installer::commit();

$remote = \pear2\Pyrus\Config::current()->channelregistry['pear2.php.net']->remotepackage['P1'];
pear2\Pyrus\Main::$paranoid = 2;
pear2\Pyrus\Logger::attach($logger);
$remote->figureOutBestVersion($fakedep);
$test->assertEquals('1.1.0', $remote->version['release'], 'paranoid 2');
pear2\Pyrus\Logger::detach($logger);
$test->assertEquals(array(
  0 => 'Skipping pear2.php.net/P1 version 2.0.0, API breaks backwards compatibility',
), $logger->log, 'paranoid 2 log');
$logger->log = array();

$remote = \pear2\Pyrus\Config::current()->channelregistry['pear2.php.net']->remotepackage['P1'];
pear2\Pyrus\Main::$paranoid = 3;
pear2\Pyrus\Logger::attach($logger);
$remote->figureOutBestVersion($fakedep);
$test->assertEquals('1.0.3', $remote->version['release'], 'paranoid 3');
pear2\Pyrus\Logger::detach($logger);
$test->assertEquals(array(
  0 => 'Skipping pear2.php.net/P1 version 2.0.0, API breaks backwards compatibility',
  1 => 'Skipping pear2.php.net/P1 version 1.1.0, API has added new features',
), $logger->log, 'paranoid 3 log');
$logger->log = array();

$remote = \pear2\Pyrus\Config::current()->channelregistry['pear2.php.net']->remotepackage['P1'];
pear2\Pyrus\Main::$paranoid = 5;
pear2\Pyrus\Logger::attach($logger);
$remote->figureOutBestVersion($fakedep);
$test->assertEquals('1.0.2', $remote->version['release'], 'paranoid 4');
pear2\Pyrus\Logger::detach($logger);
$test->assertEquals(array(
  0 => 'Skipping pear2.php.net/P1 version 2.0.0, API has changed',
  1 => 'Skipping pear2.php.net/P1 version 1.1.0, API has changed',
  2 => 'Skipping pear2.php.net/P1 version 1.0.3, API has changed',
), $logger->log, 'paranoid 4 log');

$chan = \pear2\Pyrus\Config::current()->channelregistry['pear2.php.net'];
unset($chan->protocols->rest['REST1.3']);
$remote = $chan->remotepackage['P1'];
$fake = new pear2\Pyrus\PackageFile\v2;
$fakedep = $fake->dependencies['required']->package['pear2.php.net/P1'];

pear2\Pyrus\Main::$paranoid = 2;
pear2\Pyrus\Main::$options['upgrade'] = true;
try {
    $remote->figureOutBestVersion($fakedep);
    throw new Exception('worked and should not');
} catch (\pear2\Pyrus\Channel\Exception $e) {
    $test->assertEquals('Channel pear2.php.net does not support a paranoia greater than 1', $e->getMessage(),
                        'error message');
}
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===