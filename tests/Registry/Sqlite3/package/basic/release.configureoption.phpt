--TEST--
Sqlite3 Registry PackageFile v2: test package.xml release configureoption property
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
require __DIR__ . '/../../../AllRegistries/package/extended/release.configureoption.template';

$info->type = 'extsrc';
$info->installrelease->configureoption['foo']->prompt('prompt 1')->default('yes');
$info->installrelease->configureoption['bar']->prompt('prompt 2');

$reg = new \Pyrus\Registry\Sqlite3(TESTDIR);
$reg->replace($info);
$inf = $reg->package[$info->channel . '/' . $info->name];

$stuff = array();
foreach ($inf->installrelease->configureoption as $key => $option) {
    $test->assertIsa('\Pyrus\PackageFile\v2\Release\ConfigureOption', $option, 'right class');
    $stuff[$key] = $option->getInfo();
}
ksort($stuff);
$test->assertEquals(array (
  'bar' => 
  array (
    'name' => 'bar',
    'prompt' => 'prompt 2',
    'default' => NULL,
  ),
  'foo' => 
  array (
    'name' => 'foo',
    'default' => 'yes',
    'prompt' => 'prompt 1',
  ),
), $stuff, 'info stuff');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../../clean.php.inc';
?>
--EXPECT--
===DONE===