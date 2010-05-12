--TEST--
ScriptFileFilterIterator: verify it gets only script files
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
$postinstall = $package->files['foobar']->postinstallscript->add();

$postinstall->paramgroup['first']->param['paramname']->type('string')->prompt('paramname');
$postinstall->paramgroup['first']->param['paramname2']->type('string')->prompt('paramname2');
$postinstall->paramgroup['first']->instructions = "testing\nthis thing";

$postinstall->paramgroup['second']->save();

$postinstall->paramgroup['third']->param['paramname']->type('string')->prompt('paramname');
$postinstall->paramgroup['third']->param['paramname2']->type('string')->prompt('paramname');

$postinstall->paramgroup['fourth']->param['paramname']->type('string')->prompt('paramname');
$postinstall->paramgroup['fourth']->param['paramname2']->type('string')->prompt('paramname');

$files = array();
foreach (new \PEAR2\Pyrus\PackageFile\v2Iterator\ScriptFileFilterIterator($package->getFilelist(), $package)
         as $file) {
    $files[] = $file;
}

$test->assertEquals(1, count($files), 'correct count');
$test->assertEquals('foobar', $files[0]['attribs']['name'], 'correct script file');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===