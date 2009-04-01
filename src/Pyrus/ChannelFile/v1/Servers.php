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
            return 1;
        }
        return count($this->info['mirror']) + 1;
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
            return false;
        }
        
        foreach ($this->info['mirror'] as $details) {
            if ($details['attribs']['host'] == $mirror) {
                return new PEAR2_Pyrus_Channel_Mirror($details, $this->parent);
            }
        }
        
        return false;
    }
    
    function offsetSet($type, $value)
    {
        throw new Exception('not there yet');
    }
}