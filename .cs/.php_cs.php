<?php
$finder = PhpCsFixer\Finder::create()->in('bundle');

return (new PhpCsFixer\Config())->setRules(
    [
        '@PSR2' => true,
        '@PSR12' => true,
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'increment_style' => ['style' => 'pre'],
        'ordered_imports' => true,
        'phpdoc_order' => true,
        'linebreak_after_opening_tag' => true,
        'phpdoc_no_package' => false,
        'phpdoc_inline_tag_normalizer' => false,
        'cast_spaces' => false,
        'no_superfluous_phpdoc_tags' => true,
        'single_line_throw' => false,
        'binary_operator_spaces' => ['default' => 'single_space', 'operators' => ['=' => 'align_single_space_minimal']],
    ]
)->setFinder($finder);
