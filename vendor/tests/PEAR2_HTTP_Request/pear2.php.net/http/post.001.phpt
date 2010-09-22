--TEST--
PEAR2\HTTP\Request\Adapter\Http - Test of an HTTP Post request 
uses test001.html
--SKIPIF--
<?php
if (!extension_loaded('http')) {
    die('SKIP Http extension must be enabled');
}
?>
--FILE--
<?php
require_once dirname(__FILE__).'/../_setup.php';

$adapter = new PEAR2\HTTP\Request\Adapter\Http();

// this is a shared test with only the adapter being differ
require_once dirname(__FILE__).'/../shared/post.001.php';
--EXPECT--
string(5) "Test
"
bool(true)
