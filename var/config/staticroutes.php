<?php 

return [
    1 => [
        "name" => "news",
        "pattern" => "/(.*)_n([\\d]+)/",
        "reverse" => "%prefix/%text_n%id",
        "module" => "",
        "controller" => "news",
        "action" => "detail",
        "variables" => "text,id",
        "defaults" => "",
        "siteId" => "0",
        "priority" => "1",
        "creationDate" => "0",
        "modificationDate" => "0",
        "id" => "1"
    ],
    2 => [
        "name" => "blog",
        "pattern" => "/(.*)_b([\\d]+)/",
        "reverse" => "%prefix/%text_b%id",
        "module" => "",
        "controller" => "blog",
        "action" => "detail",
        "variables" => "text,id",
        "defaults" => "",
        "siteId" => "0",
        "priority" => "1",
        "creationDate" => "1388391249",
        "modificationDate" => "1388391368",
        "id" => "2"
    ],
    4 => [
        "id" => 4,
        "name" => "club55_login",
        "pattern" => "@^/(de|en)/secure/login\$@",
        "reverse" => "/%_locale/secure/login",
        "module" => "AppBundle",
        "controller" => "Secure",
        "action" => "login",
        "variables" => "_locale",
        "defaults" => "_locale=de",
        "siteId" => [

        ],
        "priority" => 0,
        "legacy" => FALSE,
        "creationDate" => 1490874634,
        "modificationDate" => 1490874753
    ],
    5 => [
        "id" => 5,
        "name" => "club55_logout",
        "pattern" => "@^/(de|en)/secure/logout\$@",
        "reverse" => "/%_locale/secure/logout",
        "module" => "AppBundle",
        "controller" => "Secure",
        "action" => "logout",
        "variables" => "_locale",
        "defaults" => "_locale=en",
        "siteId" => [

        ],
        "priority" => 0,
        "legacy" => FALSE,
        "creationDate" => 1490874774,
        "modificationDate" => 1490874774
    ],
    6 => [
        "id" => 6,
        "name" => "blogpost",
        "pattern" => "/\\/article\\/([0-9]+)\\/(.*)/",
        "reverse" => "/article/%id/%title",
        "module" => NULL,
        "controller" => "default",
        "action" => "blogarticle",
        "variables" => "id,title",
        "defaults" => NULL,
        "siteId" => [

        ],
        "priority" => 1,
        "legacy" => FALSE,
        "creationDate" => 1520280708,
        "modificationDate" => 1520605649
    ],
    7 => [
        "id" => 7,
        "name" => "kegelabend",
        "pattern" => "/\\/kegelabend\\/([0-9]+)/",
        "reverse" => "/kegelabend/%id",
        "module" => NULL,
        "controller" => "default",
        "action" => "oneEvening",
        "variables" => "id",
        "defaults" => NULL,
        "siteId" => [

        ],
        "priority" => 1,
        "legacy" => FALSE,
        "creationDate" => 1520281207,
        "modificationDate" => 1520282881
    ]
];
