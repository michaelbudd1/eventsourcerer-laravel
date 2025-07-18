<?php

return [
    'applicationId' => env('EVENT_SOURCERER_APPLICATION_ID'),
    'cache'         => storage_path('eventsourcerer'),
    'host'          => env('EVENT_SOURCERER_SERVER_HOST'),
    'port'          => env('EVENT_SOURCERER_SERVER_PORT'),
    'url'           => env('EVENT_SOURCERER_SERVER_URL'),
];
