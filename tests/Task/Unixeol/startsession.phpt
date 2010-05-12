--TEST--
\PEAR2\Pyrus\Task\Unixeol::startSession()
--FILE--
<?php
define('MYDIR', __DIR__);
include dirname(__DIR__) . '/setup.php.inc';

$taskxml = array('attribs' =>
              array('name' => 'foo.php', 'role' => 'src'),
'tasks:unixeol' => '');

file_put_contents(__DIR__ . '/testit/foo.php', "\r\nhi\r\n\r\nthere");

$iterator = new \PEAR2\Pyrus\Package\Creator\TaskIterator($taskxml, $package, \PEAR2\Pyrus\Task\Common::INSTALL, null);

$fp = fopen(__DIR__ . '/testit/foo.php', 'rb+');
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

file_put_contents(__DIR__ . '/testit/foo.php', "\r\nhi\r\n\r\nthere");

$iterator = new \PEAR2\Pyrus\Package\Creator\TaskIterator($taskxml, $package, \PEAR2\Pyrus\Task\Common::PACKAGE, null);

$fp = fopen(__DIR__ . '/testit/foo.php', 'rb+');
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
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===