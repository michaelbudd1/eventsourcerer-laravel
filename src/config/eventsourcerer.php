<?php

return [
    'applicationId' => env('EVENTSOURCERER_APPLICATION_ID'),
    'cache'         => [
        'path' => storage_path('eventsourcerer')
    ],
    'host'          => env('EVENTSOURCERER_HOST'),
    'port'          => env('EVENTSOURCERER_PORT'),
    'url'           => env('EVENTSOURCERER_URL'),
    'secure'        => env('EVENTSOURCERER_SECURE', false),
    'localCertificateDirectory' => env('EVENTSOURCERER_LOCAL_CERTIFICATE_DIRECTORY'),
    'verifyPeer'    => env('EVENTSOURCERER_VERIFY_PEER', false),
    'verifyPeerName' => env('EVENTSOURCERER_VERIFY_PEER_NAME', false),
    'allowSelfSigned' => env('EVENTSOURCERER_ALLOW_SELF_SIGNED', false),
    'cafile'        => env('EVENTSOURCERER_CERTIFICATE_AUTHORITY_FILE'),
];
