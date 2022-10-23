<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/11
 */

namespace App\Models\Blog;

use App\Database\Blog\PostEntity;
use App\Models\Blog\Traits\PostTrait;
use Autumn\Validation\Assert;

class Category extends PostEntity
{
    use PostTrait;

    public const DEFAULT_TYPE = 'category';
    public const STATUS_ACTIVE = 'active';
    public const DEFAULT_STATUS = self::STATUS_ACTIVE;

    protected function onPersist(): void
    {
        if (!$this->getType()) $this->setType(static::DEFAULT_TYPE);
        if (!$this->getShare()) $this->setShare(static::DEFAULT_SHARE);
        if (!$this->getStatus()) $this->setStatus(static::DEFAULT_STATUS);

        $this->onValidate();

        parent::onPersist();
    }

    protected function onUpdate(): void
    {
        $this->onValidate();

        parent::onUpdate();
    }

    public function validateTitle(): void
    {
        Assert::isNotEmpty($this->getTitle(), 'Title is required.');
    }

    public function validateParentId(): void
    {
        if ($parentId = $this->getParentId()) {
            static::withoutTrashed();
            static::with(['siteId' => $this->getSiteId()]);
            static::with(['type' => $this->getType()]);
            Assert::isNotEmpty(static::findById($parentId), 'Parent ID is invalid.');
        }
    }

    public function validateSlug(): void
    {
        if ($slug = $this->getSlug()) {
            Assert::isEmpty(static::withTrashed()->find([
                'siteId' => $this->getSiteId(),
                'parentId' => $this->getParentId(),
                'type' => $this->getType(),
                'lang' => $this->getLang(),
                'slug' => $slug
            ]), 'Slug "' . $slug . '" is in use.');
        }
    }

    protected function onValidate(): void
    {
        $this->validateTitle();     // required check
        $this->validateSlug();      // unique check
        $this->validateParentId();  // valid check
    }
}