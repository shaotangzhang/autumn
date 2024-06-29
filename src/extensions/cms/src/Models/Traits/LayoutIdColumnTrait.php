<?php
namespace Autumn\Extensions\Cms\Models\Traits;

use Autumn\Database\Attributes\Column;

trait LayoutIdColumnTrait
{
    #[Column(type: Column::FK, name: 'layout_id', unsigned: true)]
    private int $layoutId = 0;

    /**
     * @return int
     */
    public function getLayoutId(): int
    {
        return $this->layoutId;
    }

    /**
     * @param int $layoutId
     */
    public function setLayoutId(int $layoutId): void
    {
        $this->layoutId = $layoutId;
    }
}