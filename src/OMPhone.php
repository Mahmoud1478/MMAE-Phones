<?php

namespace MMAE\Phones;

use MMAE\Phones\Base\BasePhone;


class OMPhone extends BasePhone
{
    /**
     * create new object
     * @param string $number
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'OM');
    }

    /**
     * create new object
     * @param string $number
     * @return OMPhone
     */
    public static function make(string $number): OMPhone
    {
        return new static($number);
    }
}
