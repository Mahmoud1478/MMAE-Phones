<?php

namespace MMAE\Phones;

use MMAE\Phones\Base\BasePhone;


class AEPhone extends BasePhone
{
    /**
     * create new object
     * @param string $number
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'AE');
    }

    /**
     * create new object
     * @param string $number
     * @return AEPhone
     */
    public static function make(string $number): AEPhone
    {
        return new static($number);
    }
}
