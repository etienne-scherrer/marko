<?php

declare(strict_types=1);

namespace Marko\Mail\Log\Config;

use Marko\Config\ConfigRepositoryInterface;
use Marko\Config\ConfigValue;
use Marko\Config\Exceptions\ConfigException;
use Marko\Config\Exceptions\ConfigNotFoundException;
use Marko\Core\Environment\AppEnvironment;

readonly class MailLogConfig
{
    public function __construct(
        private ConfigRepositoryInterface $config,
        private AppEnvironment $appEnvironment,
    ) {}

    /**
     * Whether message bodies and raw content are written to the log.
     *
     * An explicit mail-log.include_body wins; when it is null, bodies are logged only in development.
     *
     * @throws ConfigException|ConfigNotFoundException
     */
    public function includeBody(): bool
    {
        $value = $this->config->get('mail-log.include_body');

        if ($value === null) {
            return $this->appEnvironment->isDevelopment();
        }

        return ConfigValue::toBool('mail-log.include_body', $value);
    }

    /**
     * @throws ConfigException|ConfigNotFoundException
     */
    public function allowProduction(): bool
    {
        return $this->config->getBool('mail-log.allow_production');
    }
}
