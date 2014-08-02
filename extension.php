<?php

return [

    'main' => 'Mii\\Faq\\MiiFaqExtension',

    'autoload' => [

        'Mii\\Faq\\' => 'src'

    ],

    'resources' => [

        'export' => [
            'view' => 'views',
            'asset' => 'assets'
        ]

    ],

    'controllers' => 'src/Controller/*Controller.php',

    // 'settings' => [

    //     'system' => 'miiFaq/admin/settings.razr'

    // ],

    'menu' => [

        'miiFaq' => [
            'label'  => 'miiFaq',
            'icon'   => 'extension://blog/extension.svg',
            'url'    => '@miiFaq/admin/question',
            'active' => '@miiFaq/admin/question*',
            'access' => 'miiFaq: manage questions || miiFaq: manage answers'
        ],
        // 'miiFaq: question list' => [
        //     'label'  => 'Faq',
        //     'parent' => 'miiFaq',
        //     'url'    => '@miiFaq/admin/question',
        //     'active' => '@miiFaq/admin/question*',
        //     'access' => 'miiFaq: manage questions'
        // ],
        // 'miiFaq: answer list' => [
        //     'label'  => 'Answers',
        //     'parent' => 'miiFaq',
        //     'url'    => '@miiFaq/admin/answer',
        //     'active' => '@miiFaq/admin/answer*',
        //     'access' => 'miiFaq: manage answers'
        // ],

    ],

    'permissions' => [

        'miiFaq: manage settings' => [
            'title' => 'Manage settings'
        ],
        'miiFaq: manage questions' => [
            'title' => 'Manage questions'
        ],
        'miiFaq: manage answers' => [
            'title' => 'Manage answers'
        ]

    ],


    'defaults' => [

        'index.questions_per_page'  => 20,

    ]


];
