<?php
return [
    // secret key (store in .env as JWT_SECRET)
    'secret'    => env('JWT_SECRET', 'change-me'),
    // token time-to-live, in minutes
    'ttl'       => env('JWT_TTL', 60),
    // signing algorithm
    'algo'      => env('JWT_ALGO', 'HS256'),
];
