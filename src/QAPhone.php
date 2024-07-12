<?php

namespace MMAE\Phones;

use MMAE\Phones\Base\BasePhone;


class QAPhone extends BasePhone
{
    public function __construct(string $number)
    {
        parent::__construct($number, 'QA');
    }

    /**
     * create new object
     * @param string $number
     * @return QAPhone
     */
    public static function make(string $number): QAPhone
    {
        return new static($number);
    }
}
