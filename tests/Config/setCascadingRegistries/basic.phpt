--TEST--
\pear2\Pyrus\Config::setCascading Registries() basic test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'foo');

set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'foo');

$fake = new Sqlite3(__DIR__ . DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR . '.pear2registry');
$fake->close();
unset($fake);
$unused = $configclass::singleton(__DIR__ . '/foo',
                                  __DIR__ . '/something/blah');

$c = $configclass::singleton(dirname(__FILE__) . '/something' . PATH_SEPARATOR . dirname(__FILE__) . '/foo',
                             dirname(__FILE__) . '/something/blah');
restore_include_path();

$test->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'something',
                    $c->registry->path, 'registry path 1');
$test->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'foo',
                    $c->registry->parent->path, 'registry->parent path 1');
$test->assertNull($c->registry->parent->parent, 'registry parent parent 1');
$test->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'something',
                    $c->channelregistry->path, 'channelregistry path 1');
$test->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'foo',
                    $c->channelregistry->parent->path, 'channelregistry->parent path 1');
$test->assertNull($c->channelregistry->parent->parent, 'channelregistry parent parent 1');

unset($c);
// test to see if 2nd call to singleton returns the same object
$c = $configclass::singleton(dirname(__FILE__) . '/something' . PATH_SEPARATOR . dirname(__FILE__) . '/foo',
                             dirname(__FILE__) . '/something/blah');

$test->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'something',
                    $c->registry->path, 'registry path 2');
$test->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'foo',
                    $c->registry->parent->path, 'registry->parent path 2');
$test->assertNull($c->registry->parent->parent, 'registry parent parent 2');
$test->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'something',
                    $c->channelregistry->path, 'channelregistry path 2');
$test->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'foo',
                    $c->channelregistry->parent->path, 'channelregistry->parent path 2');
$test->assertNull($c->channelregistry->parent->parent, 'channelregistry parent parent 2');

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
--EXPECT--
===DONE===
