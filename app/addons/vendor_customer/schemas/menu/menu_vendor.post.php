<?php

defined('BOOTSTRAP') or die('Access denied');

$schema['central']['settings']['items']['vendor_customers_import'] = [
    'attrs'    => [
        'class' => 'is-addon',
    ],
    'href'              => 'exim.import&section=vendor_customers',
    'position'          => 700,
];

$schema['central']['marketing']['items']['vendor_customers'] = [
    'attrs' => [
        'class' => 'is-addon'
    ],
    'href'     => 'profiles.manage&user_type=N',
    'position' => 100,
];

return $schema;
