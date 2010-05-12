--TEST--
\PEAR2\Pyrus\Task\Replace::validateXml() failures
--FILE--
<?php
include dirname(__DIR__) . '/setup.minimal.php.inc';
$xmltest = function($xml, $filexml, $message, $exception) use ($package, $test)
{
    try {
        \PEAR2\Pyrus\Task\Replace::validateXml($package, $xml, $filexml, 'filename');
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

$xmltest(array(), array(), 'task <replace> has no attributes in file filename', '\PEAR2\Pyrus\Task\Exception\NoAttributes');

$errs = $xmltest(array('attribs' => array()), array(),
         'Invalid replace task, multiple missing attributes', '\PEAR2\Pyrus\Task\Exception');
$test->assertEquals(3, count($errs->getPrevious()), 'right number of missing attributes');
$causetest('task <replace> is missing attribute "type" in file filename', 'E_ERROR',
           '\PEAR2\Pyrus\Task\Exception\MissingAttribute', 0, $errs);
$causetest('task <replace> is missing attribute "to" in file filename', 'E_ERROR',
           '\PEAR2\Pyrus\Task\Exception\MissingAttribute', 1, $errs);
$causetest('task <replace> is missing attribute "from" in file filename', 'E_ERROR',
           '\PEAR2\Pyrus\Task\Exception\MissingAttribute', 2, $errs);

$errs = $xmltest(array('attribs' => array('type' => 'package-info')), array(),
         'Invalid replace task, multiple missing attributes', '\PEAR2\Pyrus\Task\Exception');
$test->assertEquals(2, count($errs->getPrevious()), 'right number of missing attributes');
$causetest('task <replace> is missing attribute "to" in file filename', 'E_ERROR',
           '\PEAR2\Pyrus\Task\Exception\MissingAttribute', 0, $errs);
$causetest('task <replace> is missing attribute "from" in file filename', 'E_ERROR',
           '\PEAR2\Pyrus\Task\Exception\MissingAttribute', 1, $errs);

$errs = $xmltest(array('attribs' => array('to' => 'package-info')), array(),
         'Invalid replace task, multiple missing attributes', '\PEAR2\Pyrus\Task\Exception');
$test->assertEquals(2, count($errs->getPrevious()), 'right number of missing attributes');
$causetest('task <replace> is missing attribute "type" in file filename', 'E_ERROR',
           '\PEAR2\Pyrus\Task\Exception\MissingAttribute', 0, $errs);
$causetest('task <replace> is missing attribute "from" in file filename', 'E_ERROR',
           '\PEAR2\Pyrus\Task\Exception\MissingAttribute', 1, $errs);

$xmltest(array('attribs' => array('type' => 'package-info', 'to' => 'package-info')), array(),
         'task <replace> is missing attribute "from" in file filename', '\PEAR2\Pyrus\Task\Exception\MissingAttribute');

$xmltest(array('attribs' => array('type' => 'pear-config', 'from' => 'poop', 'to' => 'package-info')), array(),
         'task <replace> attribute "to" has the wrong value "package-info" in file filename, expecting one of "php_dir, ext_dir, cfg_dir, doc_dir, bin_dir, data_dir, www_dir, test_dir, src_dir, php_bin, php_ini, php_prefix, php_suffix"', '\PEAR2\Pyrus\Task\Exception\WrongAttributeValue');

$xmltest(array('attribs' => array('type' => 'php-const', 'from' => 'poop', 'to' => 'package-info')), array(),
         'task <replace> attribute "to" has the wrong value "package-info" in file filename, expecting one of "valid PHP constant"', '\PEAR2\Pyrus\Task\Exception\WrongAttributeValue');

$xmltest(array('attribs' => array('type' => 'package-info', 'from' => 'poop', 'to' => 'package-info')), array(),
         'task <replace> attribute "to" has the wrong value "package-info" in file filename, expecting one of "name, summary, channel, notes, extends, description, release_notes, license, release-license, license-uri, version, api-version, state, api-state, release_date, date, time"', '\PEAR2\Pyrus\Task\Exception\WrongAttributeValue');

$xmltest(array('attribs' => array('type' => 'blurp', 'from' => 'poop', 'to' => 'package-info')), array(),
         'task <replace> attribute "type" has the wrong value "blurp" in file filename, expecting one of "pear-config, package-info, php-const"', '\PEAR2\Pyrus\Task\Exception\WrongAttributeValue');
?>
===DONE===
--EXPECT--
===DONE===