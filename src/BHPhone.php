<?php

namespace MMAE\Phones;

use MMAE\Phones\Base\BasePhone;


class BHPhone extends BasePhone
{
    public function __construct(string $number)
    {
        parent::__construct($number, 'BH');
    }

    /**
     * create new object
     * @param string $number
     * @return BHPhone
     */
    public static function make(string $number): BHPhone
    {
        return new static($number);
    }
}
