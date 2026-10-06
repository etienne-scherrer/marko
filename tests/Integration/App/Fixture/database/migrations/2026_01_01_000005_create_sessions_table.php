<?php

declare(strict_types=1);

use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Migration\Migration;

// The sessions table as the marko/session-database docs gave it before the package shipped its entity (#337). It
// matches the DatabaseSession entity, so db:migrate reports no drift for it.
return new class () extends Migration
{
    public function up(
        ConnectionInterface $connection,
    ): void {
        $this->execute($connection, <<<'SQL'
            CREATE TABLE sessions (
                id VARCHAR(128) PRIMARY KEY,
                payload TEXT NOT NULL,
                last_activity INT NOT NULL
            )
            SQL);
    }

    public function down(
        ConnectionInterface $connection,
    ): void {
        $this->execute($connection, 'DROP TABLE IF EXISTS sessions');
    }
};
