<?php

declare(strict_types=1);

use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Migration\Migration;

/*
 * A hand-made partial index no entity declares. config/database.php lists it
 * under migrations.ignore_indexes, so db:migrate must never drop it (#170).
 */
return new class () extends Migration
{
    public function up(
        ConnectionInterface $connection,
    ): void {
        $this->execute($connection, "CREATE INDEX authors_named_idx ON authors (name) WHERE name <> ''");
    }

    public function down(
        ConnectionInterface $connection,
    ): void {
        $this->execute($connection, 'DROP INDEX IF EXISTS authors_named_idx');
    }
};
