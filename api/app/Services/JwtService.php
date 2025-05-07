<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Carbon\Carbon;

class JwtService
{
    protected $secret;
    protected $algo;
    protected $ttl;

    public function __construct()
    {
        $this->secret = config('jwt.secret');
        $this->algo   = config('jwt.algo');
        $this->ttl    = config('jwt.ttl');
    }

    /** Generate a JWT for a given payload (e.g. user) */
    public function generateToken(array $payload): string
    {
        $now   = Carbon::now()->timestamp;
        $exp   = Carbon::now()->addMinutes($this->ttl)->timestamp;

        $token = array_merge($payload, [
            'iat' => $now,
            'exp' => $exp,
        ]);

        return JWT::encode($token, $this->secret, $this->algo);
    }

    /** Validate and decode a token; throws on failure */
    public function validateToken(string $token): object
    {
        return JWT::decode($token, new Key($this->secret, $this->algo));
    }
}
