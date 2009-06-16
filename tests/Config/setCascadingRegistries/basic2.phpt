--TEST--
PEAR2_Pyrus_Config::setCascading Registries() basic test 2
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';

// check descending from php to registry directory
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'something/php');
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'something2');
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'something');
$fake = new Sqlite3(__DIR__ . DIRECTORY_SEPARATOR . 'something' . DIRECTORY_SEPARATOR . '.pear2registry');
$fake->close();
unset($fake);
$unused = new PEAR2_Pyrus_Registry(__DIR__ . '/something');
set_include_path(__DIR__ . DIRECTORY_SEPARATOR . 'something2' .
                 DIRECTORY_SEPARATOR . 'php' . PATH_SEPARATOR . dirname(__FILE__) . DIRECTORY_SEPARATOR . 'something/php');
chdir(__DIR__ . DIRECTORY_SEPARATOR . 'something2');
$c = $configclass::singleton(false, dirname(__FILE__) . '/something/blah');
restore_include_path();

$test->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'something2',
                    $c->registry->path, 'registry path 3');
$test->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'something',
                    $c->registry->parent->path, 'registry->parent path 3');
$test->assertNull($c->registry->parent->parent, 'registry parent parent 3');
$test->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'something2',
                    $c->channelregistry->path, 'channelregistry path 3');
$test->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'something',
                    $c->channelregistry->parent->path, 'channelregistry->parent path 3');
$test->assertNull($c->channelregistry->parent->parent, 'channelregistry parent parent 3');

// now check starting with unregisterable directory
if (substr(PHP_OS, 0, 3) != 'WIN') {
    // no way to do this on windows that I know of
    try {
        $c = $configclass::singleton('/', __DIR__ . '/something/blah');
        echo "ERROR: no exception thrown\n";
    } catch (Exception $e) {
        $test->assertEquals($configclass . '_Exception', get_class($e), 'exception class');
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
