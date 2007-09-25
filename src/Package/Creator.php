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
        $package->attribs['packagerversion'] = self::VERSION;
        $packagingarr = $package->toArray(true); // get packaging package.xml
        $creator->addFile($packagexml, (string) new PEAR2_Pyrus_XMLWriter($packagingarr));
        // $packageat is the relative path within the archive
        // $info is an array of format:
        // array('attribs' => array('name' => ...)[, 'tasks:blah' ...])
        foreach ($package->packagingcontents as $packageat => $info) {
            $contents = $package->getFileContents($info['attribs']['name']);
            foreach (new PEAR2_Pyrus_Package_Creator_TaskIterator($info, $package) as
                     $task) {
                // do pre-processing of file contents
                try {
                    // TODO: get last installed version into that last "null"
                    $task[1]->init($task[0], $info['attribs'], null);
                    $newcontents = $task[1]->startSession($package, $contents, $packageat);
                    if ($newcontents) {
                        $contents = $newcontents;
                    }
                } catch (Exception $e) {
                    // TODO: handle exceptions
                }
            }
            foreach ($this->_creators as $creator) {
                $creator->mkdir(dirname($packageat));
                $creator->addFile($packageat, $contents);
            }
        }
        foreach ($this->_creators as $creator) {
            $creator->close();
        }
    }
}