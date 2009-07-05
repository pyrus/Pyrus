--TEST--
PackageFile v2: test package.xml dependencies property, os iterator
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$pf = new \pear2\Pyrus\PackageFile\v2;
$pf->dependencies['required']->os['hi'] = true;
$pf->dependencies['required']->os['bye'] = false;
$pf->dependencies['required']->os['last'] = true;
$comp = array();
foreach ($pf->dependencies['required']->os as $key => $dep) {
    $comp[$key] = $dep->getInfo();
}
$test->assertEquals(array (
  0 => 
  array (
    'name' => 'hi',
  ),
  1 => 
  array (
    'name' => 'bye',
    'conflicts' => '',
  ),
  2 => 
  array (
    'name' => 'last',
  ),
), $comp, 'test');
?>
===DONE===
--EXPECT--
===DONE===