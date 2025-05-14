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
];
