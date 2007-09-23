<?php
class PEAR2_Pyrus_Package_Creator
{
    const VERSION = '@PACKAGE_VERSION@';
    private $_creators;
    /**
     * Begin package creation
     *
     * @param array|PEAR2_Pyrus_Package_ICreator $creators
     */
    function __construct($creators)
    {
        if ($creators instanceof PEAR2_Pyrus_Package_ICreator) {
            $this->_creators = array($creators);
        } elseif (is_array($creators)) {
            foreach ($creators as $creator) {
                if ($creator instanceof PEAR2_Pyrus_Package_ICreator) {
                    continue;
                }
                throw new PEAR2_Pyrus_Package_Creator_Exception('Invalid ' .
                    'PEAR2 package creator passed into PEAR2_Pyrus_Package_Creator');
            }
            $this->_creators = $creators;
        } else {
            throw new PEAR2_Pyrus_Package_Creator_Exception('Invalid ' .
                'PEAR2 package creator passed into PEAR2_Pyrus_Package_Creator');
        }
    }

    function render(PEAR2_Pyrus_Package $package)
    {
        foreach ($this->_creators as $creator) {
            $creator->init();
        }
        $packagexml = 'package-' . $package->channel . '-' . $package->name .
            $package->version['release'] . '.xml';
        $info = $package->toArray();
        $info['p']['attribs']['packagerversion'] = self::VERSION;
        $creator->addFile($packagexml, '');
        // $packageat is the relative path within the archive
        // $info is an array of format:
        // array('attribs' => array('name' => ...)[, 'tasks:blah' ...])
        foreach ($package->packagingcontents as $packageat => $info) {
            foreach ($this->_creators as $creator) {
                $creator->mkdir(dirname($packageat));
                $creator->addFile($packageat, $package->getFileContents($file));
            }
        }
        foreach ($this->_creators as $creator) {
            $creator->close();
        }
    }
}