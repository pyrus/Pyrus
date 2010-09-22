<?php
$testServer = false; // set to the url where testFiles has been copied
$testServer = "http://ucommbieber.unl.edu/workspace/PEAR2/HTTP_Request/tests/testFiles/";

// for tests were including all the HTTP_Request files
// this is a hack to make tests run from svn for now

$autoload = dirname(__FILE__).'/../../autoload.php';
if (file_exists($autoload)) {
	require_once $autoload;
}
