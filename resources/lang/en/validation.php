<?php

$_LANG = array(
    'accepted' => ':attribute must be accepted.',
    'active_url' => ':attribute is not a valid url.',
    'after' => ':attribute must be later than :date.',
    'after_or_equal' => ':attribute must be equal to :date or later.',
    'alpha' => 'Attribute can only be composed of letters.',
    'alpha_dash' => 'Attribute can only consist of letters, Numbers, and slashes.',
    'alpha_num' => 'Attribute can only be composed of letters and Numbers.',
    'array' => 'Attribute must be an array.',
    'before' => ':attribute must be earlier than :date.',
    'before_or_equal' => ':attribute must be equal to :date or earlier.',
    'between' =>
        array(
            'numeric' => ':attribute must be between :min - : Max.',
            'file' => ':attribute must be between :min - : Max KB.',
            'string' => 'Attribute must be between :min - : Max characters.',
            'array' => ':attribute must only have :min - : Max units.',
        ),
    'boolean' => ':attribute must be Boolean.',
    'confirmed' => ': inconsistent input of attribute.',
    'date' => ':attribute is not a valid date.',
    'date_format' => 'The format of :attribute must be :format.',
    'different' => 'Attribute and other must be different.',
    'digits' => ':attribute must be the digits for :digits.',
    'digits_between' => ':attribute must be a number between :min and: Max bits.',
    'dimensions' => ':attribute image size is incorrect.',
    'distinct' => ':attribute already exists.',
    'email' => ':attribute is not a legitimate mailbox.',
    'exists' => ':attribute does not exist.',
    'file' => 'Attribute must be a file.',
    'filled' => ':attribute cannot be empty.',
    'gt' =>
        array(
            'numeric' => ':attribute must be greater than :value.',
            'file' => ':attribute must be greater than :value KB.',
            'string' => 'Attribute must be more than :value.',
            'array' => 'Attribute must be more than :value.',
        ),
    'gte' =>
        array(
            'numeric' => ':attribute must be greater than or equal to :value.',
            'file' => ':attribute must be greater than or equal to :value KB.',
            'string' => 'Attribute must be more than or equal to :value.',
            'array' => 'Attribute must be more than or equal to :value element.',
        ),
    'image' => 'Attribute must be an image.',
    'in' => 'Selected attribute :attribute is illegal.',
    'in_array' => 'Attribute is not in :other.',
    'integer' => ':attribute must be an integer.',
    'ip' => ':attribute must be a valid IP address.',
    'ipv4' => ':attribute must be a valid IPv4 address.',
    'ipv6' => ':attribute must be a valid IPv6 address.',
    'json' => ':attribute must be in the correct JSON format.',
    'lt' =>
        array(
            'numeric' => ':attribute must be less than :value.',
            'file' => ':attribute must be less than :value KB.',
            'string' => 'Attribute must be less than :value.',
            'array' => 'Attribute must be less than :value element.',
        ),
    'lte' =>
        array(
            'numeric' => 'Attribute must be less than or equal to :value.',
            'file' => 'Attribute must be less than or equal to :value KB.',
            'string' => 'Attribute must be less than or equal to :value.',
            'array' => 'Attribute must be less than or equal to :value element.',
        ),
    'max' =>
        array(
            'numeric' => ':attribute cannot be greater than: Max.',
            'file' => ':attribute cannot be greater than: Max KB.',
            'string' => 'Attribute cannot be greater than: Max characters.',
            'array' => 'Attribute: Max units at most.',
        ),
    'mimes' => ':attribute must be a file of type :values.',
    'mimetypes' => ':attribute must be a file of type :values.',
    'min' =>
        array(
            'numeric' => ':attribute must be greater than or equal to :min.',
            'file' => ':attribute size cannot be smaller than :min KB.',
            'string' => ':attribute is at least :min characters.',
            'array' => 'Attribute :min units at least.',
        ),
    'not_in' => 'Selected attribute :attribute is illegal.',
    'not_regex' => ': format error of attribute.',
    'numeric' => ': an attribute must be a number.',
    'present' => 'Attribute must exist.',
    'regex' => ':attribute format is incorrect.',
    'required' => ':attribute cannot be empty.',
    'required_if' => 'Attribute cannot be empty when :other is :value.',
    'required_unless' => 'Attribute cannot be empty when :other is not :value.',
    'required_with' => 'When :values exists :attribute cannot be empty.',
    'required_with_all' => 'When :values exists :attribute cannot be empty.',
    'required_without' => 'Attribute cannot be empty when :values does not exist.',
    'required_without_all' => 'Attribute cannot be empty when :values do not exist.',
    'same' => 'Attribute and other must be the same.',
    'size' =>
        array(
            'numeric' => 'Attribute size must be :size.',
            'file' => ':attribute size must be :size KB.',
            'string' => ':attribute must be :size characters.',
            'array' => 'Attribute must be :size units.',
        ),
    'string' => ': an attribute must be a string.',
    'timezone' => ':attribute must be a valid time zone value.',
    'unique' => ':attribute already exists.',
    'uploaded' => ':attribute upload failed.',
    'url' => ':attribute format is incorrect.',
    'custom' =>
        array(
            'attribute-name' =>
                array(
                    'rule-name' => 'custom-message',
                ),
            'mobile' => ':attribute format is incorrect.',
        ),
    'attributes' =>
        array(
            'name' => 'The name of the',
            'username' => 'The user name',
            'email' => 'email',
            'first_name' => 'The name',
            'last_name' => 'The surname',
            'password' => 'password',
            'password_confirmation' => 'Confirm password',
            'city' => 'city',
            'country' => 'countries',
            'address' => 'address',
            'phone' => 'The phone',
            'mobile' => 'Mobile phone',
            'captcha' => 'Picture verification code',
            'age' => 'age',
            'sex' => 'gender',
            'gender' => 'gender',
            'day' => 'day',
            'month' => 'month',
            'year' => 'years',
            'hour' => 'when',
            'minute' => 'points',
            'second' => 'seconds',
            'title' => 'The title',
            'content' => 'content',
            'description' => 'describe',
            'excerpt' => 'Abstract',
            'date' => 'The date of',
            'time' => 'time',
            'available' => 'The available',
            'size' => 'The size of the',
        ),
);


return $_LANG;
