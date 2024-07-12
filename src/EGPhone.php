<?php

namespace MMAE\Phones;

use MMAE\Phones\Base\BasePhone;


class EGPhone extends BasePhone
{
    /**
     * create new object
     * @param string $number
     */
    public function __construct(string $number)
    {
        parent::__construct($number, 'EG');
    }

    /**
     * create new object
     * @param string $number
     * @return EGPhone
     */
    public static function make(string $number): EGPhone
    {
        return new static($number);
    }
}
