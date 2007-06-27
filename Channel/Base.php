<?php
abstract class PEAR2_Pyrus_Channel_Base implements PEAR2_Pyrus_IChannel
{
    /**
     * Determines whether a mirror supports Representational State Transfer (REST) protocols
     * for retrieving channel information
     * @return bool
     */
    function supportsREST()
    {
        return (bool) $this->getREST();
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
        if (!$rest) {
            return false;
        }
        if (!isset($rest['baseurl'][0])) {
            $rest['baseurl'] = array($rest['baseurl']);
        }
        foreach ($rest['baseurl'] as $baseurl) {
            if (strtolower($baseurl['attribs']['type']) == strtolower($resourceType)) {
                return $baseurl['_content'];
            }
        }
        return false;
    }

    /**
     * @param string protocol type
     * @param string protocol name
     * @param string version
     * @return boolean
     */
    function supports($type, $name = null, $version = '1.0')
    {
        $protocols = $this->getFunctions($type);
        if (!$protocols) {
            return false;
        }
        foreach ($protocols as $protocol) {
            if ($protocol['attribs']['version'] != $version) {
                continue;
            }
            if ($name === null) {
                return true;
            }
            if ($protocol['_content'] != $name) {
                continue;
            }
            return true;
        }
        return false;
    }

    /**
     * @param string Protocol type
     * @param string Function name (null to return the
     *               first protocol of the type requested)
     * @return array
     */
    function getFunction($type, $name = null)
    {
        $protocols = $this->getFunctions($type);
        if (!$protocols) {
            return false;
        }
        foreach ($protocols as $protocol) {
            if ($name === null) {
                return $protocol;
            }
            if ($protocol['_content'] != $name) {
                continue;
            }
            return $protocol;
        }
        return false;
    }
}