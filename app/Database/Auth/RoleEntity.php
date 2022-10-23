<?php
/**
 * Enflares PHP Framework
 *
 * Date:        2022/9/30
 */

namespace App\Database\Auth;

use Autumn\Database\AbstractEntity;
use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\Index;

class RoleEntity extends AbstractEntity
{
    public const ENTITY_NAME = 'auth_roles';

    #[Column(type: 'bigint')]
    #[Index(unique: true)]
    private int $siteId = 0;

    #[Column(type: 'bigint')]
    #[Index(unique: true)]
    private int $parentId = 0;

    #[Column(collation: 'ascii_general_ci')]
    #[Index(unique: true)]
    private string $name = '';

    #[Column(type: 'text')]
    private ?string $description = null;

    /**
     * @return int
     */
    public function getSiteId(): int
    {
        return $this->siteId;
    }

    /**
     * @param int $siteId
     */
    public function setSiteId(int $siteId): void
    {
        $this->siteId = $siteId;
    }

    /**
     * @return int
     */
    public function getParentId(): int
    {
        return $this->parentId;
    }

    /**
     * @param int $parentId
     */
    public function setParentId(int $parentId): void
    {
        $this->parentId = $parentId;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }


}