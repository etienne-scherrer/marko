<?php

declare(strict_types=1);

namespace Marko\Tests\Support\SqlIdentifierQuoting;

use PhpToken;

/**
 * Finds SQL that quotes identifiers with a hard-coded delimiter instead of ConnectionInterface::quoteIdentifier().
 *
 * Each driver owns its quoting rule (#323/#331): a backtick for MySQL and MariaDB, a double quote for PostgreSQL.
 * Package code that writes `jobs` or "jobs" itself keeps a second copy of that rule, which a new driver or a
 * decorator such as ReadWriteConnection won't follow. This tokenizes each file and splits it into statements on
 * `;`. A statement builds SQL when one of its string literals starts with an uppercase statement keyword (SELECT,
 * INSERT, UPDATE, DELETE, TRUNCATE, CREATE, ALTER, DROP) or contains FROM, JOIN, INTO or WHERE in upper case, so
 * lowercase prose and CSS never count. Every string literal in such a statement that holds a backtick or a double
 * quote is reported, including the parts of an interpolated string or heredoc. Comments are not strings, so they
 * are never flagged.
 */
readonly class HardCodedIdentifierQuoteDetector
{
    /**
     * A literal that opens an SQL statement, or names a table or condition the way only SQL does. Prose such as
     * "Validating SELECT column alias" neither starts with a statement keyword nor uses FROM, JOIN, INTO or WHERE.
     */
    private const string SQL_PATTERN = '/^\s*(?:SELECT|INSERT|UPDATE|DELETE|TRUNCATE|CREATE|ALTER|DROP)\b|\b(?:FROM|JOIN|INTO|WHERE)\b/';

    /**
     * @param list<string> $files
     * @return list<array{file: string, line: int, literal: string}>
     */
    public function scan(
        array $files,
    ): array {
        $violations = [];

        foreach ($files as $file) {
            array_push($violations, ...$this->scanFile($file));
        }

        return $violations;
    }

    /**
     * @return list<array{file: string, line: int, literal: string}>
     */
    public function scanFile(
        string $file,
    ): array {
        $violations = [];

        foreach ($this->statements(PhpToken::tokenize((string) file_get_contents($file))) as $literals) {
            $buildsSql = array_any(
                $literals,
                fn (PhpToken $literal): bool => preg_match(self::SQL_PATTERN, $this->content($literal)) === 1,
            );

            if (!$buildsSql) {
                continue;
            }

            foreach ($literals as $literal) {
                $content = $this->content($literal);

                if (str_contains($content, '`') || str_contains($content, '"')) {
                    $violations[] = ['file' => $file, 'line' => $literal->line, 'literal' => $literal->text];
                }
            }
        }

        return $violations;
    }

    /**
     * The string literal tokens of each `;`-terminated statement.
     *
     * @param list<PhpToken> $tokens
     * @return list<list<PhpToken>>
     */
    private function statements(
        array $tokens,
    ): array {
        $statements = [];
        $literals = [];

        foreach ($tokens as $token) {
            if ($token->text === ';') {
                $statements[] = $literals;
                $literals = [];

                continue;
            }

            if ($token->is([T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE])) {
                $literals[] = $token;
            }
        }

        $statements[] = $literals;

        return array_values(array_filter($statements, fn (array $literals): bool => $literals !== []));
    }

    /**
     * A literal's content without its own delimiters: 'x' and "x" lose their outer quotes, the parts of an
     * interpolated string or heredoc are content already.
     */
    private function content(
        PhpToken $token,
    ): string {
        return $token->is(T_CONSTANT_ENCAPSED_STRING) ? substr($token->text, 1, -1) : $token->text;
    }
}
