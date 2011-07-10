--TEST--
\Pyrus\Task\Unixeol::startSession()
--FILE--
<?php
include dirname(__DIR__) . '/setup.php.inc';

$taskxml = array('attribs' =>
              array('name' => 'foo.php', 'role' => 'src'),
'tasks:unixeol' => '');

file_put_contents(TESTDIR . '/foo.php', "\r\nhi\r\n\r\nthere");

$iterator = new \Pyrus\Package\Creator\TaskIterator($taskxml, $package, \Pyrus\Task\Common::INSTALL, null);

$fp = fopen(TESTDIR . '/foo.php', 'rb+');
$runcount = 0;
foreach ($iterator as $task) {
    ++$runcount;
    $task->startSession($fp, 'foo.php');
    rewind($fp);
}
$test->assertEquals(1, $runcount, 'Iterator did not run unixeol 1');
$contents = stream_get_contents($fp);
$test->assertTrue("\nhi\n\nthere" === $contents, 'contents differ 1');
if ($contents != "\nhi\n\nthere") {
    var_dump(str_replace(array("\r", "\n"), array('\\r', '\\n'), $contents));
}
fclose($fp);

file_put_contents(TESTDIR . '/foo.php', "\r\nhi\r\n\r\nthere");

$iterator = new \Pyrus\Package\Creator\TaskIterator($taskxml, $package, \Pyrus\Task\Common::PACKAGE, null);

$fp = fopen(TESTDIR . '/foo.php', 'rb+');
$runcount = 0;
foreach ($iterator as $task) {
    ++$runcount;
    $task->startSession($fp, 'foo.php');
    rewind($fp);
}
$test->assertEquals(1, $runcount, 'Iterator did not run unixeol 2');
$test->assertTrue("\nhi\n\nthere" === stream_get_contents($fp), 'contents differ 2');
fclose($fp);
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===