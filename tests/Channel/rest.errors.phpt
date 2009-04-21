--TEST--
PEAR2_Pyrus_ChannelFile_v1_REST errors
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$channel = new PEAR2_Pyrus_ChannelFile_v1;
$getassert = function($message) use ($channel, $test) {
    return function($action, $type) use ($channel, $test, $message) {
        try {
            $action();
            throw new Exception($type . ' worked and should not');
        } catch (PEAR2_Pyrus_ChannelFile_Exception $e) {
            $test->assertEquals($message, $e->getMessage(), $type);
        }    
    };
};

$assert = $getassert('Cannot use [] to access '
                    . 'baseurl, use ->');

$assert(function() use ($channel) {$a = $channel->protocols->rest['REST1.0']['oops'];},
        'offsetGet');

$assert(function() use ($channel) {$channel->protocols->rest['REST1.0']['oops'] = 1;},
        'offsetSet');

$assert(function() use ($channel) {isset($channel->protocols->rest['REST1.0']['oops']);},
        'offsetExists');

$assert(function() use ($channel) {unset($channel->protocols->rest['REST1.0']['oops']);},
        'offsetUnset');

$assert = $getassert('Cannot use -> to access '
                    . 'REST protocols, use []');

$assert(function() use ($channel) {$a = $channel->protocols->rest->oops;},
        '__get');

$assert(function() use ($channel) {$channel->protocols->rest->oops = 1;},
        '__set');

$assert = $getassert('Unknown variable oops');

$assert(function() use ($channel) {$a = $channel->protocols->rest['REST1.0']->oops;},
        '__get oops');

$assert(function() use ($channel) {$channel->protocols->rest['REST1.0']->oops = 1;},
        '__set oops');

$assert = $getassert('Can only set REST protocol ' .
                        ' to a PEAR2_Pyrus_ChannelFile_v1_Servers_Protocol_REST object');

$assert(function() use ($channel) {$channel->protocols->rest['REST1.0'] = 1;},
        'offsetSet non-self');

?>
===DONE===
--EXPECT--
===DONE===