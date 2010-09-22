--TEST--
PEAR2\HTTP\Request\Adapter\Phpsocket - Test of an HTTP Get request
uses test001.html
--FILE--
<?php
require_once dirname(__FILE__).'/../_setup.php';

$adapter = new PEAR2\HTTP\Request\Adapter\Phpsocket();

// this is a shared test with only the adapter being differ
require_once dirname(__FILE__).'/../shared/get.001.php';
--EXPECT--
string(5) "Test
"
bool(true)
