<?php
$testServer = false; // set to the url where testFiles has been copied
$testServer = "http://localhost/workspace/PEAR2_HTTP_Request/tests/testFiles/";

// for tests were including all the HTTP_Request files
// this is a hack to make tests run from svn for now

function autoload($class)
{
    $file = str_replace(
                array('PEAR2\\', '\\', '_'),
                array('', DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR),
                $class
            );
    $file .= '.php';
    include __DIR__ . '/../src/' . $file;
}

spl_autoload_register('autoload');
