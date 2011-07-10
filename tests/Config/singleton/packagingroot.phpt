--TEST--
Config: packagingroot changes
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$first = Pyrus\Config::singleton(TESTDIR, TESTDIR . '//foo/config.xml');
$test->assertEquals(false, $first->hasPackagingRoot(), 'first');
Pyrus\Main::$options['packagingroot'] = TESTDIR;
$second = Pyrus\Config::singleton(TESTDIR, TESTDIR . '//foo/config.xml');
$test->assertEquals(false, $first->hasPackagingRoot(), 'first 2');
$test->assertEquals(true, $second->hasPackagingRoot(), 'second');
$third = Pyrus\Config::singleton(TESTDIR, TESTDIR . '//foo/config.xml');
$test->assertEquals(true, $third->hasPackagingRoot(), 'third');
unset(Pyrus\Main::$options['packagingroot']);
$fourth = Pyrus\Config::singleton(TESTDIR, TESTDIR . '//foo/config.xml');
$test->assertEquals(false, $fourth->hasPackagingRoot(), 'fourth');
?>
===DONE===
--EXPECT--
===DONE===