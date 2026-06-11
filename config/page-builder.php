<?php

return [
    // Middleware applied to editor routes: /themes, /editor, /preview/cancel.
    // Defaults to ['auth'] so the builder is not exposed to anonymous users.
    // Tighten with a Gate, e.g. ['auth', 'can:edit-pages'].
    'editor_middleware' => ['auth'],

    // Middleware applied to the public page render route: /page/view.
    // Defaults to none (pages are publicly viewable). Add ['auth'] for gated pages.
    'render_middleware' => [],

    // When true (default), inline-style CSS values that don't match the
    // expected color/url/keyword shapes are dropped (with a Log::warning).
    // Set to false if you have legacy property values that fail validation
    // and you trust your editors.
    'strict_css_validation' => true,

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

    // Theme Encryption Configuration
    'encryption' => [
        // Enable/disable theme encryption
        'enabled' => env('PAGE_BUILDER_ENCRYPTION_ENABLED', false),

        // Default encryption key (should be set in .env for production)
        'key' => env('PAGE_BUILDER_ENCRYPTION_KEY', ''),

        // File extension for encrypted themes
        'file_extension' => env('PAGE_BUILDER_ENCRYPTION_FILE_EXTENSION', '.tet'),

        // Whether to require password for encrypted themes
        'require_password' => env('PAGE_BUILDER_ENCRYPTION_REQUIRE_PASSWORD', true),
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

    /*
    |--------------------------------------------------------------------------
    | Theme Settings
    |--------------------------------------------------------------------------
    |
    | Host-defined fields shown in the Theme Manager's "Settings" modal and
    | saved into each theme's `settings` JSON column. The package renders them
    | generically and stays unaware of what they mean. Fields left empty are
    | NOT stored, so consumers fall back to their own defaults.
    |
    | Each field:
    |   'key'         => dot path into the theme settings JSON (e.g. 'slider.width')
    |   'label'       => translated label
    |   'type'        => 'number' | 'text'   (default 'text')
    |   'placeholder' => hint shown when empty (e.g. the app's current default)
    |   'rule'        => optional Laravel rule applied only when filled
    |   'group'       => optional section heading to group fields under
    |
    */
    'theme_settings' => [
        // [ 'key' => 'example.width', 'label' => 'Width', 'type' => 'number', 'placeholder' => '1920' ],
    ],
];

