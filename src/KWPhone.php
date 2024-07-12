<?php

namespace MMAE\Phones;

use MMAE\Phones\Base\BasePhone;


class KWPhone extends BasePhone
{
    public function __construct(string $number)
    {
        parent::__construct($number, 'KW');
    }

    /**
     * create new object
     * @param string $number
     * @return KWPhone
     */
    public static function make(string $number): KWPhone
    {
        return new static($number);
    }
}
