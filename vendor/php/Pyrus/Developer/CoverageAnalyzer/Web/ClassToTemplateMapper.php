<?php
namespace Pyrus\Developer\CoverageAnalyzer\Web;
class ClassToTemplateMapper extends \PEAR2\Templates\Savant\ClassToTemplateMapper
{

    function map($class)
    {
        if ($class == 'Pyrus\Developer\CoverageAnalyzer\SourceFile\PerTest') {
            return 'SourceFile.tpl.php';
        }
        $class = str_replace(array('Pyrus\Developer\CoverageAnalyzer\Web', 'Pyrus\Developer\CoverageAnalyzer'), '', $class);
        return parent::map($class);
    }
}