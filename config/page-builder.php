<?php

return [
    // Middleware to secure the page builder routes (e.g., ['auth', 'can:edit-pages'])
    'middleware' => [],

    // Localization Configuration
    'localization' => [
        // UI locales affect the builder interface (buttons, labels, etc.)
        'ui_locales' => [
            'en' => 'English',
            // 'ar' => 'العربية',
            // 'fr' => 'Français',
        ],

        // Content locales are used for multilingual content in the builder
        'content_locales' => [
            'en' => 'English',
            // 'ar' => 'العربية',
            // 'fr' => 'Français',
        ],

        // Default locale for new content
        'default_content_locale' => 'en',
    ],

    'blocks' => [
        // MainMenu::class,
    ],
    'pages' => [
        //  'home',
        // 'header' => ['is_block' => true],
    ],

    /*
    |--------------------------------------------------------------------------
    | Page Builder Variables
    |--------------------------------------------------------------------------
    |
    | You can define dynamic variables that can be used in your page builder.
    | Variables can be string values or callables that return dynamic values.
    | These variables will be available in text blocks and templates.
    |
    */
    'variables' => [
        // Simple string variables
        // 'company_name' => 'Acme Inc',
        // 'support_email' => 'support@example.com',
    ],
];
