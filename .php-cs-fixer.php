<?php

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PSR1' => true,
    '@PSR2' => true,
    '@PSR12' => true,
    '@PhpCsFixer' => true,
    '@Symfony' => true,
    'protected_to_private' => false,
    'yoda_style' => false,
    'no_trailing_comma_in_list_call' => false,
    'global_namespace_import' => [
        'import_classes' => true,
        'import_constants' => null,
        'import_functions' => null,
    ],
    'binary_operator_spaces' => [
        'default' => 'align',
    ],
    'list_syntax' => [
        'syntax' => 'short',
    ],
    'array_syntax' => ['syntax' => 'short'],
    'self_static_accessor' => true,
    'mb_str_functions' => true,
])->setUsingCache(false);
