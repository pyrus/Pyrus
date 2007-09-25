<?php
interface PEAR2_Pyrus_IPackage
{
    function getFileContents($file, $asstream = false);
}