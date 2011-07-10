--TEST--
PackageFile v2: test package.xml release properties (2)
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
require __DIR__ . '/../../Registry/AllRegistries/package/basic/release2.template';

$a = new \Pyrus\PackageFile\v2;
$a->fromArray(array('package' => array('contents' => array('dir' => array('attribs' => array('name' => '/'),
                                                                          'file' => array('attribs' => array('name' => 'test')))),
                                       'phprelease' => array('installcondition' => array('extension' => null)))));
$a->setFilelist(array('test' => array('attribs' => array('name' => 'test'))));
$a->release[0]->ignore('test');
$test->assertEquals(false, $a->release[0]->getInstallCondition(), 'install conditions');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===