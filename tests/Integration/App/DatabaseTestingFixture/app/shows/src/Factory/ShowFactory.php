<?php

declare(strict_types=1);

namespace Marko\Integration\DatabaseTesting\Factory;

use Marko\Database\Testing\EntityFactory;
use Marko\Integration\DatabaseTesting\Entity\Show;
use Marko\Integration\DatabaseTesting\Repository\ShowRepository;

/**
 * @extends EntityFactory<Show>
 */
class ShowFactory extends EntityFactory
{
    protected const string REPOSITORY = ShowRepository::class;

    private int $number = 0;

    protected function definition(): Show
    {
        $number = ++$this->number;

        $show = new Show();
        $show->slug = "show-$number";
        $show->title = "Show $number";

        return $show;
    }
}
