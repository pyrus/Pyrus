<?php
namespace pear2\Pyrus\ChannelFile\v1\Servers\Protocols;
class REST implements \ArrayAccess, \Countable, \Iterator
{
    protected $info;
    protected $parent;
    protected $index;
    
    function __construct($info, $parent, $index = null)
    {
        if (isset($info['baseurl']) && !isset($info['baseurl'][0])) {
            $info['baseurl'] = array($info['baseurl']);
        }
        $this->info = $info;
        $this->parent = $parent;
        $this->index = $index;
    }

    function current()
    {
        $info = current($this->info['baseurl']);
        if ($info['_content'][strlen($info['_content'])-1] != '/') {
            return $info['_content'] . '/';
        }
        return $info['_content'];
    }

    function rewind()
    {
        if (isset($this->info['baseurl'])) {
            reset($this->info['baseurl']);
        }
    }

    function key()
    {
        return $this->info['baseurl'][key($this->info['baseurl'])]['attribs']['type'];
    }

    function next()
    {
        return next($this->info['baseurl']);
    }

    function valid()
    {
        if (!isset($this->info['baseurl'])) {
            return false;
        }
        return current($this->info['baseurl']);
    }

    function count()
    {
        if (!isset($this->info['baseurl'])) {
            return 0;
        }
        return count($this->info['baseurl']);
    }
    
    function __get($var)
    {
        if (!isset($this->index)) {
            throw new \pear2\Pyrus\ChannelFile\Exception('Cannot use -> to access '
                    . 'REST protocols, use []');
        }
        if ($var === 'baseurl') {
            if (isset($this->info['_content']) &&
                    $this->info['_content'][strlen($this->info['_content'])-1] != '/') {
                return $this->info['_content'] . '/';
            }
            return $this->info['_content'];
        }
        throw new \pear2\Pyrus\ChannelFile\Exception('Unknown variable ' . $var);
    }

    function __set($var, $value)
    {
        if (!isset($this->index)) {
            throw new \pear2\Pyrus\ChannelFile\Exception('Cannot use -> to access '
                    . 'REST protocols, use []');
        }
        if ($var === 'baseurl') {
            $this->info['_content'] = $value;
            return $this->save();
        }
        throw new \pear2\Pyrus\ChannelFile\Exception('Unknown variable ' . $var);
    }
    
    function offsetGet($protocol)
    {
        if (isset($this->index)) {
            throw new \pear2\Pyrus\ChannelFile\Exception('Cannot use [] to access '
                    . 'baseurl, use ->');
        }
        if (!isset($this->info['baseurl'])) {
            return new \pear2\Pyrus\ChannelFile\v1\Servers\Protocols\REST(
                array('attribs' => array('type' => $protocol), '_content' => null),
                $this, 0);
        }
        foreach ($this->info['baseurl'] as $baseurl) {
            if (strtolower($baseurl['attribs']['type']) == strtolower($protocol)) {
                $ret = new \pear2\Pyrus\ChannelFile\v1\Servers\Protocols\REST(
                    $baseurl, $this, $protocol
                );
                return $ret;
            }
        }
        return new \pear2\Pyrus\ChannelFile\v1\Servers\Protocols\REST(
            array('attribs' => array('type' => $protocol), '_content' => null),
            $this, count($this->info['baseurl']));
    }
    
    function offsetSet($protocol, $value)
    {
        if (isset($this->index)) {
            throw new \pear2\Pyrus\ChannelFile\Exception('Cannot use [] to access '
                    . 'baseurl, use ->');
        }
        if (!($value instanceof self)) {
            throw new \pear2\Pyrus\ChannelFile\Exception('Can only set REST protocol ' .
                        ' to a \pear2\Pyrus\ChannelFile\v1\Servers\Protocol\REST object');
        }
        if (!isset($this->info['baseurl'])) {
            $this->info['baseurl'] = $value->getInfo();
            return $this->save();
        }
        foreach ($this->info['baseurl'] as $i => $baseurl) {
            if (strtolower($baseurl['attribs']['type']) == strtolower($protocol)) {
                $this->info['baseurl'][$i] = $value->getInfo();
                return $this->save();
            }
        }
        $this->info['baseurl'][] = $value->getInfo();
        return $this->save();
    }
    
    function offsetExists($protocol)
    {
        if (isset($this->index)) {
            throw new \pear2\Pyrus\ChannelFile\Exception('Cannot use [] to access '
                    . 'baseurl, use ->');
        }
        foreach ($this->info['baseurl'] as $baseurl) {
            if (strtolower($baseurl['attribs']['type']) == strtolower($protocol)) {
                return true;
            }
        }
        return false;
    }
    
    function offsetUnset($protocol)
    {
        if (isset($this->index)) {
            throw new \pear2\Pyrus\ChannelFile\Exception('Cannot use [] to access '
                    . 'baseurl, use ->');
        }
        foreach ($this->info['baseurl'] as $i => $baseurl) {
            if (strtolower($baseurl['attribs']['type']) == strtolower($protocol)) {
                unset($this->info['baseurl'][$i]);
                $this->info['baseurl'] = array_values($this->info['baseurl']);
                return $this->save();
            }
        }
    }

    function getInfo()
    {
        return $this->info;
    }

    function save()
    {
        if ($this->parent instanceof self) {
            $this->parent[$this->info['attribs']['type']] = $this;
            return $this->parent->save();
        }
        $info = $this->info;
        if (isset($info['baseurl']) && count($info['baseurl']) == 1) {
            $info['baseurl'] = $info['baseurl'][0];
        }
        $this->parent->rawrest = $info;
    }
}
?>