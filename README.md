# MMAE/Phones

### laravel package to make interact with phone number easier

## 1. Supported Countries  
        * Egypt (EG)
        * Soudi Arabia (KSA)
        * Lybia (LY)
        * Qtar (QT)
        * Oman (OM)
        * Arab Emirates (AU)
        * Bahrain (BH)
        * kwit (KW)
## 2. Installation
```shell
    composer require mmae/phones
```

## 3. Usage
### -  validate the number before saving the record
```php
<?php
use Illuminate\Http\Request;
use MMAE\Phones\EGPhone;
class  UserController  extends App\Http\Controllers\Controller {
    function store(Request $request) {
        $data = $request->validate([
            'name' => 'required'
            'phone' = 'required'
        ])
        $phone = EGPhone::make($data['phone']);   
        if ($phone->isNotValid()){
            return back()->withErrors([
                'phone' => 'wrong format'
            ])
        }
        $data ['name'] = $phone; // if you save the full version of the number
        \App\Models\User::create($data);
        return back()->with('success','created')
    }   
}
```
### - validate the number before sending sms if you supporting multiple country registration
```php
    
    $phone = \MMAE\Phones\Phone::make($user->phone, $user->country_code)
    if($phone->isNotValid()){
        throw new Exception('wrong format')
    }
    $SMSService ->message('hello')->to($phone->withPlus())->send()
```
