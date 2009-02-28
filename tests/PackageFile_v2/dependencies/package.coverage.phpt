--TEST--
PackageFile v2: test package.xml dependencies property, Package class extra coverage
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$pf = new PEAR2_Pyrus_PackageFile_v2;
$pf->fromArray(array('package' => array('dependencies' => array('required' => array('package' =>
                                        array('name' => 'try', 'channel' => 'first', 'exclude' => '1.2.0'))))));
$test->assertEquals(array('1.2.0'), $pf->dependencies['required']->package['first/try']->exclude, 'exclude');
// covers unset() on non-existing package
unset($pf->dependencies['required']->package['Hi/There']);
$test->assertEquals(false, isset($pf->dependencies['required']->package['Hi/There']), 'verify unset did not add it');
$test->assertEquals('required', $pf->dependencies['required']->package['Hi/There']->deptype, 'deptype');
$pf->dependencies['required']->package['Hi/There']->conflicts(true);
$test->assertEquals(true, $pf->dependencies['required']->package['Hi/There']->conflicts, 'conflicts');
$pf->dependencies['required']->package['first/try']->exclude(null);
$test->assertEquals(null, $pf->dependencies['required']->package['first/try']->exclude, 'exclude null');
?>
===DONE===
--EXPECT--
===DONE===