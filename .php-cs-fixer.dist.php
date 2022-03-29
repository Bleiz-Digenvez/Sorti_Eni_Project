<?php
$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->exclude('var')
    ->exclude('config')
    ->exclude('build')
    ->notPath('src/Kernel.php')
    ->notPath('public/index.php')
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->in(__DIR__)
;
$config = new PhpCsFixer\Config();
return $config->setRules([
    '@Symfony' => true,
    '@DoctrineAnnotation' => true
])
    ->setFinder($finder)
    ;