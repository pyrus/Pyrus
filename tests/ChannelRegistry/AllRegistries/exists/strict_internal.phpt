--TEST--
\PEAR2\Pyrus\ChannelRegistry::exists() strict internal channel test
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/../setup.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = \PEAR2\Pyrus\Config::singleton(__DIR__.'/testit', __DIR__ . '/testit/plugins/pearconfig.xml');
restore_include_path();
$c->saveConfig();
foreach (array('pear'=>'pear.php.net',
               'pear2'=>'pear2.php.net',
               'pecl'=>'pecl.php.net') as $alias=>$name) {
    $test->assertEquals(false, $c->channelregistry->exists($alias, true), $alias.' channel alias should not exist');
    $test->assertEquals(1, $c->channelregistry->exists($name, true), $name.' channel name');
}
$test->assertEquals(1, $c->channelregistry->exists('__uri', true), '__uri channel');
$test->assertEquals(false, $c->channelregistry->exists('cookiemonster', true), 'fake channel does not exist, 1');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===