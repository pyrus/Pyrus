--TEST--
\PEAR2\Pyrus\Config::setCascading Registries() basic test
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
@mkdir(TESTDIR . DIRECTORY_SEPARATOR . 'foo');

set_include_path(TESTDIR . DIRECTORY_SEPARATOR . 'foo');

$fake = new Sqlite3(TESTDIR . DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR . '.pear2registry');
$fake->close();
unset($fake);
$unused = $configclass::singleton(TESTDIR . '/foo',
                                  TESTDIR . '/something/blah');

$c = $configclass::singleton(TESTDIR . '/something' . PATH_SEPARATOR . TESTDIR . '/foo',
                             TESTDIR . '/something/blah');
restore_include_path();

$test->assertEquals(TESTDIR . DIRECTORY_SEPARATOR . 'something',
                    $c->registry->path, 'registry path 1');
$test->assertEquals(TESTDIR . DIRECTORY_SEPARATOR . 'foo',
                    $c->registry->getParent()->path, 'registry->parent path 1');
$test->assertNull($c->registry->getParent()->getParent(), 'registry parent parent 1');
$test->assertEquals(TESTDIR . DIRECTORY_SEPARATOR . 'something',
                    $c->channelregistry->path, 'channelregistry path 1');
$test->assertEquals(TESTDIR . DIRECTORY_SEPARATOR . 'foo',
                    $c->channelregistry->getParent()->path, 'channelregistry->parent path 1');
$test->assertNull($c->channelregistry->getParent()->getParent(), 'channelregistry parent parent 1');

unset($c);
// test to see if 2nd call to singleton returns the same object
$c = $configclass::singleton(TESTDIR . '/something' . PATH_SEPARATOR . TESTDIR . '/foo',
                             TESTDIR . '/something/blah');

$test->assertEquals(TESTDIR . DIRECTORY_SEPARATOR . 'something',
                    $c->registry->path, 'registry path 2');
$test->assertEquals(TESTDIR . DIRECTORY_SEPARATOR . 'foo',
                    $c->registry->getParent()->path, 'registry->parent path 2');
$test->assertNull($c->registry->getParent()->getParent(), 'registry parent parent 2');
$test->assertEquals(TESTDIR . DIRECTORY_SEPARATOR . 'something',
                    $c->channelregistry->path, 'channelregistry path 2');
$test->assertEquals(TESTDIR . DIRECTORY_SEPARATOR . 'foo',
                    $c->channelregistry->getParent()->path, 'channelregistry->parent path 2');
$test->assertNull($c->channelregistry->getParent()->getParent(), 'channelregistry parent parent 2');

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===
