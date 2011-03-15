<?php
namespace PEAR2\Pyrus\Channel\RemotePackage;
use PEAR2\Pyrus\Channel;
class Categories extends Channel\RemoteCategories
{
	/**
	 * @var PEAR2\Pyrus\Channel\RemotePackage
	 */
	protected $package;

	function __construct(\PEAR2\Pyrus\ChannelInterface $channelinfo, Channel\RemotePackage $package)
	{
		$this->package = $package;
		parent::__construct($channelinfo);
	}
	
	function rewind()
	{
		$info = $this->package->getPackageInfo($this->package->name);
        $this->categoryList = $info['ca'];
        if (!isset($this->categoryList[0])) {
            $this->categoryList = array($this->categoryList);
        }
	}
}