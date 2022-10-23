<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/9
 */

namespace App\Models\Blog\Traits;

use Autumn\Validation\Assert;

trait PostTrait
{
    use MediaTrait;
    use CategoriesTrait;

    public function onPersist(): void
    {
        if (!$this->getType()) $this->setType(static::DEFAULT_TYPE);
        if (!$this->getShare()) $this->setShare(static::DEFAULT_SHARE);
        if (!$this->getStatus()) $this->setStatus(static::DEFAULT_STATUS);

        $this->onValidate();

        parent::onPersist();
    }

    public function onUpdate(): void
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
            static::withTrashed();
            static::with([
                'siteId' => $this->getSiteId(),
                'parentId' => $this->getParentId(),
                'type' => $this->getType(),
                'lang' => $this->getLang()
            ]);
            Assert::isEmpty(static::findBy('slug', $slug), 'Slug "' . $slug . '" is in use.');
        }
    }
}