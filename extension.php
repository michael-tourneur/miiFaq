<?php

return [

    'main' => 'Mii\\Faq\\MiiFaqExtension',

    'autoload' => [

        'Mii\\Faq\\' => 'src'

    ],

    'resources' => [

        'export' => [
            'view' => 'views'
        ]

    ],

    'controllers' => 'src/Controller/*Controller.php',

    'settings' => [

        'system' => 'miiFaq/admin/settings.razr'

    ],

    'menu' => [

        'faq' => [
            'label'  => 'Faq',
            'icon'   => 'extension://blog/extension.svg',
            'url'    => '@miiFaq/question',
            'active' => '@miiFaq/question*',
            'access' => 'faq: manage questions || faq: manage answers'
        ],
        'faq: question list' => [
            'label'  => 'Faq',
            'parent' => 'faq',
            'url'    => '@miiFaq/question',
            'active' => '@miiFaq/question*',
            'access' => 'faq: manage questions'
        ],
        'faq: answer list' => [
            'label'  => 'Answers',
            'parent' => 'faq',
            'url'    => '@miiFaq/answer',
            'active' => '@miiFaq/answer*',
            'access' => 'faq: manage answers'
        ],

    ],

    'permissions' => [

        'faq: manage settings' => [
            'title' => 'Manage settings'
        ],
        'faq: manage questions' => [
            'title' => 'Manage questions'
        ],
        'faq: manage answers' => [
            'title' => 'Manage answers'
        ]

    ],


];
