<?php
namespace PEAR2\Pyrus\ChannelFile\v1;
class Servers implements \ArrayAccess, \Countable, \Iterator
{
    /**
     * @var \PEAR2\Pyrus\ChannelFile\v1
     */
    protected $parent;

    protected $info = array();

    protected $type = 'primary';

    function __construct($info, \PEAR2\Pyrus\ChannelFile\v1 $parent)
    {
        if (isset($info['mirror']) && !isset($info['mirror'][0])) {
            $info['mirror'] = array($info['mirror']);
        }

        $this->info   = $info;
        $this->parent = $parent;
    }

    function current()
    {
        $info = current($this->info['mirror']);
        return new Mirror($info, $this, $this->parent, key($this->info['mirror']));
    }

    function rewind()
    {
        if (!isset($this->info['mirror'])) {
            return;
        }

        reset($this->info['mirror']);
    }

    function key()
    {
        return key($this->info['mirror']);
    }

    function next()
    {
        return next($this->info['mirror']);
    }

    function valid()
    {
        if (!isset($this->info['mirror'])) {
            return false;
        }

        return current($this->info['mirror']);
    }

    function count()
    {
        if (!isset($this->info['mirror'])) {
            return 0;
        }

        return count($this->info['mirror']);
    }

    function offsetExists($mirror)
    {
        foreach ($this->info as $type=>$details) {
            if ($type == 'mirror'
                && isset($details[0])
                && $details[0]['attribs']['host'] == $mirror
            ) {
                return true;
            }
        }

        return false;
    }

    function offsetUnset($mirror)
    {
        if (!isset($this->info['mirror'])) {
            return;
        }

        foreach ($this->info['mirror'] as $i => $details) {
            if (isset($details['attribs']) && isset($details['attribs']['host']) &&
                    $details['attribs']['host'] == $mirror
            ) {
                unset($this->info['mirror'][$i]);
                $this->info['mirror'] = array_values($this->info['mirror']);
                return $this->save();
            }
        }
    }

    function offsetGet($mirror)
    {
        if (!isset($this->info['mirror'])) {
            return new Mirror(array('attribs' => array('host' => $mirror)), $this, $this->parent, 0);
        }

        foreach ($this->info['mirror'] as $i => $details) {
            if (isset($details['attribs']) && isset($details['attribs']['host']) &&
                $details['attribs']['host'] == $mirror
            ) {
                return new Mirror($details, $this, $this->parent, $i);
            }
        }

        return new Mirror(array('attribs' => array('host' => $mirror)), $this, $this->parent, count($this->info['mirror']));
    }

    function offsetSet($mirror, $value)
    {
        if ($value === null) {
            return $this->offsetUnset($mirror);
        }

        if (!($value instanceof Mirror)) {
            throw new \PEAR2\Pyrus\ChannelFile\Exception('Can only set mirror to a ' .
                        '\PEAR2\Pyrus\ChannelFile\v1\Mirror object');
        }

        $info = $value->getInfo();
        if ($mirror != $value->server) {
            $info['attribs']['host'] = $mirror;
        }

        foreach ($this->info['mirror'] as $i => $details) {
            if (isset($details['attribs']) && isset($details['attribs']['host']) &&
                $details['attribs']['host'] == $mirror
            ) {
                $this->setMirror($i, $info);
                return $this->save();
            }
        }

        $this->setMirror(count($this->info['mirror']), $info);
        $this->save();
    }

    function setMirror($index, $info)
    {
        $this->info['mirror'][$index] = $info;
    }

    function save()
    {
        $info = $this->info;
        if (!$info['mirror']) {
            return $this->parent->rawmirrors = null;
        }

        if (count($info['mirror']) === 1) {
            return $this->parent->rawmirrors = $info['mirror'][0];
        }

        $this->parent->rawmirrors = $info['mirror'];
    }
}