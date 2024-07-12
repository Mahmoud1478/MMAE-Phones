<?php

namespace MMAE\Phones\Tests\Unit;

use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\LYPhone;
use MMAE\Phones\tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LYPhoneTest extends TestCase
{

    #[Test]
    public function can_create_a_phone_object()
    {
        $phone = LYPhone::make('0910000000');
        $this->assertInstanceOf(LYPhone::class, $phone);
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_country_key_without_plus()
    {
        $phone = LYPhone::make('0910000000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_local_key()
    {
        $phone = LYPhone::make('0910000000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_country_key()
    {
        $phone = LYPhone::make('0910000000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_country_key_with_plus()
    {
        $phone = LYPhone::make('+218910000000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_country_key_with_double_zeros()
    {
        $phone = LYPhone::make('00218910000000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_not_valid_because_of_the_length()
    {
        $phone = LYPhone::make('0109000000');
        $this->assertTrue($phone->isNotValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_not_valid_because_of_the_key()
    {
        $phone = LYPhone::make('9660910000000');
        $this->assertTrue($phone->isNotValid());
    }


    #[Test]
    public function determine_if_the_phone_number_is_not_valid_because_of_the_starter_number()
    {
        $phone = LYPhone::make('200091299453');
        $this->assertTrue($phone->isNotValid());
    }

    #[Test]
    public function can_make_all_possible_number_shapes()
    {
        $phone = LYPhone::make('0910000000');
        $phone_shapes = [
            '218910000000',
            '+218910000000',
            '0020910000000',
            '0910000000'
        ];
        $this->assertArrayIsIdenticalToArrayOnlyConsideringListOfKeys($phone_shapes, $phone->all(),[]);

    }

    #[Test]
    public function global_static_variables_effect_children()
    {
        BasePhone::$plus = true;
        $phone = LYPhone::make('0910000000');
        $this->assertEquals('+218910000000', $phone->toString());
    }



}
