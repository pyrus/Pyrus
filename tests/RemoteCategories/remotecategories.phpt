--TEST--
\Pyrus\Channel\RemoteCategories: basic test
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../Mocks/Internet/remotepackage',
                       'http://pear2.php.net/');
\Pyrus\Main::$downloadClass = 'Internet';
$chan = \Pyrus\Config::current()->channelregistry['pear2.php.net'];

$remote = new Pyrus\Channel\RemoteCategories($chan);
$cat = $remote['Default'];
$test->assertEquals('Pyrus\Channel\RemoteCategory', get_class($cat), 'wrong class');
foreach ($remote as $category => $obj) {
    $test->assertEquals('Default', $category, 'category name');
    $test->assertEquals('Pyrus\Channel\RemoteCategory', get_class($obj), 'wrong class');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===
