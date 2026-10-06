<?php

declare(strict_types=1);

use Marko\Config\Env;

return [
    'driver' => Env::string('LOG_DRIVER', 'file'),
    'path' => Env::string('LOG_PATH', 'storage/logs'),
    'level' => Env::string('LOG_LEVEL', 'debug'),
    'channel' => Env::string('LOG_CHANNEL', 'app'),
    'format' => '[{datetime}] {channel}.{level}: {message} {context}',
    'date_format' => 'Y-m-d H:i:s',
    'max_files' => Env::int('LOG_MAX_FILES', 30, min: 0),
    'max_file_size' => Env::int('LOG_MAX_FILE_SIZE', 10 * 1024 * 1024, min: 0),
    'escape_newlines' => true,
];
