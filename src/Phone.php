<?php

namespace MMAE\Phones;

use MMAE\Phones\Base\BasePhone;


class Phone extends BasePhone
{
    /**
     * create new object
     * @param string $number
     * @param string $countryCode
     * @return Phone
     */
    public static function make(string $number, string $countryCode): Phone
    {
        return new static($number,$countryCode);
    }
}
