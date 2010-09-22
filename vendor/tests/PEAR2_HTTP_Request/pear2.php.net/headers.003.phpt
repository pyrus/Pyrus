--TEST--
Test of count functionality in PEAR2\HTTP\Request\Headers
--FILE--
<?php
require_once dirname(__FILE__).'/_setup.php';
$in = array(
	'Content-Type'	=> 'text/html',
	'ETag'		=> 'EADAF124D',
	'content-length'=> '10'
	);
$headers = new PEAR2\HTTP\Request\Headers($in);

var_dump(count($headers));
?>
--EXPECT--
int(3)
