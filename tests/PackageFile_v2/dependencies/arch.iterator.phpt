--TEST--
PackageFile v2: test package.xml dependencies property, arch iterator
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$pf = new \Pyrus\PackageFile\v2;
$pf->dependencies['required']->arch['hi'] = true;
$pf->dependencies['required']->arch['bye'] = false;
$pf->dependencies['required']->arch['last'] = true;
$comp = array();
foreach ($pf->dependencies['required']->arch as $key => $dep) {
    $comp[$key] = $dep->getInfo();
}
$test->assertEquals(array (
  0 => 
  array (
    'pattern' => 'hi',
  ),
  1 => 
  array (
    'pattern' => 'bye',
    'conflicts' => '',
  ),
  2 => 
  array (
    'pattern' => 'last',
  ),
), $comp, 'test');
?>
===DONE===
--EXPECT--
===DONE===