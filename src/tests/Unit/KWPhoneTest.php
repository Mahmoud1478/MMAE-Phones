<?php

namespace MMAE\Phones\Tests\Unit;

use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\KWPhone;
use MMAE\Phones\tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class KWPhoneTest extends TestCase
{

    #[Test]
    public function can_create_a_phone_object()
    {
        $phone = KWPhone::make('060000000');
        $this->assertInstanceOf(KWPhone::class, $phone);
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_country_key_without_plus()
    {
        $phone = KWPhone::make('965 6000 0000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_local_key()
    {
        $phone = KWPhone::make('060000000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_country_key()
    {
        $phone = KWPhone::make('96560000000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_country_key_with_plus()
    {
        $phone = KWPhone::make('+96560000000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_country_key_with_double_zeros()
    {
        $phone = KWPhone::make('0096560000000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_not_valid_because_of_the_length()
    {
        $phone = KWPhone::make('06000000055');
        $this->assertTrue($phone->isNotValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_not_valid_because_of_the_key()
    {
        $phone = KWPhone::make('966060000000');
        $this->assertTrue($phone->isNotValid());
    }


    #[Test]
    public function determine_if_the_phone_number_is_not_valid_because_of_the_starter_number()
    {
        $phone = KWPhone::make('96540000000');
        $this->assertTrue($phone->isNotValid());
    }

    #[Test]
    public function can_make_all_possible_number_shapes()
    {
        $phone = KWPhone::make('060000000');
        $phone_shapes = [
            '96560000000',
            '+96560000000',
            '0096560000000',
            '060000000'
        ];
        $this->assertArrayIsIdenticalToArrayOnlyConsideringListOfKeys($phone_shapes, $phone->all(),[]);

    }

    #[Test]
    public function global_static_variables_effect_children()
    {
        BasePhone::$plus = true;
        $phone = KWPhone::make('060000000');
        $this->assertEquals('+96560000000', $phone->toString());
    }



}
