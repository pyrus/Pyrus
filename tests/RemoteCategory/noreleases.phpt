--TEST--
\pear2\Pyrus\Channel\Remotecategory: no releases for a package
--FILE--
<?php

define('MYDIR', __DIR__);
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/noreleases',
                       'http://pear2.php.net/');
\pear2\Pyrus\Main::$downloadClass = 'Internet';
$chan = \pear2\Pyrus\Config::current()->channelregistry['pear2.php.net'];

$category = $chan->remotecategories['Default'];
$test->assertEquals(array(array('package' => 'GetMaintainers_Test',
                                'latest' => array('v' => 'n/a', 's' => 'n/a', 'm' => 'n/a'),
                                'stable' => 'n/a')), $category->basiclist, 'empty basic list');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===