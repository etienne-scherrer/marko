<?php

declare(strict_types=1);

namespace Marko\Integration\Fixture\Entity;

use Marko\Database\Attributes\Column;
use Marko\Database\Attributes\Table;
use Marko\Database\Entity\Entity;

#[Table('books')]
class Book extends Entity
{
    #[Column(primaryKey: true, autoIncrement: true)]
    public ?int $id = null;

    #[Column]
    public int $authorId = 0;

    #[Column(length: 255)]
    public string $title = '';
}
