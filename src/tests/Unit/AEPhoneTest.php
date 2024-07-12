<?php

namespace MMAE\Phones\Tests\Unit;

use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\AEPhone;
use MMAE\Phones\tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AEPhoneTest extends TestCase
{

    #[Test]
    public function can_create_a_phone_object()
    {
        $phone = AEPhone::make('0550000000');
        $this->assertInstanceOf(AEPhone::class, $phone);
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_country_key_without_plus()
    {
        $phone = AEPhone::make('971550000000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_local_key()
    {
        $phone = AEPhone::make('0550000000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_country_key()
    {
        $phone = AEPhone::make('0550000000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_country_key_with_plus()
    {
        $phone = AEPhone::make('+971550000000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_country_key_with_double_zeros()
    {
        $phone = AEPhone::make('00971550000000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_not_valid_because_of_the_length()
    {
        $phone = AEPhone::make('97155000000');
        $this->assertTrue($phone->isNotValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_not_valid_because_of_the_key()
    {
        $phone = AEPhone::make('977550000000');
        $this->assertTrue($phone->isNotValid());
    }


    #[Test]
    public function determine_if_the_phone_number_is_not_valid_because_of_the_starter_number()
    {
        $phone = AEPhone::make('971450000000');
        $this->assertTrue($phone->isNotValid());
    }

    #[Test]
    public function can_make_all_possible_number_shapes()
    {
        $phone = AEPhone::make('0550000000');
        $phone_shapes = [
            '971550000000',
            '+971550000000',
            '00971550000000',
            '0550000000'
        ];
        $this->assertArrayIsIdenticalToArrayOnlyConsideringListOfKeys($phone_shapes, $phone->all(),[]);

    }

    #[Test]
    public function global_static_variables_effect_children()
    {
        BasePhone::$plus = true;
        $phone = AEPhone::make('0550000000');
        $this->assertEquals('+971550000000', $phone->toString());
    }



}
