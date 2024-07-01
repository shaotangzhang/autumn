<?php

namespace Autumn\Extensions\Cms\Services;

use Autumn\Extensions\Cms\Models\Page\Page;

class PageService extends AbstractService
{
    public function getBanners(string $name): iterable
    {
        return $this->dummies('carousel', 'home')
            ?: Page::findBy(['slug' => $name, 'type' => 'carousel'])
                ->withoutTrashed();
    }

    public function getHomePage(): ?Page
    {
        return $this->getPage('index');
    }

    public function getPage(string $name): ?Page
    {
        if ($data = $this->dummies('page', $name)) {
            return Page::from($data);
        }

        return Page::findBy(['slug' => $name, 'type' => 'default'])
            ->withoutTrashed()
            ->first();
    }
}