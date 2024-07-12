<?php

namespace MMAE\Phones;

use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Contracts\PhoneMakable;

class LYPhone extends BasePhone
{
    public function __construct(string $number)
    {
        parent::__construct($number, 'LY');
    }

    /**
     * create new object
     * @param string $number
     * @return LYPhone
     */
    public static function make(string $number): LYPhone
    {
        return new static($number);
    }
}
