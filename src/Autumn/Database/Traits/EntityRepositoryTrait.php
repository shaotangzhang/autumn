<?php

namespace Autumn\Database\Traits;

use Autumn\Database\Db;
use Autumn\Database\DbConnection;
use Autumn\Exceptions\NotFoundException;

trait EntityRepositoryTrait
{
    use EntityRelationshipTrait;
    use RepositoryTrait {
        of as protected __of__;
    }

    /**
     * Creates a repository instance for the current model class with optional context and database connection.
     *
     * @param array|null $context
     * @param DbConnection|null $connection
     * @return static
     */
    public static function repository(array $context = null, DbConnection $connection = null): static
    {
        $instance = new static;

        $instance->modelClass = static::class;
        $instance->connection = $connection;
        $instance->readOnly = $connection === null;

        $instance->reset();
        if ($context) {
            $instance->__prepare_from_context__($context);
        }

        return $instance;
    }

    public function first(): ?static
    {
        return $this->__affective_result__()?->fetch();
    }

    public function firstOrFail(string $messageOnNotFound = null): static
    {
        return $this->first() ?? throw NotFoundException::of($messageOnNotFound);
    }
}