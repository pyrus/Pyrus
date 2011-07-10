<?php
/**
 * Basic requirement for implementing a packagefile
 */
namespace Pyrus;
interface PackageFileInterface
{
    function __get($var);
    function __set($var, $value);
    function __toString();
    function toArray($forpackaging = false);
    function getValidator();
}
