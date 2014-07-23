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
            'url'    => '@faq/question',
            'active' => '@faq/question*',
            'access' => 'faq: manage questions || faq: manage answers'
        ],
        'faq: question list' => [
            'label'  => 'Faq',
            'parent' => 'faq',
            'url'    => '@faq/question',
            'active' => '@faq/question*',
            'access' => 'faq: manage questions'
        ],
        'faq: answer list' => [
            'label'  => 'Answers',
            'parent' => 'faq',
            'url'    => '@faq/answer',
            'active' => '@faq/answer*',
            'access' => 'faq: manage answers'
        ],

    ],

    'permissions' => [

        'faq: manage settings' => [
            'title' => 'Manage settings'
        ],
        'blog: manage content' => [
            'title' => 'Manage questions'
        ],
        'blog: manage answers' => [
            'title' => 'Manage answers'
        ]

    ],


];
