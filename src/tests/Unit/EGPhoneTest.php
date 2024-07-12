<?php

namespace MMAE\Phones\Tests\Unit;

use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\EGPhone;
use MMAE\Phones\tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class EGPhoneTest extends TestCase
{

    #[Test]
    public function can_create_a_phone_object()
    {
        $phone = EGPhone::make('01090000000');
        $this->assertInstanceOf(EGPhone::class, $phone);
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_country_key_without_plus()
    {
        $phone = EGPhone::make('01090000000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_local_key()
    {
        $phone = EGPhone::make('01090000000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_country_key()
    {
        $phone = EGPhone::make('01090000000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_country_key_with_plus()
    {
        $phone = EGPhone::make('+201090000000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_country_key_with_double_zeros()
    {
        $phone = EGPhone::make('00201090000000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_not_valid_because_of_the_length()
    {
        $phone = EGPhone::make('0109000000');
        $this->assertTrue($phone->isNotValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_not_valid_because_of_the_key()
    {
        $phone = EGPhone::make('96601090000000');
        $this->assertTrue($phone->isNotValid());
    }


    #[Test]
    public function determine_if_the_phone_number_is_not_valid_because_of_the_starter_number()
    {
        $phone = EGPhone::make('200091299453');
        $this->assertTrue($phone->isNotValid());
    }

    #[Test]
    public function can_make_all_possible_number_shapes()
    {
        $phone = EGPhone::make('01090000000');
        $phone_shapes = [
            '201090000000',
            '+201090000000',
            '00201090000000',
            '01090000000'
        ];
        $this->assertArrayIsIdenticalToArrayOnlyConsideringListOfKeys($phone_shapes, $phone->all(),[]);

    }

    #[Test]
    public function global_static_variables_effect_children()
    {
        BasePhone::$plus = true;
        $phone = EGPhone::make('01090000000');
        $this->assertEquals('+201090000000', $phone->toString());
    }



}
