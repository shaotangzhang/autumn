<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Traits;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\JsonIgnore;

trait AuthorIdColumnTrait
{
    #[JsonIgnore(ignore: JsonIgnore::IGNORE_ZERO)]
    #[Column(type: Column::FK, name: 'author_id')]
    private int $authorId = 0;

    /**
     * @return int
     */
    public function getAuthorId(): int
    {
        return $this->authorId;
    }

    /**
     * @param int $authorId
     */
    public function setAuthorId(int $authorId): void
    {
        $this->authorId = $authorId;
    }
}