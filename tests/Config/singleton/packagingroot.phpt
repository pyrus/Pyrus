--TEST--
Config: packagingroot changes
--FILE--
<?php
require dirname(__FILE__) . '/../setup.php.inc';
$first = pear2\Pyrus\Config::singleton(__DIR__ . '/testit', __DIR__ . '/testit/foo/config.xml');
$test->assertEquals(false, $first->hasPackagingRoot(), 'first');
pear2\Pyrus\Main::$options['packagingroot'] = __DIR__ . '/testit';
$second = pear2\Pyrus\Config::singleton(__DIR__ . '/testit', __DIR__ . '/testit/foo/config.xml');
$test->assertEquals(false, $first->hasPackagingRoot(), 'first 2');
$test->assertEquals(true, $second->hasPackagingRoot(), 'second');
$third = pear2\Pyrus\Config::singleton(__DIR__ . '/testit', __DIR__ . '/testit/foo/config.xml');
$test->assertEquals(true, $third->hasPackagingRoot(), 'third');
unset(pear2\Pyrus\Main::$options['packagingroot']);
$fourth = pear2\Pyrus\Config::singleton(__DIR__ . '/testit', __DIR__ . '/testit/foo/config.xml');
$test->assertEquals(false, $fourth->hasPackagingRoot(), 'fourth');
?>
===DONE===
--EXPECT--
===DONE===