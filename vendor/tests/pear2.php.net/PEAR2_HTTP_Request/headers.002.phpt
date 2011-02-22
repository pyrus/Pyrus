--TEST--
Test of arrau access for PEAR2\HTTP\Request\Headers
--FILE--
<?php
require_once dirname(__FILE__).'/_setup.php';
$in = array(
	'Content-Type'	=> 'text/html',
	'ETag'		=> 'EADAF124D',
	'content-length'=> '10'
	);
$headers = new PEAR2\HTTP\Request\Headers($in);

var_dump($headers['content-type']);
var_dump($headers['Content-Type']);
var_dump($headers['content-Type']);
var_dump($headers['content-length']);
var_dump($headers['ETag']);
var_dump($headers['etag']);
var_dump($headers['blah']);

var_dump(isset($headers['content-type']));
var_dump(isset($headers['content-type']));

unset($headers['content-type']);
var_dump(isset($headers['content-type']));
?>
--EXPECT--
string(9) "text/html"
string(9) "text/html"
string(9) "text/html"
string(2) "10"
string(9) "EADAF124D"
string(9) "EADAF124D"
NULL
bool(true)
bool(true)
bool(false)
