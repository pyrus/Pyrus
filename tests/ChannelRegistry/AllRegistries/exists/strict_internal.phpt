--TEST--
\Pyrus\ChannelRegistry::exists() strict internal channel test
--FILE--
<?php
require dirname(__DIR__) . '/../setup.php.inc';
$c = getTestConfig();

foreach (array('pear'=>'pear.php.net',
               'pear2'=>'pear2.php.net',
               'pecl'=>'pecl.php.net') as $alias=>$name) {
    $test->assertEquals(false, $c->channelregistry->exists($alias, true), $alias.' channel alias should not exist');
    $test->assertEquals(true, $c->channelregistry->exists($name, true), $name.' channel name');
}
$test->assertEquals(true, $c->channelregistry->exists('__uri', true), '__uri channel');
$test->assertEquals(false, $c->channelregistry->exists('cookiemonster', true), 'fake channel does not exist, 1');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===
