<?php
//require_once 'PEAR2/HTTP/Request/allfiles.php';

// to run from svn checkout
require_once '../../autoload.php';

$request = new PEAR2\HTTP\Request('http://pear.php.net/');
$request->verb = 'HEAD';
$response = $request->sendRequest();

var_dump($response->code);
var_dump($response->headers);
var_dump(strlen($response->body));
