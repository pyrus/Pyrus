<?php

class PEAR2_SimpleChannelServer_CLI
{
    /**
     * Channel object
     *
     * @var \pear2\Pyrus\Channel
     */
    protected $channel;
    
    /**
     * Directory to the channel we're managing.
     *
     * @var string
     */
    public $dir;
    
    public $pyruspath = false;
    
    public function __construct()
    {
        $channel_file = getcwd() . '/channel.xml';
        if (file_exists($channel_file)) {
            $this->channel = new \pear2\Pyrus\Channel(new \pear2\Pyrus\ChannelFile($channel_file));
        }
        $this->dir = getcwd();
    }
    
    public function process()
    {
        if ($_SERVER['argc'] < 2) {
            $this->printUsage();
            return false;
        }
        switch ($_SERVER['argv'][1]) {
            case 'update':
                $this->handleUpdate();
                break;
            case 'create':
                return $this->handleCreate();
            case 'add-maintainer':
                $scs = new PEAR2_SimpleChannelServer($this->channel,
                    $this->dir);
                    // is this even needed?
                break;
            case 'add-category':
                PEAR2_SimpleChannelServer_Categories::create($category, $description);
                break;
            case 'release':
                $this->handleRelease();
                break;
            default:
                echo 'Please use one of the following commands:'.PHP_EOL;
                $this->printUsage();
                break;
        }
    }
    
    public function handleUpdate()
    {
        if (!isset($_SERVER['argv'][2])) {
            $this->printUpdateUsage();
            return;
        }
        if (!isset($this->channel)) {
            $this->printUpdateUsage();
            return;
        }
        $scs = new PEAR2_SimpleChannelServer($this->channel, $this->dir);
        $dirname = $this->dir . '/get/';
        $dir = new DirectoryIterator($dirname);
        foreach ($dir as $file) {
            if (!$file->isDot()
                && !$file->isDir()
                && substr($file->getFilename(), -3) != 'tar'
                && substr($file->getFilename(), 0, 1) != '.') {
                $scs->saveRelease(new \pear2\Pyrus\Package($dirname.$file->getFilename()), $_SERVER['argv'][2]);
            }
        }
    }
    
    public function handleRelease()
    {
        if (!isset($_SERVER['argv'][3])) {
            $this->printReleaseUsage();
            return;
        }
        $scs = new PEAR2_SimpleChannelServer($this->channel, $this->dir, $this->pyruspath);
        $scs->saveRelease(new \pear2\Pyrus\Package($_SERVER['argv'][2]), $_SERVER['argv'][3]);
        echo 'Release successfully saved.'.PHP_EOL;
    }
    
    public function printReleaseUsage()
    {
        echo '
Usage: pearscs release packagefile maintainer
    This will release the package to the channel.
    
    packagefile The release .tgz file.
    maintainer  The channel maintainer performing the release.
    
';
    }
    
    public function handleCreate()
    {
        if ($_SERVER['argc'] < 4) {
            $this->printCreateUsage();
            return false;
        }
        $name        = $_SERVER['argv'][2];
        $summary     = $_SERVER['argv'][3];
        list($alias) = explode('.', $name);
        $file        = getcwd().'/channel.xml';
        if (isset($_SERVER['argv'][4])) {
            $alias = $_SERVER['argv'][4];
        }
        if (isset($_SERVER['argv'][5])) {
            $file = $_SERVER['argv'][5];
        }
        $this->channel = new PEAR2_SimpleChannelServer_Channel($name, $summary, $alias);
        $scs = new PEAR2_SimpleChannelServer($this->channel,
            $this->dir);
        $scs->saveChannel();
        echo '
Created '.$name.'
      | ./channel.xml
      | ./rest/
      | ./get/'.PHP_EOL;
        return true;
    }
    
    public function printCreateUsage()
    {
        echo '
Usage: pearscs create pear.example.com summary [alias] [./channel.xml]
    This will create a file named channel.xml for the pear channel pear.example.com.
    
    summary  This is the a description for the channel.
    alias    Channel alias pear users can use as a shorthand.
    filename Path to where to create the channel.xml file. Current directory will be
             used by default.
    
';
    }
    
    public function printUpdateUsage()
    {
        echo '
Usage: pearscs update maintainer [channel.xml]
    This will update all releases within the /get/ directory.
';
    }
    
    public function printUsage()
    {
        echo '
Usage: pearscs update|create|add-maintainer|add-category|release [option]
    Commands:
        update [channel.xml]                  Update the channel xml files.
        create pear.example.com summary [...] Create a new channel.
        add-maintainer handle                 Add a maintainer.
        add-category category                 Add a category.
        release package.tgz maintainer        Release package.
';
    }
}

?>