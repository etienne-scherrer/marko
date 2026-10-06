<?php

declare(strict_types=1);

namespace Marko\Integration\DatabaseTesting\Entity;

use Marko\Database\Attributes\Column;
use Marko\Database\Attributes\Table;
use Marko\Database\Entity\Entity;

#[Table('shows')]
class Show extends Entity
{
    #[Column(primaryKey: true, autoIncrement: true)]
    public ?int $id = null;

    #[Column(length: 255, unique: true)]
    public string $slug = '';

    #[Column(length: 255)]
    public string $title = '';

    #[Column(length: 20)]
    public string $status = 'draft';
}
