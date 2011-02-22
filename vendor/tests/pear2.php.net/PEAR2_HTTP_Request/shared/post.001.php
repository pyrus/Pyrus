<?php
$url = $testServer.'test002.php';

$http = new PEAR2\HTTP\Request($url,$adapter);
$http->verb = 'POST';
$http->body = "Test\n";

$response = $http->sendRequest();
// make sure we got the correct body back
var_dump($response->body);

// check that the content-length header is correct
if (isset($response->headers['Content-Length'])) {
	var_dump($response->headers['Content-Length'] == 5);
}

/* Expects Should Be: 
string(5) "Test
"
bool(true)
*/
