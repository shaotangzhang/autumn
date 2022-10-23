<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/8
 */

namespace App\Models\Trading\Products;

use App\Database\Trading\ProductEntity;
use App\Models\Blog\Traits\PostTrait;
use Autumn\Database\Attributes\Transient;
use Autumn\Validation\Assert;

class Product extends ProductEntity
{
    use PostTrait;

    #[Transient]
    private ?ProductEntity $parent = null;

    /**
     * @return ProductEntity|null
     */
    public function getParent(): ?ProductEntity
    {
        return $this->parent;
    }

    /**
     * @param ProductEntity|array|null $parent
     */
    public function setParent(ProductEntity|array|null $parent): void
    {
        if (is_array($parent)) {
            $parent = ProductEntity::from($parent);
        }

        $this->parent = $parent;
        $this->setParentId($parent?->getId() ?: 0);
    }

    public function validateSKU(): void
    {
        if ($sku = $this->getSku()) {
            static::withTrashed();
            static::with(['siteId' => $this->getSiteId()]);

            if ($item = static::findBy('sku', $sku)) {
                if ($this->isNew()) {
                    Assert::fail('SKU "' . $sku . '" is in use.');
                } else {
                    Assert::assert($item->getId() === $this->getId(), 'SKU "' . $sku . '" is in use.');
                }
            }
        }
    }

    public function onValidate(): void
    {
        $this->validateTitle();     // required check
        $this->validateSKU();       // unique check
        $this->validateSlug();      // unique check
        $this->validateParentId();  // valid check
    }
}