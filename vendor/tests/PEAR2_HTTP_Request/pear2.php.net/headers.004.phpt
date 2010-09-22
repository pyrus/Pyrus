--TEST--
Test of iterator in PEAR2\HTTP\Request\Headers
--FILE--
<?php
require_once dirname(__FILE__).'/_setup.php';
$in = array(
	'Content-Type'	=> 'text/html',
	'ETag'		=> 'EADAF124D',
	'content-length'=> '10'
	);
$headers = new PEAR2\HTTP\Request\Headers($in);

foreach($headers as $k => $v) {
	echo "$k: $v\n";
}

$headers->iterationStyle = PEAR2\HTTP\Request\Headers::CAMEL_CASE;
foreach($headers as $k => $v) {
	echo "$k: $v\n";
}

$headers->iterationStyle = PEAR2\HTTP\Request\Headers::ORIGINAL_CASE;
foreach($headers as $k => $v) {
	echo "$k: $v\n";
}

?>
--EXPECT--
content-type: text/html
etag: EADAF124D
content-length: 10
ContentType: text/html
ETag: EADAF124D
ContentLength: 10
Content-Type: text/html
ETag: EADAF124D
content-length: 10
