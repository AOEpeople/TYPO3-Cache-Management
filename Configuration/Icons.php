<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    // Icon identifier
    'module-cachemgm-backend-module' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:cachemgm/Resources/Public/Icons/Extension.svg',
    ],
];
