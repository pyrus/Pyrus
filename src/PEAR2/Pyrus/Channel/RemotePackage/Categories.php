<?php
namespace Pyrus\Channel\RemotePackage;
use Pyrus\Channel;
class Categories extends Channel\RemoteCategories
{
	/**
	 * @var Pyrus\Channel\RemotePackage
	 */
	protected $package;

	function __construct(\Pyrus\ChannelInterface $channelinfo, Channel\RemotePackage $package)
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