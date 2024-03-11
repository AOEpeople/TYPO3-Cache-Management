<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\Strict\StrictParamFixer;
use Symplify\CodingStandard\Fixer\LineLength\DocBlockLineLengthFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/../Classes',
        __DIR__ . '/../Tests',
        __DIR__ . '/ecs.php',
    ])
    ->withSets([
        SetList::COMMON,
        SetList::CLEAN_CODE,
        SetList::PSR_12,
        SetList::SYMPLIFY,
    ])
    ->withConfiguredRule(
        LineLengthFixer::class,
        [
            LineLengthFixer::LINE_LENGTH => 140,
            LineLengthFixer::INLINE_SHORT_LINES => false,
        ]
    )
    ->withSkip([
        // Default Skips
        NotOperatorWithSuccessorSpaceFixer::class => null,
        DocBlockLineLengthFixer::class => null,
        ArrayListItemNewlineFixer::class => null,
        ArrayOpenerAndCloserNewlineFixer::class => null,

        // @todo strict php
        DeclareStrictTypesFixer::class => null,
        StrictComparisonFixer::class => null,
        StrictParamFixer::class => null,
    ]);
