<?php
/**
 * Basic requirement for implementing a packagefile
 */
interface PEAR2_Pyrus_IPackageFile
{
    function __get($var);
    function __set($var, $value);
    function __toString();
    function toArray($forpackaging = false);
}
