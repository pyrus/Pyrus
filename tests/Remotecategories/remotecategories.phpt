--TEST--
\pear2\Pyrus\Channel\Remotecategories: basic test
--FILE--
<?php

define('MYDIR', __DIR__);
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../Mocks/Internet/remotepackage',
                       'http://pear2.php.net/');
\pear2\Pyrus\Main::$downloadClass = 'Internet';
$chan = \pear2\Pyrus\Config::current()->channelregistry['pear2.php.net'];

$remote = new pear2\Pyrus\Channel\Remotecategories($chan);
$cat = $remote['Default'];
$test->assertEquals('pear2\Pyrus\Channel\Remotecategory', get_class($cat), 'wrong class');
foreach ($remote as $category => $obj) {
    $test->assertEquals('Default', $category, 'category name');
    $test->assertEquals('pear2\Pyrus\Channel\Remotecategory', get_class($obj), 'wrong class');
}
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===