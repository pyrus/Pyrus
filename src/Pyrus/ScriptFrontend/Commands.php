<?php
class PEAR2_Pyrus_ScriptFrontend_Commands
{
    public $commands = array();

    function __construct()
    {
        $a = new ReflectionClass($this);
        foreach ($a->getMethods() as $method) {
            $name = $method->getName();
            if ($name[0] == '_' || $name === 'run') {
                continue;
            }
            $this->commands[$name] = true;
        }
    }

    function run($args)
    {
        try {
            if (!count($args)) {
                $args[0] = 'help';
            }
            $this->_findPEAR($args);
            if (isset($this->commands[$args[0]])) {
                $command = array_shift($args);
                $this->$command($args);
            } else {
                $this->help($args);
            }
        } catch (Exception $e) {
            echo "Operation failed:\n$e";
        }
    }

    function _findPEAR(&$arr)
    {
        if (isset($arr[0]) && @file_exists($arr[0]) && @is_dir($arr[0])) {
            $maybe = array_shift($arr);
            $maybe = realpath($maybe);
            echo "Using PEAR installation found at $maybe\n";
            $config = new PEAR2_Pyrus_Config($maybe);
            return;
        }
        $mypath = PEAR2_Pyrus_Config::current()->my_pear_path;
        if ($mypath) {
            foreach (explode(PATH_SEPARATOR, $mypath) as $path) {
                echo "Using PEAR installation found at $path\n";
                $config = new PEAR2_Pyrus_Config($path);
                return;
            }
        }
        $include_path = explode(PATH_SEPARATOR, get_include_path());
        foreach ($include_path as $path) {
            if ($path == '.') continue;
            echo "Using PEAR installation found at $path\n";
            $config = new PEAR2_Pyrus_Config($path);
            return;
        }
        echo "Using PEAR installation in current directory\n";
    }

    function help($args)
    {
        if ($args[0] == 'help') {
            echo "Commands supported:\n";
            foreach ($this->commands as $command => $true) {
                echo "$command\n";
            }
        } else {
            if (isset($args[0])) {
                echo "Unknown command: $args[0]\n";
            }
            echo "Commands supported:\n";
            foreach ($this->commands as $command => $true) {
                echo "$command [PEARPath]\n";
            }
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

    function listPackages($args)
    {
        echo "Listing packages in channel " . PEAR2_Pyrus_Config::current()->default_channel .
            ":\n";
        foreach (PEAR2_Pyrus_Config::current()->registry as $package) {
            echo $package->channel . '/' . $package->name . "\n";
        }
    }

    function listChannels($args)
    {
        echo "Listing channels:\n";
        foreach (PEAR2_Pyrus_Config::current()->channelregistry as $channel) {
            echo $channel->getName() . ' (' . $channel->getAlias() . ")\n";
        }
    }

    function configs($args)
    {
        $conf = PEAR2_Pyrus_Config::current();
        echo "System paths:\n";
        foreach ($conf->systemvars as $var) {
            echo "  $var => " . $conf->$var . "\n";
        }
        echo "Custom variables (from " . $conf->userfile . "):\n";
        foreach ($conf->uservars as $var) {
            echo "  $var => " . $conf->$var . "\n";
        }
    }

    function set($args)
    {
        $conf = PEAR2_Pyrus_Config::current();
        if (in_array($args[0], $conf->uservars)) {
            echo "Setting $args[0] in " . $conf->userfile . "\n";
            $conf->{$args[0]} = $args[1];
        } else {
            echo "Setting $args[0] in system paths\n";
            $conf->{$args[0]} = $args[1];
        }
        $conf->saveConfig();
    }

    function mypear($args)
    {
        echo "Setting my pear repositories to:\n";
        echo implode("\n", $args) . "\n";
        $args = implode(PATH_SEPARATOR, $args);
        PEAR2_Pyrus_Config::current()->my_pear_path = $args;
        PEAR2_Pyrus_Config::current()->saveConfig();
    }
}