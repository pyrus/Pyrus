--TEST--
Get a file larger than memory limit
--INI--
memory_limit=1m
--FILE--
<?php
require_once dirname(__FILE__)."/_setup.php";


$adapter = new PEAR2\HTTP\Request\Adapter\Curl(); // curl extension
$request = new PEAR2\HTTP\Request($testServer."2meg.bin",$adapter);

$temp = tempnam('/tmp','phpt');

$request->requestToFile($temp);

echo md5_file($temp)."\n";

unlink($temp);

?>
--EXPECT--
b2d1236c286a3c0704224fe4105eca49
