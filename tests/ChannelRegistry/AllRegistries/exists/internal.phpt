--TEST--
\PEAR2\Pyrus\ChannelRegistry::exists() basic test
--FILE--
<?php
require dirname(__DIR__) . '/../setup.php.inc';
$c = getTestConfig();

foreach (array('pear'=>'pear.php.net',
               'pear2'=>'pear2.php.net',
               'pecl'=>'pecl.php.net') as $alias=>$name) {
    $test->assertEquals(true, $c->channelregistry->exists($alias, false), $alias.' channel alias');
    $test->assertEquals(true, $c->channelregistry->exists($name, false), $name.' channel name');
}
$test->assertEquals(true, $c->channelregistry->exists('__uri', false), '__uri channel');
$test->assertEquals(false, $c->channelregistry->exists('cookiemonster', false), 'fake channel does not exist, 1');

$test->assertEquals(true, isset($c->channelregistry['__uri']), '__uri channel 2');
$test->assertEquals(false, isset($c->channelregistry['cookiemonster']), 'fake channel does not exist, 2');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===
