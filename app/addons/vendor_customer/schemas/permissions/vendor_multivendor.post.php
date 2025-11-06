<?php

defined('BOOTSTRAP') or die('Access denied');

/**
 * @psalm-var array{
 *   controllers: array{
 *     yml: array<string, array{
 *       permissions: array<string, string>
 *     }>
 *   }
 * } $schema
 */

$schema['controllers']['vendor_customer'] = [
    'permissions' =>
        ['GET' => 'manage_vendor_customers', 'POST' => 'manage_vendor_customers'],
];

return $schema;
