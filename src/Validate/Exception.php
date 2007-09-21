<?php
class PEAR2_Pyrus_Validate_Exception extends PEAR2_Exception
{
    /**
     * package.xml field that failed channel-specific validation
     *
     * @var string
     */
    public $field;
    /**
     * The reason that validation failed
     *
     * @var string
     */
    public $reason;
    /**
     * Set up message/field combination for package.xml validation
     *
     * @param string $msg
     * @param string $field
     */
    public function __construct($msg, $field)
    {
        $this->reason = $msg;
        $msg = 'Channel validator error: field "' . $field . '" - "' .
                    $msg;
        parent::__construct($msg);
        $this->field = $field;
    }
}