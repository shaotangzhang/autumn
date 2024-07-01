<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Traits;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\Index;
use Autumn\Database\Attributes\JsonIgnore;

trait OptionIdColumnTrait
{
    #[JsonIgnore(ignore: true)]
    #[Index(Index::DEFAULT_UNIQUE_NAME, Index::UNIQUE)]
    #[Column(type: Column::FK, name: 'option_id', unsigned: true)]
    private int $OptionId = 0;

    /**
     * @return int
     */
    public function getOptionId(): int
    {
        return $this->OptionId;
    }

    /**
     * @param int $OptionId
     */
    public function setOptionId(int $OptionId): void
    {
        $this->OptionId = $OptionId;
    }
}