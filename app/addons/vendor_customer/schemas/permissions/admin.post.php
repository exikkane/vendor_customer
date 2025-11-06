<?php

defined('BOOTSTRAP') or die('Access denied');

/**
 * @var array<string, array> $schema
 */

$schema['vendor_customer'] = [
    'modes' => [
        'manage_vendor_customers' => [
            'permissions' => 'manage_vendor_customers'
        ],
    ],
    'permissions' => ['GET' => 'manage_vendor_customers', 'POST' => 'manage_vendor_customers']
];
return $schema;
