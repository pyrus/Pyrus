<?php
//require_once 'PEAR2/HTTP/Request/allfiles.php';

// to run from svn checkout
require_once '../../autoload.php';

$url = 'http://webthumb.bluga.net/home';

$adapters = array(
	'Phpstream' => true,
	'Phpsocket' => false,
	'Http' => false,
	);

foreach($adapters as $adapter => $status) {
	if (!$status) {
		continue;
	}

	$class = 'PEAR2\HTTP\Request\Adapter\\'.$adapter;
	$request = new PEAR2\HTTP\Request($url,new $class);
	$response = $request->sendRequest();

	echo "$adapter adapter\n";
	var_dump($response->code);
	var_dump($response->headers);
	var_dump($response->cookies);
	var_dump(strlen($response->body));
}
