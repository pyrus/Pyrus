<?php
require_once dirname(__DIR__).'/../../autoload.php';
ini_set('display_errors',true);
session_start();

$view = new PEAR2\Pyrus\Developer\CoverageAnalyzer\Web\View;
$rooturl = parse_url($_SERVER['REQUEST_URI']);
$rooturl = $rooturl['path'];

$controller = new PEAR2\Pyrus\Developer\CoverageAnalyzer\Web\Controller($_GET);
$controller::$rooturl = $rooturl;

$savant = new PEAR2\Templates\Savant\Main();
$savant->setClassToTemplateMapper(new PEAR2\Pyrus\Developer\CoverageAnalyzer\Web\ClassToTemplateMapper);
$savant->setTemplatePath(__DIR__.'/templates');
$savant->setEscape('htmlentities');
echo $savant->render($controller);
