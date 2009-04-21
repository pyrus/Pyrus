--TEST--
PEAR2_Pyrus_ChannelFile_Parser_v1 errors
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
try {
    $parser->parse('', false, 'stdClass');
    throw new Exception('stdClass worked and should not');
} catch (PEAR2_Pyrus_ChannelFile_Exception $e) {
    $test->assertEquals('Class stdClass' .
                ' passed to parse() must be a child class of PEAR2_Pyrus_ChannelFile_v1',
                $e->getMessage(), 'stdClass');
}

try {
    $parser->parse('<channel');
    throw new Exception('invalid xml worked and should not');
} catch (PEAR2_Pyrus_ChannelFile_Exception $e) {
    $test->assertEquals('Invalid channel.xml',
                $e->getMessage(), 'invalid xml');
}
?>
===DONE===
--EXPECT--
===DONE===