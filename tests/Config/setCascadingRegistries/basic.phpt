--TEST--
PEAR2_Pyrus_Config::setCascading Registries() basic test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'foo');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'foo');
$unused = $configclass::singleton(__DIR__ . '/foo', __DIR__ . '/something/blah');
$c = $configclass::singleton(dirname(__FILE__) . '/something' . PATH_SEPARATOR . dirname(__FILE__) . '/foo', dirname(__FILE__) . '/something/blah');
restore_include_path();
$test->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'something', $c->registry->path, 'registry path');
$test->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'foo', $c->registry->parent->path, 'registry->parent path');
$test->assertNull($c->registry->parent->parent, 'registry parent parent');
$test->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'something', $c->channelregistry->path, 'channelregistry path');
$test->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'foo', $c->channelregistry->parent->path, 'channelregistry->parent path');
$test->assertNull($c->channelregistry->parent->parent, 'channelregistry parent parent');

unset($c);
// test to see if 2nd call to singleton returns the same object
$c = $configclass::singleton(dirname(__FILE__) . '/something' . PATH_SEPARATOR . dirname(__FILE__) . '/foo', dirname(__FILE__) . '/something/blah');
$test->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'something', $c->registry->path, 'registry path');
$test->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'foo', $c->registry->parent->path, 'registry->parent path');
$test->assertNull($c->registry->parent->parent, 'registry parent parent');
$test->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'something', $c->channelregistry->path, 'channelregistry path');
$test->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'foo', $c->channelregistry->parent->path, 'channelregistry->parent path');
$test->assertNull($c->channelregistry->parent->parent, 'channelregistry parent parent');

// now check descending from src to registry directory
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'something/src');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'something/src');
$c = $configclass::singleton(dirname(__FILE__) . '/something2', dirname(__FILE__) . '/something/blah');
$test->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'something2', $c->registry->path, 'registry path');
$test->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'something', $c->registry->parent->path, 'registry->parent path');
$test->assertNull($c->registry->parent->parent, 'registry parent parent');
$test->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'something2', $c->channelregistry->path, 'channelregistry path');
$test->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'something', $c->channelregistry->parent->path, 'channelregistry->parent path');
$test->assertNull($c->channelregistry->parent->parent, 'channelregistry parent parent');

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
$dir = __DIR__ . '/foo';
include __DIR__ . '/../../clean.php.inc';
?>
<?php
$dir = __DIR__ . '/something';
include __DIR__ . '/../../clean.php.inc';
?>
<?php
$dir = __DIR__ . '/something2';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===
