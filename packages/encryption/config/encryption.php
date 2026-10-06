<?php

declare(strict_types=1);

use Marko\Config\Env;

return [
    'key' => Env::string('ENCRYPTION_KEY', ''),
    'cipher' => Env::string('ENCRYPTION_CIPHER', 'aes-256-gcm'),
    // Retired keys, tried for decryption only, so a key can be rotated without
    // invalidating stored ciphertext. Comma-separated base64 keys.
    'previous_keys' => Env::list('ENCRYPTION_PREVIOUS_KEYS', []),
    // When decrypting with associated data fails, retry with empty associated data
    // so values written before AAD was introduced still decrypt. Turn this off once
    // existing ciphertext has been re-encrypted. Default changes to false in a future release.
    'aad_fallback' => Env::bool('ENCRYPTION_AAD_FALLBACK', true),
];
