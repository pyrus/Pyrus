--TEST--
\PEAR2\Pyrus\Channel\RemoteCategory: no releases for a package
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/noreleases',
                       'http://pear2.php.net/');
\PEAR2\Pyrus\Main::$downloadClass = 'Internet';
$chan = \PEAR2\Pyrus\Config::current()->channelregistry['pear2.php.net'];

$category = $chan->remotecategories['Default'];
$test->assertEquals(array(array('package' => 'GetMaintainers_Test',
                                'latest' => array('v' => 'n/a', 's' => 'n/a', 'm' => 'n/a'),
                                'stable' => 'n/a')), $category->basiclist, 'empty basic list');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===