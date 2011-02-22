<?php
namespace PEAR2\Pyrus\Developer\CoverageAnalyzer\Web;
class ClassToTemplateMapper extends \PEAR2\Templates\Savant\ClassToTemplateMapper
{

    function map($class)
    {
        if ($class == 'PEAR2\Pyrus\Developer\CoverageAnalyzer\SourceFile\PerTest') {
            return 'SourceFile.tpl.php';
        }
        $class = str_replace(array('PEAR2\Pyrus\Developer\CoverageAnalyzer\Web', 'PEAR2\Pyrus\Developer\CoverageAnalyzer'), '', $class);
        return parent::map($class);
    }
}