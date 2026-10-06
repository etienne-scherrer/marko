<?php

declare(strict_types=1);

use Marko\Validation\Contracts\RuleInterface;
use Marko\Validation\Rules\Url;
use Marko\Validation\Validation\RuleParser;

it('implements RuleInterface', function () {
    expect(new Url())->toBeInstanceOf(RuleInterface::class);
});

it('passes for http and https URLs by default', function () {
    $rule = new Url();

    expect($rule->passes('website', 'http://example.com', []))->toBeTrue()
        ->and($rule->passes('website', 'https://example.com/path?q=1#frag', []))->toBeTrue()
        ->and($rule->passes('website', 'https://user:pass@sub.example.com:8443/', []))->toBeTrue();
});

it('rejects dangerous schemes by default', function (string $payload) {
    expect((new Url())->passes('website', $payload, []))->toBeFalse();
})->with([
    'javascript' => 'javascript://%0aalert(1)',
    'file' => 'file:///etc/passwd',
    'gopher' => 'gopher://127.0.0.1:6379/_x',
    'ftp' => 'ftp://example.com/file.txt',
    'data' => 'data://text/plain;base64,SGVsbG8=',
]);

it('rejects an upper-case JAVASCRIPT scheme', function () {
    expect((new Url())->passes('website', 'JAVASCRIPT://%0aalert(1)', []))->toBeFalse();
});

it('compares schemes case-insensitively', function () {
    expect((new Url())->passes('website', 'HTTPS://example.com', []))->toBeTrue()
        ->and((new Url('HTTP'))->passes('website', 'http://example.com', []))->toBeTrue();
});

it('accepts ftp when it is listed', function () {
    $rule = new Url('http', 'https', 'ftp');

    expect($rule->passes('website', 'ftp://example.com/file.txt', []))->toBeTrue()
        ->and($rule->passes('website', 'https://example.com', []))->toBeTrue()
        ->and($rule->passes('website', 'gopher://127.0.0.1:6379/_x', []))->toBeFalse();
});

it('rejects schemes left out of an explicit list', function () {
    expect((new Url('https'))->passes('website', 'http://example.com', []))->toBeFalse();
});

it('rejects a URL with no host even when its scheme is allowed', function () {
    expect((new Url('file'))->passes('path', 'file:///etc/passwd', []))->toBeFalse();
});

it('rejects values that are not URLs', function () {
    $rule = new Url();

    expect($rule->passes('website', 'not a url', []))->toBeFalse()
        ->and($rule->passes('website', 'example.com', []))->toBeFalse()
        ->and($rule->passes('website', 123, []))->toBeFalse()
        ->and($rule->passes('website', ['http://example.com'], []))->toBeFalse();
});

it('passes for null and empty values', function () {
    $rule = new Url();

    expect($rule->passes('website', null, []))->toBeTrue()
        ->and($rule->passes('website', '', []))->toBeTrue();
});

it('names the allowed schemes in its message', function () {
    expect((new Url())->message('website', 'javascript:alert(1)'))
        ->toBe('The website field must be a valid URL using one of these schemes: http, https.');
});

it('throws when the scheme list is given but empty', function () {
    new Url(' ', '');
})->throws(InvalidArgumentException::class, 'url:http,https,ftp');

it('parses url with a scheme list from a rule string', function () {
    $rules = (new RuleParser())->parse('url:http,https,ftp');

    expect($rules)->toHaveCount(1)
        ->and($rules[0])->toBeInstanceOf(Url::class)
        ->and($rules[0]->passes('website', 'ftp://example.com', []))->toBeTrue()
        ->and($rules[0]->passes('website', 'gopher://example.com', []))->toBeFalse();
});

it('parses a bare url rule string as http and https only', function () {
    $rules = (new RuleParser())->parse('url');

    expect($rules[0]->passes('website', 'https://example.com', []))->toBeTrue()
        ->and($rules[0]->passes('website', 'javascript://%0aalert(1)', []))->toBeFalse();
});
