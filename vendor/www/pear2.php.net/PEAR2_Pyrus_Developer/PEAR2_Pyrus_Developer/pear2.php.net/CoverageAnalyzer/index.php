<?php
require_once dirname(__DIR__).'/../../autoload.php';

session_start();
$view = new PEAR2\Pyrus\Developer\CoverageAnalyzer\Web\View;
$rooturl = parse_url($_SERVER['REQUEST_URI']);
$rooturl = $rooturl['path'];
$controller = new PEAR2\Pyrus\Developer\CoverageAnalyzer\Web\Controller($view, $rooturl);
$controller->route();
