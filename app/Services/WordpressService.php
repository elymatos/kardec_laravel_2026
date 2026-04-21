<?php

namespace App\Services;

use RickWest\WordPress\Facades\WordPress;

class WordpressService
{
    private static array $pages = [
        'index' => [
            'pt' => 620,
            'fr' => 638,
        ],
        'presentation' => [
            'pt' => 53,
            'fr' => 250,
        ],
        'collections' => [
            'pt' => 59,
            'fr' => 271,
        ],
        'editorial' => [
            'pt' => 55,
            'fr' => 263,
        ],
        'team' => [
            'pt' => 57,
            'fr' => 276,
        ],
        'terms' => [
            'pt' => 185,
            'fr' => 724,
        ],
        'bibliography' => [
            'pt' => 69,
            'fr' => 304,
        ],
    ];

    public static function getPage(string $idPage, string $lang = 'pt')
    {
        //        debug(WordPress::pages());
        //        debug($idPage);
        //        debug(self::$pages[$idPage][$lang]);
        $page = WordPress::pages()->find(self::$pages[$idPage][$lang]);

        //        debug(WordPress::pages());
        //        debug($page);
        return $page['content']['rendered'];
    }
}
