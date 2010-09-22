<?php

if (!defined('PEAR2_HTTP_Request_PATH')) {
    define('PEAR2_HTTP_Request_PATH', dirname(__FILE__));
}

require PEAR2_HTTP_Request_PATH . '/../Request.php';
require PEAR2_HTTP_Request_PATH . '/Exception.php';
require PEAR2_HTTP_Request_PATH . '/Uri.php';
require PEAR2_HTTP_Request_PATH . '/Headers.php';
require PEAR2_HTTP_Request_PATH . '/Response.php';
require PEAR2_HTTP_Request_PATH . '/Adapter.php';
require PEAR2_HTTP_Request_PATH . '/Adapter/Curl.php';
require PEAR2_HTTP_Request_PATH . '/Adapter/Filesystem.php';
require PEAR2_HTTP_Request_PATH . '/Adapter/Http.php';
require PEAR2_HTTP_Request_PATH . '/Adapter/Phpsocket.php';
require PEAR2_HTTP_Request_PATH . '/Adapter/Phpstream.php';
