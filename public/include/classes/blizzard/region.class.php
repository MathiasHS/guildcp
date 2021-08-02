<?php namespace GuildCP\Blizzard;

/**
 * Class Region - representing blizzard regions
 */
class Region
{

    /**
     * @var array $List of available regions as of 19.03.2019
     */
    public static $list = [
        'eu' => [
            'en_gb',
            'en_es',
            'fr_fr',
            'ru_ru',
            'de_de',
            'pt_pt',
            'it_it',
        ],
        'us' => [
            'en_us',
            'es_mx',
            'pt_br'
        ],
        'apac' => [
            'ko_kr',
            'zh_tw'
        ],
        'cn' => [
            'zh_cn'
        ]
    ];
}
