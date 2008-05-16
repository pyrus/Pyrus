<?php
require __DIR__ . '/../setup.php.inc';
$testpath = __DIR__ . '/testit';
mkdir($testpath);
class tc extends PEAR2_Pyrus_Config
{
    /**
     * location of PEAR2 installation
     *
     * @var string
     */
    public $pearDir;
    /**
     * location of user-specific configuration file
     *
     * @var string
     */
    public $userFile;

    public function __construct($pearDirectory, $userfile = false)
    {
        return parent::__construct($pearDirectory, $userfile);
    }

}
