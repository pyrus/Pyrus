--TEST--
\PEAR2\Pyrus\Channel\RemoteCategory: completely empty category (with invalid REST, no <pi/> tag)
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/empty',
                       'http://pear2.php.net/');
\PEAR2\Pyrus\Main::$downloadClass = 'Internet';
$chan = \PEAR2\Pyrus\Config::current()->channelregistry['pear2.php.net'];

$category = $chan->remotecategories['Default'];
$test->assertEquals(array(), $category->basiclist, 'empty basic list');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===