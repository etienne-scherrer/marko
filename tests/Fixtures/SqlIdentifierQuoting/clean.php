<?php

declare(strict_types=1);

// Nothing here quotes an SQL identifier by hand, so the detector must report nothing.

// SQL quoted through the connection
$connection->execute('DELETE FROM ' . $connection->quoteIdentifier($table) . ' WHERE id = ?', [1]);
$connection->query("SELECT * FROM {$connection->quoteIdentifier($table)} WHERE id = ?", [1]);

// A comment mentioning SELECT * FROM `jobs` is not a string
/* DELETE FROM "jobs" */

// Quote characters in statements that build no SQL
$hasBacktick = str_contains($expression, '`');
$phrase = implode(' OR ', array_map(static fn (string $term): string => '"' . $term . '"', $terms));
$message = "Run `marko db:migrate` to create the \"jobs\" table";
$css = '.bar { user-select: none; } .table td { content: "x"; }';
$sentence = 'select the rows from the "jobs" table and update them';
$context = "Validating SELECT column alias '$alias'";
$suggestion = 'Use an alias such as "my_alias"';
