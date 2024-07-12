<?php

namespace MMAE\Phones\Tests\Unit;

use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\BHPhone;
use MMAE\Phones\tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class BHPhoneTest extends TestCase
{

    #[Test]
    public function can_create_a_phone_object()
    {
        $phone = BHPhone::make('+97338000000');
        $this->assertInstanceOf(BHPhone::class, $phone);
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_country_key_without_plus()
    {
        $phone = BHPhone::make('038000000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_local_key()
    {
        $phone = BHPhone::make('038000000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_country_key()
    {
        $phone = BHPhone::make('038000000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_country_key_with_plus()
    {
        $phone = BHPhone::make('+97338000000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_valid_with_country_key_with_double_zeros()
    {
        $phone = BHPhone::make('0097338000000');
        $this->assertTrue($phone->isValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_not_valid_because_of_the_length()
    {
        $phone = BHPhone::make('9733800000');
        $this->assertTrue($phone->isNotValid());
    }

    #[Test]
    public function determine_if_the_phone_number_is_not_valid_because_of_the_key()
    {
        $phone = BHPhone::make('96638000000');
        $this->assertTrue($phone->isNotValid());
    }


    #[Test]
    public function determine_if_the_phone_number_is_not_valid_because_of_the_starter_number()
    {
        $phone = BHPhone::make('200091299453');
        $this->assertTrue($phone->isNotValid());
    }

    #[Test]
    public function can_make_all_possible_number_shapes()
    {
        $phone = BHPhone::make('038000000');
        $phone_shapes = [
            '2038000000',
            '973038000000',
            '0097338000000',
            '038000000'
        ];
        $this->assertArrayIsIdenticalToArrayOnlyConsideringListOfKeys($phone_shapes, $phone->all(),[]);

    }

    #[Test]
    public function global_static_variables_effect_children()
    {
        BasePhone::$plus = true;
        $phone = BHPhone::make('038000000');
        $this->assertEquals('+97338000000', $phone->toString());
    }



}
