<?php

declare(strict_types=1);

use Aoe\Cachemgm\Controller\BackendModuleController;

return [
    'cachemgm' => [
        'parent' => 'tools',
        'access' => 'admin',
        'labels' => 'LLL:EXT:' . 'cachemgm' . '/Resources/Private/BackendModule/Language/locallang.xlf',
        'iconIdentifier' => 'module-cachemgm-backend-module',
        'extensionName' => 'Cachemgm',
        'controllerActions' => [
            BackendModuleController::class => [
                'index',
                'detail',
                'flush',
            ]
        ],
        'path' => 'module/tools/cachemgm',
    ],
];
