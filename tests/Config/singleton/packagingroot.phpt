--TEST--
Config: packagingroot changes
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$first = PEAR2\Pyrus\Config::singleton(TESTDIR, TESTDIR . '//foo/config.xml');
$test->assertEquals(false, $first->hasPackagingRoot(), 'first');
PEAR2\Pyrus\Main::$options['packagingroot'] = TESTDIR;
$second = PEAR2\Pyrus\Config::singleton(TESTDIR, TESTDIR . '//foo/config.xml');
$test->assertEquals(false, $first->hasPackagingRoot(), 'first 2');
$test->assertEquals(true, $second->hasPackagingRoot(), 'second');
$third = PEAR2\Pyrus\Config::singleton(TESTDIR, TESTDIR . '//foo/config.xml');
$test->assertEquals(true, $third->hasPackagingRoot(), 'third');
unset(PEAR2\Pyrus\Main::$options['packagingroot']);
$fourth = PEAR2\Pyrus\Config::singleton(TESTDIR, TESTDIR . '//foo/config.xml');
$test->assertEquals(false, $fourth->hasPackagingRoot(), 'fourth');
?>
===DONE===
--EXPECT--
===DONE===