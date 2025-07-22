<?php
if (!defined('_PS_VERSION_')) { 
    exit; 
}

ini_set('memory_limit','256M');

$finder = PhpCsFixer\Finder::create()->in([
    __DIR__.'/sql',
    __DIR__.'/src',
    __DIR__.'/upgrade',
    __DIR__.'/views',
    __DIR__.'/',
]);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'array_indentation' => true,
        'cast_spaces' => [
            'space' => 'single',
        ],
        'combine_consecutive_issets' => true,
        'concat_space' => [
            'spacing' => 'one',
        ],
        'error_suppression' => [
            'mute_deprecation_error' => false,
            'noise_remaining_usages' => false,
            'noise_remaining_usages_exclude' => [],
        ],
        'function_to_constant' => false,
        'method_chaining_indentation' => true,
        'no_alias_functions' => false,
        'no_superfluous_phpdoc_tags' => false,
        'non_printable_character' => [
            'use_escape_sequences_in_strings' => true,
        ],
        'phpdoc_align' => [
            'align' => 'left',
        ],
        'phpdoc_summary' => false,
        'protected_to_private' => false,
        'psr_autoloading' => false,
        'self_accessor' => false,
        'yoda_style' => false,
        'single_line_throw' => false,
        'no_alias_language_construct_call' => false,
        'align_multiline_comment' => [
            'comment_type' => 'all_multiline',
        ],
        'phpdoc_order' => ['order' => ['param', 'throws', 'return']],
        'phpdoc_separation' => false,
        'phpdoc_trim' => false,
        'concat_space' => [
            'spacing' => 'one',
        ],
        'operator_linebreak' => [
            'only_booleans' => true,
            'position' => 'beginning',
        ],
        'no_trailing_whitespace_in_comment' => true,
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__.'/.php-cs-fixer.cache');
