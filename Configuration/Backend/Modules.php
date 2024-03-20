<?php

declare(strict_types=1);

return [
    'cachemgm' => [
        'parent' => 'tools',
        'access' => 'admin',
        'labels' => 'LLL:EXT:' . 'cachemgm' . '/Resources/Private/BackendModule/Language/locallang.xlf',
        'icon' => 'EXT:' . 'cachemgm' . '/Resources/Public/Icons/Extension.svg',
        'extensionName' => 'Cachemgm',
        'controllerActions' => [
            \Aoe\Cachemgm\Controller\BackendModuleController::class => [
                'index', 'detail', 'flush'
            ]
        ],
    ],
];
