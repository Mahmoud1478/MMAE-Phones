<?php

namespace MMAE\Phones;

use MMAE\Phones\Base\BasePhone;


class KSAPhone extends BasePhone
{
    public function __construct(string $number)
    {
        parent::__construct($number, 'KSA');
    }

    /**
     * create new object
     * @param string $number
     * @return KSAPhone
     */
    public static function make(string $number): KSAPhone
    {
        return new static($number);
    }
}
