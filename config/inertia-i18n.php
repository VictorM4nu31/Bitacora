<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Auto Share
    |--------------------------------------------------------------------------
    |
    | When enabled, translations are automatically shared with Inertia via
    | Inertia::share() in the ServiceProvider. Set to false if you prefer
    | to use the SharesTranslations trait in your HandleInertiaRequests
    | middleware for more control.
    |
    */
    'auto_share' => true,

    /*
    |--------------------------------------------------------------------------
    | Prop Name
    |--------------------------------------------------------------------------
    |
    | The key under which translation data is shared with Inertia. Your React
    | I18nProvider will look for this prop in page.props.
    |
    */
    'prop_name' => 'i18n',

    /*
    |--------------------------------------------------------------------------
    | Language Path
    |--------------------------------------------------------------------------
    |
    | Path to your JSON translation files. Set to null to use Laravel's
    | default lang_path(). Translation files should be named {locale}.json.
    |
    */
    'lang_path' => null,

    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    |
    | Optional array of allowed locale codes. When set, the HandleLocale
    | middleware will only accept locales in this list. When null, any
    | well-formed locale string is accepted (e.g. "en", "pt_BR", "zh-Hans").
    |
    | Example: ['en', 'es', 'fr', 'pt_BR']
    |
    */
    'supported_locales' => ['es', 'en'],

];
