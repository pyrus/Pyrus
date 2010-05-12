--TEST--
test Registry __construct error
--FILE--
<?php
require dirname(__FILE__) . '/../../setup.php.inc';

try {
    $a = new $registryclass(__DIR__, array('b#b#b#'));
    throw new Exception('did not fail');
} catch (\PEAR2\Pyrus\Registry\Exception $e) {
    $test->assertEquals('Unable to initialize registry for path "' . __DIR__ . '"', $e->getMessage(), 'message');
    $test->assertEquals(1, count($e->getPrevious()), 'cause');
}

?>
===DONE===
--EXPECT--
===DONE===