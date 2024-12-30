<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages.
    |
    */

    'accepted' => ':attribute мақұлдау міндетті',
    'active_url' => ':attribute Жарамсыз URL',
    'after' => ':attribute Кейінірек болу керек:date。',
    'after_or_equal' => ':attribute Тең болу керек :date ,н/е кешірек',
    'alpha' => ':attribute Тек латын әріппен',
    'alpha_dash' => ':attribute Тек латын әріп,сан,қисық сызық',
    'alpha_num' => ':attribute Тек латын әріппен сан',
    'array' => ':attribute Міндетті түрде сан тізбегі болу керек',
    'before' => ':attribute Ерте болу керек :date',
    'before_or_equal' => ':attribute Тең болу керек :date н/е ертерек',
    'between' => [
        'numeric' => ':attribute міндетті түрде :min - :max аралығында болу керек',
        'file' => ':attribute міндетті түрде :min - :max KB аралығында болу керек',
        'string' => ':attribute міндетті түрде :min - :max әріп аралығында болу керек',
        'array' => ':attribute Тек ғана :min - :max бөлім',
    ],
    'boolean' => ':attribute Boolean мәніне тең болу керек',
    'confirmed' => ':attribute екі реткі енгізу бірдей емес',
    'date' => ':attribute жарамсыз мерзім',
    'date_format' => ':attribute форматы :format болу керек',
    'different' => ':attribute және :other ұқсас болмау керек',
    'digits' => ':attribute тек ғана :digits орынды сан',
    'digits_between' => ':attribute тек ғана :min ж/е :max орынды сан',
    'dimensions' => ':attribute сурет размері қате',
    'distinct' => ':attribute бұрыннан бар',
    'email' => ':attribute жарамсыз Email',
    'exists' => ':attribute табылмады',
    'file' => ':attribute тек ғана файл',
    'filled' => ':attribute бос қалтыруға болмайды',
    'gt' => [
        'numeric' => ':attribute міндетті түрде :value үлкен болу керек',
        'file' => ':attribute міндетті түрде :value KB үлкен болу керек',
        'string' => ':attribute міндетті түрде :value әріптен көр болу керек',
        'array' => ':attribute міндетті түрде :value элементтен көп болу керек',
    ],
    'gte' => [
        'numeric' => ':attribute Міндетті түрде үлкен н/е тең :value ',
        'file' => ':attribute міндетті түрде үлкен н/е тең :value KB。',
        'string' => ':attribute міндетті түрде көп н/е тең :value әріп',
        'array' => ':attribute міндетті түрде көп н/е тең :value элемент',
    ],
    'image' => ':attribute тек ғана сурет',
    'in' => 'таңдалған сипат :attribute ережесіз',
    'in_array' => ':attribute  :other ішінде жоқ',
    'integer' => ':attribute тек ғана бүтін сан',
    'ip' => ':attribute тек ғана жарамды IP адресс',
    'ipv4' => ':attribute тек ғана жарамды IPv4 адресс',
    'ipv6' => ':attribute тек ғана жарамды IPv6 адресс',
    'json' => ':attribute дүрыс JSON форматта болу керек',
    'lt' => [
        'numeric' => ':attribute кішкене болу керек :value',
        'file' => ':attribute кшкене болу керек :value KB',
        'string' => ':attribute аз болу керек :value әріптен',
        'array' => ':attribute аз болу керек :value элементтен',
    ],
    'lte' => [
        'numeric' => ':attribute кіші н/е тең :value ',
        'file' => ':attribute кіші н/е тең :value KB ',
        'string' => ':attribute кіші н/е тең :value әріп ',
        'array' => ':attribute кіші н/е тең :value элемент ',
    ],
    'max' => [
        'numeric' => ':attribute үлкен болмау керек :max ',
        'file' => ':attribute үлкен болмау керек :max KB ',
        'string' => ':attribute үлкен болмау керек :max әріп ',
        'array' => ':attribute ең көбінде :max бөлім ',
    ],
    'mimes' => ':attribute тек ғана бір :values түрдегі файл',
    'mimetypes' => ':attribute тек ғана бір :values түрдегі файл',
    'min' => [
        'numeric' => ':attribute үлкен н/е тең :min',
        'file' => ':attribute кіші болмау керек :min KB',
        'string' => ':attribute кемінде :min әріп',
        'array' => ':attribute кемінде :min бөлім',
    ],
    'not_in' => 'таңдалған сипат :attribute ережесіз',
    'not_regex' => ':attribute форматы қате',
    'numeric' => ':attribute сан болуы керек',
    'present' => ':attribute бар болу керек',
    'regex' => ':attribute форматы қате',
    'required' => ':attribute бос қалмау керек',
    'required_if' => ' :other  :value болған кезде :attribute бос қалмау керек',
    'required_unless' => ' :other  :value болмаған кезде :attribute бос қалмау керек',
    'required_with' => ':values бар болған кезде :attribute бос қалмау керек',
    'required_with_all' => ' :values бар болған кезде :attribute бос қалмау керек',
    'required_without' => ' :values жоқ болған кезде :attribute бос қалмау керек',
    'required_without_all' => ' :values бәрі жоқ болған кезде :attribute бос қалмау керек',
    'same' => ':attribute ж/е :other ұқсас болу керек',
    'size' => [
        'numeric' => ':attribute үлкендігі :size болу керек',
        'file' => ':attribute размері :size KB болу керек',
        'string' => ':attribute  :size әріп болу керек',
        'array' => ':attribute  :size бөлім болу керек',
    ],
    'string' => ':attribute әріп тіркесі болу керек',
    'timezone' => ':attribute заңды уақыт белдеуі болу керек',
    'unique' => ':attribute бұрыннан бар',
    'uploaded' => ':attribute жолдау сәтсіз',
    'url' => ':attribute форматы қате',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention 'attribute.rule' to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
        'mobile' => ':attribute форматы қате',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of 'email'. This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'name' => 'Аты',
        'username' => 'Логин',
        'email' => 'Email',
        'first_name' => 'Аты',
        'last_name' => 'Тегі',
        'password' => 'Пароль',
        'password_confirmation' => 'Паролді растау',
        'city' => 'Қала',
        'country' => 'Мемлекет',
        'address' => 'Мекен-жай',
        'phone' => 'Үй тел',
        'mobile' => 'Тел',
        'captcha' => 'Тексеріс коды',
        'age' => 'Жасы',
        'sex' => 'Жынысы',
        'gender' => 'Жыныс',
        'day' => 'Күн',
        'month' => 'Ай',
        'year' => 'Жыл',
        'hour' => 'Сағ',
        'minute' => 'Мин',
        'second' => 'Сек',
        'title' => 'Тақырып',
        'content' => 'Мазмұн',
        'description' => 'Сипаты',
        'excerpt' => 'Түйіндеме',
        'date' => 'Мерзім',
        'time' => 'Уақыт',
        'available' => 'Жарамды',
        'size' => 'Размер',
    ],
];
