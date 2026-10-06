<?php

declare(strict_types=1);

/**
 * Intentional exceptions to ConfigReadersTest (#403).
 *
 * Every entry needs a reason. An entry that is no longer needed (the key gained a
 * literal reader, the accessor gained a caller, or either was deleted) fails the
 * test, so this list only ever shrinks to what is still true.
 *
 * - keys: '{config file}.{top-level key}' => reason the key has no literal reader
 * - accessors: '{Config class}::{method}' => reason nothing in packages/ calls it
 */
return [
    'keys' => [
        'discovery.enabled' => 'Mirrors the boot gate for introspection; boot reads the env through DiscoveryEnvironment (marko/core cannot depend on marko/config).',
        'discovery.environment' => 'Mirrors the boot gate for introspection; boot reads the env through DiscoveryEnvironment (marko/core cannot depend on marko/config).',
        'discovery.cache_path' => 'Mirrors the boot gate for introspection; boot reads the env through DiscoveryEnvironment (marko/core cannot depend on marko/config).',
        'http-guzzle.timeout' => 'Read through the composed key "http-guzzle.$name" in GuzzleHttpClient::timeoutFromConfig().',
        'http-guzzle.connect_timeout' => 'Read through the composed key "http-guzzle.$name" in GuzzleHttpClient::timeoutFromConfig().',
        'layout.components' => 'Pre-existing: no reader yet; components are discovered from #[Component] attributes. Not security-relevant.',
        'layout.layouts' => 'Pre-existing: no reader yet; layouts are discovered from attributes. Not security-relevant.',
        'mail.sendmail' => 'Pre-existing: no sendmail driver ships yet. Not security-relevant.',
    ],
    'accessors' => [
        'Marko\AdminPanel\Config\AdminPanelConfig::getPageTitle' => 'Pre-existing: documented accessor for app templates; the panel itself does not read it yet. Not security-relevant.',
        'Marko\AdminPanel\Config\AdminPanelConfig::getItemsPerPage' => 'Pre-existing: documented accessor for app code; the panel itself does not paginate yet. Not security-relevant.',
        'Marko\Authentication\Config\AuthConfig::providers' => 'Whole-section accessor for app code; the framework reads providersOrNull().',
        'Marko\Authentication\Config\AuthConfig::passwordConfig' => 'Whole-section accessor for app code; the framework reads bcryptCost().',
        'Marko\Authentication\Config\AuthConfig::rememberConfig' => 'Whole-section accessor for app code; the framework reads the remember* accessors.',
        'Marko\Hashing\Config\HashConfig::hasHasher' => 'Public accessor for app code; HasherFactory reads the per-hasher accessors.',
        'Marko\Hashing\Config\HashConfig::getHasherConfig' => 'Public accessor for app code; HasherFactory reads the per-hasher accessors.',
        'Marko\Mail\Config\MailConfig::ensureConfigExists' => 'Public guard for mail drivers in other packages and apps to call before reading mail config.',
    ],
];
