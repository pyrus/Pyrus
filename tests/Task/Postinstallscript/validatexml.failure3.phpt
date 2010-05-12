--TEST--
\PEAR2\Pyrus\Task\Postinstallscript::validateXml() failures 3
--FILE--
<?php
define('MYDIR', __DIR__);
include dirname(__DIR__) . '/setup.php.inc';
$xmltest = function($xml, $filexml, $message, $exception) use ($package, $test)
{
    try {
        \PEAR2\Pyrus\Task\Postinstallscript::validateXml($package, $xml, $filexml, 'filename');
        throw new Exception('should have failed');
    } catch (Exception $e) {
        $test->assertIsa($exception, $e, 'wrong exception class ' . $message);
        $test->assertEquals($message, $e->getMessage(), 'wrong message');
        return $e;
    }
};
$causetest = function($message, $severity, $exception, $index, $errs) use ($test)
{
    $errs = $errs->getPrevious();
    $test->assertIsa($exception, $errs->{$severity}[$index], 'right class');
    $test->assertEquals($message, $errs->{$severity}[$index]->getMessage(), 'right message');
};

file_put_contents(__DIR__ . '/testit/glooby', '<?php
class oops {
class oops2 {
interface::$hla;
    }
}
');

$xmltest(array(), array('role' => 'php', 'name' => 'glooby'), 'task <postinstallscript> in file filename is invalid because of "Analysis of post-install script "glooby" failed: Parser error: invalid PHP found in file"', '\PEAR2\Pyrus\Task\Exception\Invalidtask');

?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===