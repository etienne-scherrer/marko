<?php

declare(strict_types=1);

// Each statement below quotes an identifier with a hard-coded delimiter; the test asserts the reported lines.
$table = 'jobs';
$connection->execute('TRUNCATE TABLE `' . $table . '`');
$connection->execute('DELETE FROM "' . $table . '" WHERE id = ?', [1]);
$connection->query("SELECT * FROM \"$table\" WHERE id = ?", [1]);
$connection->query(<<<SQL
    SELECT `key` FROM settings
    SQL);
$sql = 'TRUNCATE TABLE ' . implode(', ', array_map(static fn (string $name): string => '"' . $name . '"', $tables));
