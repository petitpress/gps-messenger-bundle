<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('vendor')
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PHP74Migration' => true,
        'align_multiline_comment' => [
            'comment_type' => 'phpdocs_only',
        ],
        'array_indentation' => true,
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'braces' => [
            'allow_single_line_closure' => true,
        ],
        'declare_strict_types' => true,
        'native_function_invocation' => [
            'include' => [
                '@compiler_optimized',
            ],
        ],
        'no_extra_blank_lines' => [
            'tokens' => [
                'break',
                'continue',
                'curly_brace_block',
                'extra',
                'parenthesis_brace_block',
                'return',
                'square_brace_block',
                'throw',
                'use',
            ],
        ],
        'no_null_property_initialization' => true,
        'no_superfluous_phpdoc_tags' => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_imports' => [
            'imports_order' => [
                'class',
                'function',
                'const',
            ],
            'sort_algorithm' => 'alpha',
        ],
        'phpdoc_align' => [
            'align' => 'left'
        ],
        'phpdoc_order' => true,
        'psr_autoloading' => true,
        'no_unused_imports' => true,
        'semicolon_after_instruction' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'ternary_to_null_coalescing' => true,
    ])
    ->setFinder($finder)
;
