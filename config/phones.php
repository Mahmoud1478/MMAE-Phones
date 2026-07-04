<?php

return [
    'LY' => [
        'code' => 'LY',
        'key' => '218',
        'local_key' => '0',
        'pattern' => '(?<provider>9[1-5])(?<digits>\d{7})',
    ],
    'EG' => [
        'code' => 'EG',
        'key' => '20',
        'local_key' => '0',
        'pattern' => '(?<provider>1[0125])(?<digits>\d{8})',
    ],
    'SA' => [
        'code' => 'SA',
        'key' => '966',
        'local_key' => '0',
        'pattern' => '(?<provider>5)(?<digits>\d{8})',
    ],
    'AE' => [
        'code' => 'AE',
        'key' => '971',
        'local_key' => '0',
        'pattern' => '(?<provider>5[024568])(?<digits>\d{7})',
    ],
    'KW' => [
        'code' => 'KW',
        'key' => '965',
        'local_key' => '0',
        'pattern' => '(?<provider>[6|9|5])(?<digits>\d{7})',
    ],
    'BH' => [
        'code' => 'BH',
        'key' => '973',
        'local_key' => '0',
        'pattern' => '(?<provider>3[2-9])(?<digits>\d{6})',
    ],
    'QA' => [
        'code' => 'QA',
        'key' => '974',
        'local_key' => '0',
        'pattern' => '(?<provider>[5|6|3|7]\d{1})(?<digits>\d{6})',
    ],
    'OM' => [
        'code' => 'OM',
        'key' => '968',
        'local_key' => '0',
        'pattern' => '(?<provider>[7|9]\d{1})(?<digits>\d{6})',
    ],
    'AF' => [
        'code' => 'AF',
        'key' => '93',
        'local_key' => '0',
        'pattern' => '(?<provider>7[0-9])(?<digits>\d{7})', // Afghanistan mobile: 7x
    ],
    // --- Added countries from SupportedCountriesEnum.php ---
    'JO' => [
        'code' => 'JO',
        'key' => '962',
        'local_key' => '0',
        'pattern' => '(?<provider>7[789])(?<digits>\d{7})', // Jordan mobile providers: 77, 78, 79
    ],
    'LB' => [
        'code' => 'LB',
        'key' => '961',
        'local_key' => '0',
        'pattern' => '(?<provider>3|7[01]|76|78|79)(?<digits>\d{6,7})', // Lebanon mobile providers: 3, 70, 71, 76, 78, 79
    ],
    'SY' => [
        'code' => 'SY',
        'key' => '963',
        'local_key' => '0',
        'pattern' => '(?<provider>9[3-9])(?<digits>\d{7})', // Syria mobile providers: 93-99
    ],
    'IQ' => [
        'code' => 'IQ',
        'key' => '964',
        'local_key' => '0',
        'pattern' => '(?<provider>7[3-9])(?<digits>\d{8})', // Iraq mobile: 7[3-9], NSN 10
    ],
    'US' => [
        'code' => 'US',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>\d{3})(?<digits>\d{7})', // US: area code as provider
    ],
    'DK' => [
        'code' => 'DK',
        'key' => '45',
        'local_key' => '',
        'pattern' => '(?<provider>\d{2})(?<digits>\d{6})', // Denmark: first 2 digits as provider
    ],
    'FI' => [
        'code' => 'FI',
        'key' => '358',
        'local_key' => '0',
        'pattern' => '(?<provider>4\d|5\d|6\d|45|50|46|40|41|42|44|45|46|50|51|52|53|54|55|56|58|59)(?<digits>\d{7,8})', // Finland: common mobile prefixes
    ],
    'FR' => [
        'code' => 'FR',
        'key' => '33',
        'local_key' => '0',
        'pattern' => '(?<provider>6|7)(?<digits>\d{8})', // France: 6, 7
    ],
    'DE' => [
        'code' => 'DE',
        'key' => '49',
        'local_key' => '0',
        'pattern' => '(?<provider>1[5-7])(?<digits>\d{8,9})', // Germany: 15, 16, 17
    ],
    'GR' => [
        'code' => 'GR',
        'key' => '30',
        'local_key' => '',
        'pattern' => '(?<provider>6[69])(?<digits>\d{8})', // Greece: 69
    ],
    'HU' => [
        'code' => 'HU',
        'key' => '36',
        'local_key' => '0',
        'pattern' => '(?<provider>20|30|70)(?<digits>\d{7})', // Hungary: 20, 30, 70
    ],
    'IS' => [
        'code' => 'IS',
        'key' => '354',
        'local_key' => '',
        'pattern' => '(?<provider>[3678]\d)(?<digits>\d{5})', // Iceland mobile: 3x/6x/7x/8x, NSN 7
    ],
    'IE' => [
        'code' => 'IE',
        'key' => '353',
        'local_key' => '0',
        'pattern' => '(?<provider>8[3-9])(?<digits>\d{7})', // Ireland: 83-89
    ],
    'IT' => [
        'code' => 'IT',
        'key' => '39',
        'local_key' => '',
        'pattern' => '(?<provider>3\d)(?<digits>\d{8,9})', // Italy: 3x
    ],
    'LV' => [
        'code' => 'LV',
        'key' => '371',
        'local_key' => '',
        'pattern' => '(?<provider>2\d)(?<digits>\d{6})', // Latvia: 2x
    ],
    'LT' => [
        'code' => 'LT',
        'key' => '370',
        'local_key' => '0',
        'pattern' => '(?<provider>6\d)(?<digits>\d{6})', // Lithuania: 6x
    ],
    'LU' => [
        'code' => 'LU',
        'key' => '352',
        'local_key' => '',
        'pattern' => '(?<provider>6\d)(?<digits>\d{6,7})', // Luxembourg: 6x
    ],
    'MT' => [
        'code' => 'MT',
        'key' => '356',
        'local_key' => '',
        'pattern' => '(?<provider>[79]\d)(?<digits>\d{6})', // Malta mobile: 7x/9x, NSN 8
    ],
    'MC' => [
        'code' => 'MC',
        'key' => '377',
        'local_key' => '0',
        'pattern' => '(?<provider>6\d)(?<digits>\d{6,7})', // Monaco: 6x
    ],
    'ME' => [
        'code' => 'ME',
        'key' => '382',
        'local_key' => '0',
        'pattern' => '(?<provider>6\d)(?<digits>\d{6})', // Montenegro: 6x
    ],
    'NL' => [
        'code' => 'NL',
        'key' => '31',
        'local_key' => '0',
        'pattern' => '(?<provider>6)(?<digits>\d{8})', // Netherlands: 6
    ],
    'NO' => [
        'code' => 'NO',
        'key' => '47',
        'local_key' => '',
        'pattern' => '(?<provider>4\d|9\d)(?<digits>\d{6})', // Norway: 4x, 9x
    ],
    'PL' => [
        'code' => 'PL',
        'key' => '48',
        'local_key' => '',
        'pattern' => '(?<provider>5\d|6\d|7\d|8\d)(?<digits>\d{7})', // Poland: 5x, 6x, 7x, 8x
    ],
    'PT' => [
        'code' => 'PT',
        'key' => '351',
        'local_key' => '',
        'pattern' => '(?<provider>9\d)(?<digits>\d{7})', // Portugal: 9x
    ],
    'RO' => [
        'code' => 'RO',
        'key' => '40',
        'local_key' => '0',
        'pattern' => '(?<provider>[67]\d)(?<digits>\d{7})', // Romania mobile: 6x/7x, NSN 9
    ],
    'RS' => [
        'code' => 'RS',
        'key' => '381',
        'local_key' => '0',
        'pattern' => '(?<provider>6\d)(?<digits>\d{7})', // Serbia: 6x
    ],
    'SK' => [
        'code' => 'SK',
        'key' => '421',
        'local_key' => '0',
        'pattern' => '(?<provider>9\d)(?<digits>\d{7})', // Slovakia: 9x
    ],
    'SI' => [
        'code' => 'SI',
        'key' => '386',
        'local_key' => '0',
        'pattern' => '(?<provider>3[01]|4[01]|51|64|68|70|71|73|77|78)(?<digits>\d{6,7})', // Slovenia: common mobile prefixes
    ],
    'ES' => [
        'code' => 'ES',
        'key' => '34',
        'local_key' => '',
        'pattern' => '(?<provider>6\d|7[1-9])(?<digits>\d{7})', // Spain: 6x, 71-79
    ],
    'SE' => [
        'code' => 'SE',
        'key' => '46',
        'local_key' => '0',
        'pattern' => '(?<provider>7[0236-9])(?<digits>\d{7,8})', // Sweden: 70, 72, 73, 76, 77, 78, 79
    ],
    'CH' => [
        'code' => 'CH',
        'key' => '41',
        'local_key' => '0',
        'pattern' => '(?<provider>7[5-9])(?<digits>\d{7})', // Switzerland: 75-79
    ],
    'TR' => [
        'code' => 'TR',
        'key' => '90',
        'local_key' => '0',
        'pattern' => '(?<provider>5\d)(?<digits>\d{8})', // Turkey: 5x
    ],
    'GB' => [
        'code' => 'GB',
        'key' => '44',
        'local_key' => '0',
        'pattern' => '(?<provider>7\d{3})(?<digits>\d{6})', // UK: 7xxx (10-digit national significant number)
    ],
    // --- End of added countries ---

    // --- Additional countries from SupportedCountriesEnum.php ---
    'VN' => [
        'code' => 'VN',
        'key' => '84',
        'local_key' => '0',
        'pattern' => '(?<provider>3[2-9]|5[2689]|7[0|6-9]|8[1-9]|9[0-9])(?<digits>\d{7})', // Vietnam mobile providers
    ],
    'IN' => [
        'code' => 'IN',
        'key' => '91',
        'local_key' => '0',
        'pattern' => '(?<provider>[6-9])(?<digits>\d{9})', // India mobile providers: 6,7,8,9
    ],
    'PK' => [
        'code' => 'PK',
        'key' => '92',
        'local_key' => '0',
        'pattern' => '(?<provider>3[0-9])(?<digits>\d{8})', // Pakistan mobile: 3x, NSN 10
    ],
    'BD' => [
        'code' => 'BD',
        'key' => '880',
        'local_key' => '0',
        'pattern' => '(?<provider>1[3-9])(?<digits>\d{8})', // Bangladesh mobile providers: 13-19
    ],
    'LK' => [
        'code' => 'LK',
        'key' => '94',
        'local_key' => '0',
        'pattern' => '(?<provider>7[01245678])(?<digits>\d{7})', // Sri Lanka mobile providers
    ],
    'NP' => [
        'code' => 'NP',
        'key' => '977',
        'local_key' => '0',
        'pattern' => '(?<provider>9[6-8])(?<digits>\d{8})', // Nepal mobile providers: 96,97,98
    ],
    'MY' => [
        'code' => 'MY',
        'key' => '60',
        'local_key' => '0',
        'pattern' => '(?<provider>1[0-9])(?<digits>\d{7,8})', // Malaysia mobile providers: 10-19
    ],
    'SG' => [
        'code' => 'SG',
        'key' => '65',
        'local_key' => '',
        'pattern' => '(?<provider>[89])(?<digits>\d{7})', // Singapore mobile providers: 8,9
    ],
    'ID' => [
        'code' => 'ID',
        'key' => '62',
        'local_key' => '0',
        'pattern' => '(?<provider>8[1-9])(?<digits>\d{7,10})', // Indonesia mobile: 8[1-9], NSN 9-12
    ],
    'PH' => [
        'code' => 'PH',
        'key' => '63',
        'local_key' => '0',
        'pattern' => '(?<provider>9[0-9])(?<digits>\d{8})', // Philippines mobile providers: 90-99
    ],
    'CN' => [
        'code' => 'CN',
        'key' => '86',
        'local_key' => '0',
        'pattern' => '(?<provider>1[3-9])(?<digits>\d{9})', // China mobile providers: 13-19
    ],
    'JP' => [
        'code' => 'JP',
        'key' => '81',
        'local_key' => '0',
        'pattern' => '(?<provider>[789]0)(?<digits>\d{8})', // Japan mobile providers: 70,80,90
    ],
    'KR' => [
        'code' => 'KR',
        'key' => '82',
        'local_key' => '0',
        'pattern' => '(?<provider>1[0-9])(?<digits>\d{8})', // South Korea mobile providers: 10-19
    ],
    'AU' => [
        'code' => 'AU',
        'key' => '61',
        'local_key' => '0',
        'pattern' => '(?<provider>4[0-9])(?<digits>\d{7})', // Australia mobile: 4x, NSN 9
    ],
    'NZ' => [
        'code' => 'NZ',
        'key' => '64',
        'local_key' => '0',
        'pattern' => '(?<provider>2[0-9])(?<digits>\d{7,8})', // New Zealand mobile providers: 20-29
    ],
    'CA' => [
        'code' => 'CA',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>\d{3})(?<digits>\d{7})', // Canada: same as US format
    ],
    'MX' => [
        'code' => 'MX',
        'key' => '52',
        'local_key' => '',
        'pattern' => '(?<provider>[2-9]\d)(?<digits>\d{8})', // Mexico mobile: area+number, NSN 10
    ],
    'BR' => [
        'code' => 'BR',
        'key' => '55',
        'local_key' => '0',
        'pattern' => '(?<provider>[1-9][1-9])(?<digits>\d{8,9})', // Brazil mobile format
    ],
    'AR' => [
        'code' => 'AR',
        'key' => '54',
        'local_key' => '0',
        'pattern' => '(?<provider>9)(?<digits>\d{8,10})', // Argentina mobile format
    ],
    'CL' => [
        'code' => 'CL',
        'key' => '56',
        'local_key' => '',
        'pattern' => '(?<provider>9)(?<digits>\d{8})', // Chile mobile format
    ],
    'CO' => [
        'code' => 'CO',
        'key' => '57',
        'local_key' => '0',
        'pattern' => '(?<provider>3[0-9])(?<digits>\d{8})', // Colombia mobile providers: 30-39
    ],
    'PE' => [
        'code' => 'PE',
        'key' => '51',
        'local_key' => '0',
        'pattern' => '(?<provider>9)(?<digits>\d{8})', // Peru mobile: 9, NSN 9
    ],
    'VE' => [
        'code' => 'VE',
        'key' => '58',
        'local_key' => '0',
        'pattern' => '(?<provider>4[0-9])(?<digits>\d{8})', // Venezuela mobile providers: 40-49
    ],
    'ZA' => [
        'code' => 'ZA',
        'key' => '27',
        'local_key' => '0',
        'pattern' => '(?<provider>[6-8][0-9])(?<digits>\d{7})', // South Africa mobile providers: 60-89
    ],
    'NG' => [
        'code' => 'NG',
        'key' => '234',
        'local_key' => '0',
        'pattern' => '(?<provider>[7-9][0-9])(?<digits>\d{8})', // Nigeria mobile providers: 70-99
    ],
    'KE' => [
        'code' => 'KE',
        'key' => '254',
        'local_key' => '0',
        'pattern' => '(?<provider>[17]\d)(?<digits>\d{7})', // Kenya mobile: 1x/7x, NSN 9
    ],
    'GH' => [
        'code' => 'GH',
        'key' => '233',
        'local_key' => '0',
        'pattern' => '(?<provider>[2-5][0-9])(?<digits>\d{7})', // Ghana mobile providers: 20-59
    ],
    'DZ' => [
        'code' => 'DZ',
        'key' => '213',
        'local_key' => '0',
        'pattern' => '(?<provider>[5-7][0-9])(?<digits>\d{7})', // Algeria mobile: 5x/6x/7x, NSN 9
    ],
    'MA' => [
        'code' => 'MA',
        'key' => '212',
        'local_key' => '0',
        'pattern' => '(?<provider>[6-7][0-9])(?<digits>\d{7})', // Morocco mobile: 6x/7x, NSN 9
    ],
    'TN' => [
        'code' => 'TN',
        'key' => '216',
        'local_key' => '',
        'pattern' => '(?<provider>[2-9][0-9])(?<digits>\d{6})', // Tunisia mobile providers: 20-99
    ],
    'ZW' => [
        'code' => 'ZW',
        'key' => '263',
        'local_key' => '0',
        'pattern' => '(?<provider>7[1-9])(?<digits>\d{7})', // Zimbabwe mobile providers: 71-79
    ],
    'ET' => [
        'code' => 'ET',
        'key' => '251',
        'local_key' => '0',
        'pattern' => '(?<provider>[789]\d)(?<digits>\d{7})', // Ethiopia mobile: 7x/8x/9x, NSN 9
    ],
    'CM' => [
        'code' => 'CM',
        'key' => '237',
        'local_key' => '',
        'pattern' => '(?<provider>6[0-9])(?<digits>\d{7})', // Cameroon mobile: 6x, NSN 9
    ],
    'AO' => [
        'code' => 'AO',
        'key' => '244',
        'local_key' => '',
        'pattern' => '(?<provider>9[0-9])(?<digits>\d{7})', // Angola mobile: 9x, NSN 9
    ],
    'ZM' => [
        'code' => 'ZM',
        'key' => '260',
        'local_key' => '0',
        'pattern' => '(?<provider>9[5-7])(?<digits>\d{7})', // Zambia mobile providers: 95-97
    ],
    'AL' => [
        'code' => 'AL',
        'key' => '355',
        'local_key' => '0',
        'pattern' => '(?<provider>6[6-9])(?<digits>\d{7})', // Albania mobile: 66-69, NSN 9
    ],
    'AD' => [
        'code' => 'AD',
        'key' => '376',
        'local_key' => '',
        'pattern' => '(?<provider>[3-6])(?<digits>\d{5})', // Andorra mobile providers: 3-6
    ],
    'AM' => [
        'code' => 'AM',
        'key' => '374',
        'local_key' => '0',
        'pattern' => '(?<provider>[4-9][0-9])(?<digits>\d{6})', // Armenia mobile providers: 40-99
    ],
    'AT' => [
        'code' => 'AT',
        'key' => '43',
        'local_key' => '0',
        'pattern' => '(?<provider>6[4-9])(?<digits>\d{5,11})', // Austria mobile: 64-69, NSN 7-13
    ],
    'AZ' => [
        'code' => 'AZ',
        'key' => '994',
        'local_key' => '0',
        'pattern' => '(?<provider>[4-6][0-9])(?<digits>\d{7})', // Azerbaijan mobile providers: 40-69
    ],
    'BY' => [
        'code' => 'BY',
        'key' => '375',
        'local_key' => '0',
        'pattern' => '(?<provider>[2-4][0-9])(?<digits>\d{7})', // Belarus mobile providers: 20-49
    ],
    'BE' => [
        'code' => 'BE',
        'key' => '32',
        'local_key' => '0',
        'pattern' => '(?<provider>4[5-9])(?<digits>\d{7})', // Belgium mobile: 45-49, NSN 9
    ],
    'BG' => [
        'code' => 'BG',
        'key' => '359',
        'local_key' => '0',
        'pattern' => '(?<provider>[489]\d)(?<digits>\d{5,7})', // Bulgaria mobile: 43x/87-89/98, NSN 8-9
    ],
    'HR' => [
        'code' => 'HR',
        'key' => '385',
        'local_key' => '0',
        'pattern' => '(?<provider>9[1-9])(?<digits>\d{7})', // Croatia mobile providers: 91-99
    ],
    'CY' => [
        'code' => 'CY',
        'key' => '357',
        'local_key' => '',
        'pattern' => '(?<provider>9[0-9])(?<digits>\d{6})', // Cyprus mobile: 9x, NSN 8
    ],
    'CZ' => [
        'code' => 'CZ',
        'key' => '420',
        'local_key' => '',
        'pattern' => '(?<provider>[67]\d)(?<digits>\d{7})', // Czech Republic mobile: 6x/7x, NSN 9
    ],
    'EE' => [
        'code' => 'EE',
        'key' => '372',
        'local_key' => '',
        'pattern' => '(?<provider>[5-8])(?<digits>\d{7})', // Estonia mobile providers: 5-8
    ],
    'CR' => [
        'code' => 'CR',
        'key' => '506',
        'local_key' => '',
        'pattern' => '(?<provider>[6-8])(?<digits>\d{7})', // Costa Rica mobile providers: 6-8
    ],
    'CU' => [
        'code' => 'CU',
        'key' => '53',
        'local_key' => '0',
        'pattern' => '(?<provider>5)(?<digits>\d{7})', // Cuba mobile providers: 5
    ],
    'DO' => [
        'code' => 'DO',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>8[0-4][0-9])(?<digits>\d{7})', // Dominican Republic mobile providers: 800-849
    ],
    'SV' => [
        'code' => 'SV',
        'key' => '503',
        'local_key' => '',
        'pattern' => '(?<provider>[6-7])(?<digits>\d{7})', // El Salvador mobile providers: 6-7
    ],
    'GT' => [
        'code' => 'GT',
        'key' => '502',
        'local_key' => '',
        'pattern' => '(?<provider>[3-5])(?<digits>\d{7})', // Guatemala mobile providers: 3-5
    ],
    'HN' => [
        'code' => 'HN',
        'key' => '504',
        'local_key' => '',
        'pattern' => '(?<provider>[3-9])(?<digits>\d{7})', // Honduras mobile providers: 3-9
    ],
    'PA' => [
        'code' => 'PA',
        'key' => '507',
        'local_key' => '',
        'pattern' => '(?<provider>6)(?<digits>\d{7})', // Panama mobile providers: 6
    ],
    'UY' => [
        'code' => 'UY',
        'key' => '598',
        'local_key' => '0',
        'pattern' => '(?<provider>9)(?<digits>\d{7})', // Uruguay mobile providers: 9
    ],
    'DJ' => [
        'code' => 'DJ',
        'key' => '253',
        'local_key' => '',
        'pattern' => '(?<provider>7[7-8])(?<digits>\d{6})', // Djibouti mobile providers: 77-78
    ],
    'EC' => [
        'code' => 'EC',
        'key' => '593',
        'local_key' => '0',
        'pattern' => '(?<provider>9)(?<digits>\d{8})', // Ecuador mobile providers: 9
    ],
    'HK' => [
        'code' => 'HK',
        'key' => '852',
        'local_key' => '',
        'pattern' => '(?<provider>[5-9])(?<digits>\d{7})', // Hong Kong mobile providers: 5-9
    ],
    'IR' => [
        'code' => 'IR',
        'key' => '98',
        'local_key' => '0',
        'pattern' => '(?<provider>9[0-9])(?<digits>\d{8})', // Iran mobile providers: 90-99
    ],
    'MV' => [
        'code' => 'MV',
        'key' => '960',
        'local_key' => '',
        'pattern' => '(?<provider>[7-9])(?<digits>\d{6})', // Maldives mobile providers: 7-9
    ],
    'MD' => [
        'code' => 'MD',
        'key' => '373',
        'local_key' => '0',
        'pattern' => '(?<provider>[6-7])(?<digits>\d{7})', // Moldova mobile providers: 6-7
    ],
    'MN' => [
        'code' => 'MN',
        'key' => '976',
        'local_key' => '0',
        'pattern' => '(?<provider>[8-9])(?<digits>\d{7})', // Mongolia mobile providers: 8-9
    ],
    'KP' => [
        'code' => 'KP',
        'key' => '850',
        'local_key' => '0',
        'pattern' => '(?<provider>19)(?<digits>\d{8})', // North Korea mobile: 19x, NSN 10
    ],
    'PS' => [
        'code' => 'PS',
        'key' => '970',
        'local_key' => '0',
        'pattern' => '(?<provider>5[6-9])(?<digits>\d{7})', // Palestine mobile providers: 56-59
    ],
    'PY' => [
        'code' => 'PY',
        'key' => '595',
        'local_key' => '0',
        'pattern' => '(?<provider>9[6-9])(?<digits>\d{7})', // Paraguay mobile providers: 96-99
    ],
    'UZ' => [
        'code' => 'UZ',
        'key' => '998',
        'local_key' => '',
        'pattern' => '(?<provider>9[0-9])(?<digits>\d{7})', // Uzbekistan mobile providers: 90-99
    ],
    'RU' => [
        'code' => 'RU',
        'key' => '7',
        'local_key' => '8',
        'pattern' => '(?<provider>9[0-9])(?<digits>\d{8})', // Russia mobile providers: 90-99
    ],
    'SO' => [
        'code' => 'SO',
        'key' => '252',
        'local_key' => '0',
        'pattern' => '(?<provider>[6-9])(?<digits>\d{7})', // Somalia mobile providers: 6-9
    ],
    'SD' => [
        'code' => 'SD',
        'key' => '249',
        'local_key' => '0',
        'pattern' => '(?<provider>9[0-9])(?<digits>\d{7})', // Sudan mobile: 9x, NSN 9
    ],
    'TH' => [
        'code' => 'TH',
        'key' => '66',
        'local_key' => '0',
        'pattern' => '(?<provider>[6-9])(?<digits>\d{8})', // Thailand mobile providers: 6-9
    ],
    'UA' => [
        'code' => 'UA',
        'key' => '380',
        'local_key' => '0',
        'pattern' => '(?<provider>[5-9][0-9])(?<digits>\d{7})', // Ukraine mobile providers: 50-99
    ],
    'YE' => [
        'code' => 'YE',
        'key' => '967',
        'local_key' => '0',
        'pattern' => '(?<provider>7[0-9])(?<digits>\d{7})', // Yemen mobile providers: 70-79
    ],

    // --- World coverage (best-effort mobile regex, sourced from ITU/libphonenumber prefixes) ---

    // Europe & CIS
    'IL' => [
        'code' => 'IL',
        'key' => '972',
        'local_key' => '0',
        'pattern' => '(?<provider>5[0-9])(?<digits>\d{7})', // Israel mobile: 5x
    ],
    'GE' => [
        'code' => 'GE',
        'key' => '995',
        'local_key' => '0',
        'pattern' => '(?<provider>5[0-9]{2})(?<digits>\d{6})', // Georgia mobile: 5xx
    ],
    'KZ' => [
        'code' => 'KZ',
        'key' => '7',
        'local_key' => '8',
        'pattern' => '(?<provider>7[0-9]{2})(?<digits>\d{7})', // Kazakhstan mobile: 70x/74x/77x
    ],
    'KG' => [
        'code' => 'KG',
        'key' => '996',
        'local_key' => '0',
        'pattern' => '(?<provider>[57][0-9]{2})(?<digits>\d{6})', // Kyrgyzstan mobile: 5xx/7xx
    ],
    'TJ' => [
        'code' => 'TJ',
        'key' => '992',
        'local_key' => '',
        'pattern' => '(?<provider>[49]\d)(?<digits>\d{7})', // Tajikistan mobile: 9x/4x
    ],
    'TM' => [
        'code' => 'TM',
        'key' => '993',
        'local_key' => '0',
        'pattern' => '(?<provider>6[0-9])(?<digits>\d{6})', // Turkmenistan mobile: 6x
    ],
    'BA' => [
        'code' => 'BA',
        'key' => '387',
        'local_key' => '0',
        'pattern' => '(?<provider>6[0-6])(?<digits>\d{6})', // Bosnia & Herzegovina mobile: 60-66
    ],
    'MK' => [
        'code' => 'MK',
        'key' => '389',
        'local_key' => '0',
        'pattern' => '(?<provider>7[0-9])(?<digits>\d{6})', // North Macedonia mobile: 7x
    ],
    'XK' => [
        'code' => 'XK',
        'key' => '383',
        'local_key' => '0',
        'pattern' => '(?<provider>4[3-9])(?<digits>\d{6})', // Kosovo mobile: 43-49
    ],
    'LI' => [
        'code' => 'LI',
        'key' => '423',
        'local_key' => '0',
        'pattern' => '(?<provider>[67]\d)(?<digits>\d{5,7})', // Liechtenstein mobile: 6x/7x, NSN 7-9
    ],
    'SM' => [
        'code' => 'SM',
        'key' => '378',
        'local_key' => '',
        'pattern' => '(?<provider>6[0-9])(?<digits>\d{6})', // San Marino mobile: 6x
    ],
    'GI' => [
        'code' => 'GI',
        'key' => '350',
        'local_key' => '',
        'pattern' => '(?<provider>5[6-8])(?<digits>\d{6})', // Gibraltar mobile: 56-58
    ],
    'FO' => [
        'code' => 'FO',
        'key' => '298',
        'local_key' => '',
        'pattern' => '(?<provider>[257]\d)(?<digits>\d{4})', // Faroe Islands mobile: 2x/5x/7x
    ],
    'GL' => [
        'code' => 'GL',
        'key' => '299',
        'local_key' => '',
        'pattern' => '(?<provider>[245]\d)(?<digits>\d{4})', // Greenland mobile: 2x/4x/5x
    ],

    // Asia
    'BT' => [
        'code' => 'BT',
        'key' => '975',
        'local_key' => '',
        'pattern' => '(?<provider>17)(?<digits>\d{6})', // Bhutan mobile: 17
    ],
    'MM' => [
        'code' => 'MM',
        'key' => '95',
        'local_key' => '0',
        'pattern' => '(?<provider>9\d)(?<digits>\d{6,7})', // Myanmar mobile: 9x
    ],
    'KH' => [
        'code' => 'KH',
        'key' => '855',
        'local_key' => '0',
        'pattern' => '(?<provider>[1-9]\d)(?<digits>\d{6,7})', // Cambodia mobile
    ],
    'LA' => [
        'code' => 'LA',
        'key' => '856',
        'local_key' => '0',
        'pattern' => '(?<provider>20)(?<digits>\d{7,8})', // Laos mobile: 20
    ],
    'BN' => [
        'code' => 'BN',
        'key' => '673',
        'local_key' => '',
        'pattern' => '(?<provider>[78]\d)(?<digits>\d{5})', // Brunei mobile: 7x/8x
    ],
    'TL' => [
        'code' => 'TL',
        'key' => '670',
        'local_key' => '',
        'pattern' => '(?<provider>7[2-8])(?<digits>\d{6})', // Timor-Leste mobile: 72-78
    ],
    'TW' => [
        'code' => 'TW',
        'key' => '886',
        'local_key' => '0',
        'pattern' => '(?<provider>9\d)(?<digits>\d{7})', // Taiwan mobile: 9x
    ],
    'MO' => [
        'code' => 'MO',
        'key' => '853',
        'local_key' => '',
        'pattern' => '(?<provider>6\d)(?<digits>\d{6})', // Macau mobile: 6x
    ],

    // Americas
    'BO' => [
        'code' => 'BO',
        'key' => '591',
        'local_key' => '0',
        'pattern' => '(?<provider>[67]\d)(?<digits>\d{6})', // Bolivia mobile: 6x/7x
    ],
    'GY' => [
        'code' => 'GY',
        'key' => '592',
        'local_key' => '',
        'pattern' => '(?<provider>6\d)(?<digits>\d{5})', // Guyana mobile: 6x
    ],
    'SR' => [
        'code' => 'SR',
        'key' => '597',
        'local_key' => '',
        'pattern' => '(?<provider>[678]\d)(?<digits>\d{5})', // Suriname mobile: 6x/7x/8x
    ],
    'BZ' => [
        'code' => 'BZ',
        'key' => '501',
        'local_key' => '',
        'pattern' => '(?<provider>6\d)(?<digits>\d{5})', // Belize mobile: 6x
    ],
    'NI' => [
        'code' => 'NI',
        'key' => '505',
        'local_key' => '',
        'pattern' => '(?<provider>[578]\d)(?<digits>\d{6})', // Nicaragua mobile: 5x/7x/8x
    ],
    'HT' => [
        'code' => 'HT',
        'key' => '509',
        'local_key' => '',
        'pattern' => '(?<provider>[34]\d)(?<digits>\d{6})', // Haiti mobile: 3x/4x
    ],
    'PR' => [
        'code' => 'PR',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>787|939)(?<digits>\d{7})', // Puerto Rico: 787/939
    ],
    'JM' => [
        'code' => 'JM',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>876|658)(?<digits>\d{7})', // Jamaica: 876/658
    ],
    'TT' => [
        'code' => 'TT',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>868)(?<digits>\d{7})', // Trinidad & Tobago: 868
    ],
    'BB' => [
        'code' => 'BB',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>246)(?<digits>\d{7})', // Barbados: 246
    ],
    'BS' => [
        'code' => 'BS',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>242)(?<digits>\d{7})', // Bahamas: 242
    ],

    // Africa
    'SN' => [
        'code' => 'SN',
        'key' => '221',
        'local_key' => '',
        'pattern' => '(?<provider>7[0-9])(?<digits>\d{7})', // Senegal mobile: 7x
    ],
    'MR' => [
        'code' => 'MR',
        'key' => '222',
        'local_key' => '',
        'pattern' => '(?<provider>[234]\d)(?<digits>\d{6})', // Mauritania mobile: 2x/3x/4x
    ],
    'ML' => [
        'code' => 'ML',
        'key' => '223',
        'local_key' => '',
        'pattern' => '(?<provider>[679]\d)(?<digits>\d{6})', // Mali mobile: 6x/7x/9x
    ],
    'GN' => [
        'code' => 'GN',
        'key' => '224',
        'local_key' => '',
        'pattern' => '(?<provider>6[0-9])(?<digits>\d{7})', // Guinea mobile: 6x
    ],
    'CI' => [
        'code' => 'CI',
        'key' => '225',
        'local_key' => '',
        'pattern' => '(?<provider>0[157])(?<digits>\d{8})', // Côte d'Ivoire mobile: 01/05/07, NSN 10
    ],
    'BF' => [
        'code' => 'BF',
        'key' => '226',
        'local_key' => '',
        'pattern' => '(?<provider>[567]\d)(?<digits>\d{6})', // Burkina Faso mobile: 5x/6x/7x
    ],
    'NE' => [
        'code' => 'NE',
        'key' => '227',
        'local_key' => '',
        'pattern' => '(?<provider>[89]\d)(?<digits>\d{6})', // Niger mobile: 8x/9x
    ],
    'TG' => [
        'code' => 'TG',
        'key' => '228',
        'local_key' => '',
        'pattern' => '(?<provider>[79]\d)(?<digits>\d{6})', // Togo mobile: 7x/9x
    ],
    'BJ' => [
        'code' => 'BJ',
        'key' => '229',
        'local_key' => '',
        'pattern' => '(?<provider>01)(?<digits>\d{8})', // Benin mobile: 01 prefix, NSN 10
    ],
    'MU' => [
        'code' => 'MU',
        'key' => '230',
        'local_key' => '',
        'pattern' => '(?<provider>5\d)(?<digits>\d{6})', // Mauritius mobile: 5x
    ],
    'LR' => [
        'code' => 'LR',
        'key' => '231',
        'local_key' => '0',
        'pattern' => '(?<provider>[4-8]\d)(?<digits>\d{6,7})', // Liberia mobile: 4x-8x
    ],
    'SL' => [
        'code' => 'SL',
        'key' => '232',
        'local_key' => '0',
        'pattern' => '(?<provider>[2-9]\d)(?<digits>\d{6})', // Sierra Leone mobile
    ],
    'GM' => [
        'code' => 'GM',
        'key' => '220',
        'local_key' => '',
        'pattern' => '(?<provider>[2-9]\d)(?<digits>\d{5})', // Gambia mobile
    ],
    'TD' => [
        'code' => 'TD',
        'key' => '235',
        'local_key' => '',
        'pattern' => '(?<provider>[69]\d)(?<digits>\d{6})', // Chad mobile: 6x/9x
    ],
    'CF' => [
        'code' => 'CF',
        'key' => '236',
        'local_key' => '',
        'pattern' => '(?<provider>7[0-7])(?<digits>\d{6})', // Central African Republic mobile: 70-77
    ],
    'CG' => [
        'code' => 'CG',
        'key' => '242',
        'local_key' => '',
        'pattern' => '(?<provider>0[1-6])(?<digits>\d{7})', // Congo-Brazzaville mobile: 0[1-6], NSN 9
    ],
    'CD' => [
        'code' => 'CD',
        'key' => '243',
        'local_key' => '0',
        'pattern' => '(?<provider>[89]\d)(?<digits>\d{7})', // DR Congo mobile: 8x/9x
    ],
    'GA' => [
        'code' => 'GA',
        'key' => '241',
        'local_key' => '',
        'pattern' => '(?<provider>0[2-7])(?<digits>\d{6})', // Gabon mobile: 02-07
    ],
    'GQ' => [
        'code' => 'GQ',
        'key' => '240',
        'local_key' => '',
        'pattern' => '(?<provider>222|55\d)(?<digits>\d{6})', // Equatorial Guinea mobile: 222/55x, NSN 9
    ],
    'ST' => [
        'code' => 'ST',
        'key' => '239',
        'local_key' => '',
        'pattern' => '(?<provider>9\d)(?<digits>\d{5})', // São Tomé & Príncipe mobile: 9x
    ],
    'ER' => [
        'code' => 'ER',
        'key' => '291',
        'local_key' => '0',
        'pattern' => '(?<provider>7\d)(?<digits>\d{5})', // Eritrea mobile: 7x
    ],
    'RW' => [
        'code' => 'RW',
        'key' => '250',
        'local_key' => '0',
        'pattern' => '(?<provider>7[2389])(?<digits>\d{7})', // Rwanda mobile: 72/73/78/79
    ],
    'BI' => [
        'code' => 'BI',
        'key' => '257',
        'local_key' => '',
        'pattern' => '(?<provider>[67]\d)(?<digits>\d{6})', // Burundi mobile: 6x/7x
    ],
    'UG' => [
        'code' => 'UG',
        'key' => '256',
        'local_key' => '0',
        'pattern' => '(?<provider>7[0-9])(?<digits>\d{7})', // Uganda mobile: 7x
    ],
    'TZ' => [
        'code' => 'TZ',
        'key' => '255',
        'local_key' => '0',
        'pattern' => '(?<provider>[67]\d)(?<digits>\d{7})', // Tanzania mobile: 6x/7x
    ],
    'MG' => [
        'code' => 'MG',
        'key' => '261',
        'local_key' => '0',
        'pattern' => '(?<provider>3[2-4])(?<digits>\d{7})', // Madagascar mobile: 32-34
    ],
    'MZ' => [
        'code' => 'MZ',
        'key' => '258',
        'local_key' => '',
        'pattern' => '(?<provider>8[2-7])(?<digits>\d{7})', // Mozambique mobile: 82-87
    ],
    'MW' => [
        'code' => 'MW',
        'key' => '265',
        'local_key' => '0',
        'pattern' => '(?<provider>[89]\d)(?<digits>\d{7})', // Malawi mobile: 8x/9x
    ],
    'LS' => [
        'code' => 'LS',
        'key' => '266',
        'local_key' => '',
        'pattern' => '(?<provider>[56]\d)(?<digits>\d{6})', // Lesotho mobile: 5x/6x
    ],
    'BW' => [
        'code' => 'BW',
        'key' => '267',
        'local_key' => '',
        'pattern' => '(?<provider>7[1-8])(?<digits>\d{6})', // Botswana mobile: 71-78
    ],
    'SZ' => [
        'code' => 'SZ',
        'key' => '268',
        'local_key' => '',
        'pattern' => '(?<provider>7[6-8])(?<digits>\d{6})', // Eswatini mobile: 76-78
    ],
    'KM' => [
        'code' => 'KM',
        'key' => '269',
        'local_key' => '',
        'pattern' => '(?<provider>3[234])(?<digits>\d{5})', // Comoros mobile: 32-34
    ],
    'NA' => [
        'code' => 'NA',
        'key' => '264',
        'local_key' => '0',
        'pattern' => '(?<provider>8[0-9])(?<digits>\d{7})', // Namibia mobile: 8x
    ],
    'GW' => [
        'code' => 'GW',
        'key' => '245',
        'local_key' => '',
        'pattern' => '(?<provider>9[5-7])(?<digits>\d{7})', // Guinea-Bissau mobile: 95/96/97, NSN 9
    ],
    'CV' => [
        'code' => 'CV',
        'key' => '238',
        'local_key' => '',
        'pattern' => '(?<provider>[59]\d)(?<digits>\d{5})', // Cape Verde mobile: 5x/9x
    ],
    'SC' => [
        'code' => 'SC',
        'key' => '248',
        'local_key' => '',
        'pattern' => '(?<provider>2[5-8])(?<digits>\d{5})', // Seychelles mobile: 25-28
    ],
    'SS' => [
        'code' => 'SS',
        'key' => '211',
        'local_key' => '0',
        'pattern' => '(?<provider>9[0-9])(?<digits>\d{7})', // South Sudan mobile: 9x
    ],

    // Oceania
    'FJ' => [
        'code' => 'FJ',
        'key' => '679',
        'local_key' => '',
        'pattern' => '(?<provider>[789]\d)(?<digits>\d{5})', // Fiji mobile: 7x/8x/9x
    ],
    'PG' => [
        'code' => 'PG',
        'key' => '675',
        'local_key' => '',
        'pattern' => '(?<provider>[78]\d)(?<digits>\d{6})', // Papua New Guinea mobile: 7x/8x
    ],
    'NC' => [
        'code' => 'NC',
        'key' => '687',
        'local_key' => '',
        'pattern' => '(?<provider>[789]\d)(?<digits>\d{4})', // New Caledonia mobile: 7x/8x/9x
    ],
    'PF' => [
        'code' => 'PF',
        'key' => '689',
        'local_key' => '',
        'pattern' => '(?<provider>8[7-9])(?<digits>\d{6})', // French Polynesia mobile: 87-89, NSN 8
    ],
    'WS' => [
        'code' => 'WS',
        'key' => '685',
        'local_key' => '',
        'pattern' => '(?<provider>7[2-8])(?<digits>\d{5})', // Samoa mobile: 72-78
    ],
    'TO' => [
        'code' => 'TO',
        'key' => '676',
        'local_key' => '',
        'pattern' => '(?<provider>[78]\d)(?<digits>\d{5})', // Tonga mobile: 7x/8x
    ],
    'VU' => [
        'code' => 'VU',
        'key' => '678',
        'local_key' => '',
        'pattern' => '(?<provider>[57]\d)(?<digits>\d{5})', // Vanuatu mobile: 5x/7x
    ],
    'SB' => [
        'code' => 'SB',
        'key' => '677',
        'local_key' => '',
        'pattern' => '(?<provider>[78]\d)(?<digits>\d{5})', // Solomon Islands mobile: 7x/8x
    ],

    // --- NANP +1 territories (area code as provider, 7-digit subscriber number) ---
    'AI' => [
        'code' => 'AI',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>264)(?<digits>\d{7})', // Anguilla: 264
    ],
    'AG' => [
        'code' => 'AG',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>268)(?<digits>\d{7})', // Antigua & Barbuda: 268
    ],
    'KY' => [
        'code' => 'KY',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>345)(?<digits>\d{7})', // Cayman Islands: 345
    ],
    'DM' => [
        'code' => 'DM',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>767)(?<digits>\d{7})', // Dominica: 767
    ],
    'GD' => [
        'code' => 'GD',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>473)(?<digits>\d{7})', // Grenada: 473
    ],
    'MS' => [
        'code' => 'MS',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>664)(?<digits>\d{7})', // Montserrat: 664
    ],
    'KN' => [
        'code' => 'KN',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>869)(?<digits>\d{7})', // Saint Kitts & Nevis: 869
    ],
    'LC' => [
        'code' => 'LC',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>758)(?<digits>\d{7})', // Saint Lucia: 758
    ],
    'VC' => [
        'code' => 'VC',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>784)(?<digits>\d{7})', // Saint Vincent & the Grenadines: 784
    ],
    'TC' => [
        'code' => 'TC',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>649)(?<digits>\d{7})', // Turks & Caicos Islands: 649
    ],
    'VG' => [
        'code' => 'VG',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>284)(?<digits>\d{7})', // British Virgin Islands: 284
    ],
    'VI' => [
        'code' => 'VI',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>340)(?<digits>\d{7})', // US Virgin Islands: 340
    ],
    'AS' => [
        'code' => 'AS',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>684)(?<digits>\d{7})', // American Samoa: 684
    ],
    'GU' => [
        'code' => 'GU',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>671)(?<digits>\d{7})', // Guam: 671
    ],
    'MP' => [
        'code' => 'MP',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>670)(?<digits>\d{7})', // Northern Mariana Islands: 670
    ],
    'BM' => [
        'code' => 'BM',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>441)(?<digits>\d{7})', // Bermuda: 441
    ],
    'SX' => [
        'code' => 'SX',
        'key' => '1',
        'local_key' => '1',
        'pattern' => '(?<provider>721)(?<digits>\d{7})', // Sint Maarten: 721
    ],
];
