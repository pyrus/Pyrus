<?php
class PEAR2_Pyrus_ChannelFile_v1_Servers implements ArrayAccess, Countable
{
    /**
     * 
     * @var PEAR2_Pyrus_ChannelFile_v1
     */
    protected $parent;
    
    protected $info = array();
    
    protected $type = 'primary';
    
    function __construct($info, PEAR2_Pyrus_ChannelFile_v1 $parent, $type = 'primary')
    {
        if (isset($info['mirror']) && !isset($info['mirror'][0])) {
            $info['mirror'] = array($info['mirror']);
        }
        $this->info = $info;
        $this->parent = $parent;
    }

    function count()
    {
        if (!isset($this->info['mirror'])) {
            return 0;
        }
        return count($this->info['mirror']);
    }

    /**
     * Determines whether a channel supports Representational State Transfer (REST) protocols
     * for retrieving channel information
     * @return bool
     */
    function supportsREST()
    {
        return isset($this->info['servers']['primary']['rest']);
    }

    function getREST()
    {
        if (isset($this->channelInfo['servers']['primary']['rest'])) {
            return $this->channelInfo['servers']['primary']['rest']['baseurl'];
        }

        return false;
    }

    /**
     * Get the URL to access a base resource.
     *
     * Hyperlinks in the returned xml will be used to retrieve the proper information
     * needed.  This allows extreme extensibility and flexibility in implementation
     * @param string Resource Type to retrieve
     */
    function getBaseURL($resourceType)
    {
        $rest = $this->getREST();
        if (!isset($rest[0])) {
            $rest = array($rest);
        }

        foreach ($rest as $baseurl) {
            if (strtolower($baseurl['attribs']['type']) == strtolower($resourceType)) {
                return $baseurl['_content'];
            }
        }

        return false;
    }
    
    function offsetExists($mirror)
    {
        foreach ($this->info as $type=>$details) {
            if ($type == 'mirror'
                && isset($details[0])
                && $details[0]['attribs']['host'] == $mirror) {
                return true;
            }
        }
        return false;
    }
    
    function offsetUnset($type)
    {
                        throw new Exception('not there yet');
        
    }
    
    function offsetGet($mirror)
    {
        if (!isset($this->info['mirror'])) {
            return new PEAR2_Pyrus_ChannelFile_v1_Mirror(array('attribs' => array('host' => $mirror)), $this, $this->parent, 0);
        }
        foreach ($this->info['mirror'] as $i => $details) {
            if (isset($details['attribs']) && isset($details['attribs']['host']) &&
                $details['attribs']['host'] == $mirror) {
                return new PEAR2_Pyrus_ChannelFile_v1_Mirror($details, $this, $this->parent, $i);
            }
        }
        
        return new PEAR2_Pyrus_ChannelFile_v1_Mirror(array('attribs' => array('host' => $mirror)), $this, $this->parent, count($this->info['mirror']));
    }
    
    function offsetSet($type, $value)
    {
        throw new Exception('not there yet');
    }

    function setMirror($index, $info)
    {
        $this->info['mirror'][$index] = $info;
    }

    function save()
    {
        $info = $this->info;
        if (!$info) {
            return $this->parent->rawmirrors = null;
        }
        if (count($info['mirror']) === 1) {
            return $this->parent->rawmirrors = $info['mirror'][0];
        }
        $this->parent->rawmirrors = $info['mirror'];
    }
}