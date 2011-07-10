--TEST--
\Pyrus\Config::configSnapshot()
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$a = $configclass::singleton($testpath, $testpath . '/blah');
$oldext = $a->ext_dir;

$snap = new Pyrus\Config\Snapshot('2009-01-02 12:00:00', $a);
$test->assertEquals($oldext, $snap->ext_dir, 'snap pre-anything');
$a->ext_dir = 'in between';

$d = DIRECTORY_SEPARATOR;
$test->assertEquals('configsnapshot-' . ($date = date('Y-m-d H-i-s')) . '.xml', $a->configSnapshot(), 1);
rename($cdir . '/configsnapshot-' . $date . '.xml',
       $cdir . '/configsnapshot-2009-01-01 12-34-56.xml');
$a->ext_dir = 'new';
$test->assertEquals('configsnapshot-' . $date . '.xml', $a->configSnapshot(), 2);

$snap = new Pyrus\Config\Snapshot($date, $a);
$test->assertEquals('new', $snap->ext_dir, 'snap new');

$snap = new Pyrus\Config\Snapshot('2009-01-02 12-00-00', $a);
$test->assertEquals('in between', $snap->ext_dir, 'snap in between');

$snap = new Pyrus\Config\Snapshot('2008-12-13 12:00:00', $a);
$test->assertEquals($oldext, $snap->ext_dir, 'snap old');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===
