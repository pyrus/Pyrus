--TEST--
Test of object access for PEAR2\HTTP\Request\Headers
--FILE--
<?php
require_once dirname(__FILE__).'/_setup.php';
$in = array(
	'Content-Type'	=> 'text/html',
	'ETag'		=> 'EADAF124D',
	'content-length'=> '10'
	);
$headers = new PEAR2\HTTP\Request\Headers($in);

var_dump($headers->ContentType);
var_dump($headers->ETag);
var_dump($headers->Etag);
var_dump($headers->ContentLength);
var_dump($headers->blah);

var_dump(isset($headers->ContentType));
var_dump(isset($headers->blah));

unset($headers->ContentType);
var_dump(isset($headers->ContentType));
?>
--EXPECT--
string(9) "text/html"
string(9) "EADAF124D"
NULL
string(2) "10"
NULL
bool(true)
bool(false)
bool(false)
