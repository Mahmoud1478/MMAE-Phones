<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Configs\RuleConfig;
use MMAE\Phones\Rules\EGPhoneRule;

beforeEach(function () {
    config()->set('database.default', 'phones_testing');
    config()->set('database.connections.phones_testing', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ]);

    Schema::create('users', function ($table) {
        $table->increments('id');
        $table->string('phone');
    });

    // stored in international form; a local-form input must still match it
    DB::table('users')->insert(['phone' => '201000000000']);
});

afterEach(function () {
    Schema::dropIfExists('users');
});

test('exists passes when the number is stored in another accepted shape', function () {
    // input is the local form, the row holds the international form
    $validator = Validator::make(['phone' => '01000000000'], ['phone' => EGPhoneRule::make()->exists('users', 'phone')]);
    expect($validator->passes())->toBeTrue();
});

test('exists fails when the number is absent', function () {
    $validator = Validator::make(['phone' => '01111111111'], ['phone' => EGPhoneRule::make()->exists('users', 'phone')]);
    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('phone'))->toEqual('The selected phone does not exist.');
});

test('exists still enforces a valid format first', function () {
    $validator = Validator::make(['phone' => '999'], ['phone' => EGPhoneRule::make()->exists('users', 'phone')]);
    expect($validator->errors()->first('phone'))->toEqual('The phone is not a valid phone number. Expected format: 01[0,1,2,5]XXXXXXXX.');
});

test('unique fails when the number already exists in any shape', function () {
    $validator = Validator::make(['phone' => '+201000000000'], ['phone' => EGPhoneRule::make()->unique('users')]);
    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('phone'))->toEqual('The phone has already been taken.');
});

test('unique passes for a fresh number', function () {
    $validator = Validator::make(['phone' => '01111111111'], ['phone' => EGPhoneRule::make()->unique('users')]);
    expect($validator->passes())->toBeTrue();
});

test('unique can ignore a given record id', function () {
    $validator = Validator::make(['phone' => '01000000000'], ['phone' => EGPhoneRule::make()->unique('users', 'phone', ignore: 1)]);
    expect($validator->passes())->toBeTrue();
});

test('exists and unique accept custom messages', function () {
    $missing = Validator::make(['phone' => '01111111111'], ['phone' => EGPhoneRule::make()->exists('users', 'phone', message: 'no such phone')]);
    expect($missing->errors()->first('phone'))->toEqual('no such phone');

    $taken = Validator::make(['phone' => '01000000000'], ['phone' => EGPhoneRule::make()->unique('users', message: 'phone taken')]);
    expect($taken->errors()->first('phone'))->toEqual('phone taken');
});

test('callback can drive the exists check itself using the rule config', function () {
    $driver = function (BasePhone $phone, string $attribute, mixed $value, RuleConfig $config, Closure $fail) {
        if ($config->exists->enabled && ! DB::table($config->exists->table)->whereIn($config->exists->column, $phone->all())->exists()) {
            $fail(trans($config->exists->message));
        }
    };

    // present number passes, absent one fails, both driven by the callback
    $present = Validator::make(['phone' => '01000000000'], ['phone' => EGPhoneRule::make()->exists('users', 'phone')->validateUsing($driver)]);
    expect($present->passes())->toBeTrue();

    $absent = Validator::make(['phone' => '01111111111'], ['phone' => EGPhoneRule::make()->exists('users', 'phone')->validateUsing($driver)]);
    expect($absent->fails())->toBeTrue()
        ->and($absent->errors()->first('phone'))->toEqual('The selected phone does not exist.');
});
