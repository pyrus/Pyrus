--TEST--
PackageFile v2: test package.xml release properties (2)
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/testit');
$c->bin_dir = __DIR__ . '/testit/bin';
restore_include_path();
$c->saveConfig();
require __DIR__ . '/../setupFiles/setupPackageFile.php.inc';
$reg = $package; // simulate registry package using packagefile
require __DIR__ . '/../../Registry/AllRegistries/info/release2.template';

$a = new PEAR2_Pyrus_PackageFile_v2;
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
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===