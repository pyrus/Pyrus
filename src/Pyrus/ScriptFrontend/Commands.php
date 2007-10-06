<?php
class PEAR2_Pyrus_ScriptFrontend_Commands
{
    public $commands = array();

    function __construct()
    {
        $a = new ReflectionClass($this);
        foreach ($a->getMethods() as $method) {
            if ($method[0] == '_') {
                continue;
            }
            $method = new ReflectionMethod();
            $this->commands[$method->getName()] = true;
        }
    }

    function _process($args)
    {
        if (!count($args)) {
            $args[0] = 'help';
        }
        if (isset($this->commands[$args[0]])) {
            array_shift($args);
            $this->{$args[0]}($args);
        } else {
            $this->help($args);
        }
    }

    function install($args)
    {
        PEAR2_Pyrus_Installer::begin();
        try {
            $packages = array();
            foreach ($args as $arg) {
                PEAR2_Pyrus_Installer::prepare($packages[] = new PEAR2_Pyrus_Package($arg));
            }
            PEAR2_Pyrus_Installer::commit();
            foreach ($packages as $package) {
                echo 'Installed ' . $package->channel . '\\' . $package->name . '-' . $package->version['release'] . "\n";
            }
        } catch (Exception $e) {
            die($e);
        }
    }

    function upgrade($args)
    {
        PEAR2_Pyrus_Installer::$options['upgrade'] = true;
        $this->install($args);
    }

    function listPackages()
    {
        foreach (PEAR2_Pyrus_Config::current()->registry as $package) {
            echo $package->channel . '/' . $package->name . "\n";
        }
    }
}