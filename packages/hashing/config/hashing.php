<?php

declare(strict_types=1);

use Marko\Config\Env;

return [
    'default' => Env::string('HASH_DRIVER', 'bcrypt'),

    'hashers' => [
        'bcrypt' => [
            'cost' => Env::int('BCRYPT_COST', 12, min: 4, max: 31),
        ],

        'argon2id' => [
            'memory' => Env::int('ARGON2_MEMORY', 65536, min: 8),
            'time' => Env::int('ARGON2_TIME', 4, min: 1),
            'threads' => Env::int('ARGON2_THREADS', 1, min: 1),
        ],
    ],
];
