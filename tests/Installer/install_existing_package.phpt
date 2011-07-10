--TEST--
\Pyrus\Installer::prepare() installed package is the same or newer version
--FILE--
<?php
include __DIR__ . '/../test_framework.php.inc';
$package = new \Pyrus\Package(__DIR__.'/../Mocks/SimpleChannelServer/package.xml');
$c = getTestConfig();

class TestLog implements \Pyrus\LogInterface
{
    protected $messages = array();
    
    function log($level, $message)
    {
        $this->messages[] = $message;
    }
    
    function getMessages()
    {
        return $this->messages;
    }
}

\Pyrus\Installer::begin();
\Pyrus\Installer::prepare($package);
\Pyrus\Installer::commit();
$observer = new TestLog();
\Pyrus\Logger::attach($observer);
\Pyrus\Installer::begin();
\Pyrus\Installer::prepare($package);
\Pyrus\Installer::commit();
$test->assertEquals(true, in_array("Skipping installed package {$package->channel}/{$package->name}", $observer->getMessages()), 'package already installed');

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===