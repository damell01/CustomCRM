<?php

/*
|--------------------------------------------------------------------------
| White-label translation overrides
|--------------------------------------------------------------------------
| This file overrides specific keys from the Krayin admin language file.
| Only keys defined here are overridden — everything else falls through
| to the original package translations.
|
| To find a key to override, look in:
|   packages/Webkul/Admin/src/Resources/lang/en/app.php
|--------------------------------------------------------------------------
*/

return [

    'components' => [
        'layouts' => [
            'powered-by' => [
                // Blank — removes the "Powered by Krayin / Webkul" line entirely.
                'description' => '',
            ],
        ],
    ],

];
