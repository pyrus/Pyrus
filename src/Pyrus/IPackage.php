<?php
interface PEAR2_Pyrus_IPackage extends ArrayAccess, Iterator
{
    function getFileContents($file, $asstream = false);
    function getLocation();
    function __get($var);
    function __toString();
    function __call($func, $args);
}