<?php

return [
    'all' => [
        'design/head/demonotice'                            => '1',
        'dev/restrict/allow_ips'                            => '',
        'dev/log/active'                                    => '1',
        // ...
    ],
    'dev' => [
        'web/url/use_store'                                 => '1',
        'web/unsecure/base_url'                             => 'http://dev.example.com/',
        'web/secure/base_url'                               => 'https://dev.example.com/',
        'web/cookie/cookie_domain'                          => 'dev.example.com',
        // ...
    ],
    'staging' => [
        'web/secure/base_url'   => [
//            'websiteCode'                                   => 'value',
//            'websiteCode:storeViewCode'                     => 'value',
            'default'                                       => 'https://staging.example.com/',
            'websiteOne'                                    => 'https://staging-one.example.com/',
            'websiteTwo'                                    => 'https://staging-two.example.com/',
            'websiteThree:en'                               => 'https://staging-three-en.example.com/',
            'websiteThree:ca'                               => 'https://staging-three-ca.example.com/',
        ]
        // ...
    ]
];
