--TEST--
\PEAR2\Pyrus\Config::setCascading Registries() basic test 2
--FILE--
<?php
require __DIR__ . '/setup.php.inc';

// check descending from php to registry directory
@mkdir(TESTDIR . DIRECTORY_SEPARATOR . 'something/php');
@mkdir(TESTDIR . DIRECTORY_SEPARATOR . 'something2');
@mkdir(TESTDIR . DIRECTORY_SEPARATOR . 'something');
$fake = new Sqlite3(TESTDIR . DIRECTORY_SEPARATOR . 'something' . DIRECTORY_SEPARATOR . '.pear2registry');
$fake->close();
unset($fake);
$unused = new \PEAR2\Pyrus\Registry(TESTDIR . '/something');
set_include_path(TESTDIR . DIRECTORY_SEPARATOR . 'something2' .
                 DIRECTORY_SEPARATOR . 'php' . PATH_SEPARATOR . TESTDIR . DIRECTORY_SEPARATOR . 'something/php');
chdir(TESTDIR . DIRECTORY_SEPARATOR . 'something2');
$c = $configclass::singleton(false, TESTDIR . '/something/blah');
restore_include_path();

$test->assertEquals(TESTDIR . DIRECTORY_SEPARATOR . 'something2',
                    $c->registry->path, 'registry path 3');
$test->assertEquals(TESTDIR . DIRECTORY_SEPARATOR . 'something',
                    $c->registry->getParent()->path, 'registry->getParent() path 3');
$test->assertNull($c->registry->getParent()->getParent(), 'registry->getParent()->getParent() parent 3');
$test->assertEquals(TESTDIR . DIRECTORY_SEPARATOR . 'something2',
                    $c->channelregistry->path, 'channelregistry path 3');
$test->assertEquals(TESTDIR . DIRECTORY_SEPARATOR . 'something',
                    $c->channelregistry->getParent()->path, 'channelregistry->getParent() path 3');
$test->assertNull($c->channelregistry->getParent()->getParent(), 'channelregistry->getParent()->getParent() 3');

// now check starting with unregisterable directory
if (substr(PHP_OS, 0, 3) != 'WIN') {
    // no way to do this on windows that I know of
    try {
        $c = $configclass::singleton('/', TESTDIR . '/something/blah');
        echo "ERROR: no exception thrown\n";
    } catch (Exception $e) {
        $test->assertEquals($configclass . '\Exception', get_class($e), 'exception class');
        $test->assertEquals('Cannot initialize primary registry in path /', $e->getMessage(), 'exception message');
    }
}
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/something2';
include __DIR__ . '/../../clean.php.inc';
?>
<?php
$dir = __DIR__ . '/something';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===
