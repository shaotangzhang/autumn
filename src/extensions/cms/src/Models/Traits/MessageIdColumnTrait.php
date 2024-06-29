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

trait MessageIdColumnTrait
{
    #[Column(type: Column::FK, name: 'message_id', unsigned: true)]
    private int $messageId = 0;

    /**
     * @return int
     */
    public function getMessageId(): int
    {
        return $this->messageId;
    }

    /**
     * @param int $messageId
     */
    public function setMessageId(int $messageId): void
    {
        $this->messageId = $messageId;
    }
}