<?php
namespace Autumn\Extensions\Auth\Models\Traits;

use Autumn\Database\Attributes\Column;

trait AuthorityIdColumnTrait
{
    #[Column(type: Column::FK, name: 'authority_id', unsigned: true)]
    private int $authorityId = 0;

    /**
     * @return int
     */
    public function getAuthorityId(): int
    {
        return $this->authorityId;
    }

    /**
     * @param int $authorityId
     */
    public function setAuthorityId(int $authorityId): void
    {
        $this->authorityId = $authorityId;
    }
}