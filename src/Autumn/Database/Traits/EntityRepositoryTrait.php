<?php
/**
 * Autumn PHP Framework
 *
 * Date:        24/06/2024
 */

namespace Autumn\Database\Traits;

use Autumn\Exceptions\NotFoundException;

trait EntityRepositoryTrait
{
    use RepositoryTrait;
    use RelatedRepositoryTrait;

    public function first(): ?static
    {
        return ($this->resultSet ?? $this->query())->fetch();
    }

    public function firstOrFail(string $error = null): static
    {
        return $this->first() ?? throw NotFoundException::of($error);
    }

}