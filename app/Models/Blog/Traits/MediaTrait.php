<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/11
 */

namespace App\Models\Blog\Traits;

use App\Models\Blog\Media;
use Autumn\Database\Attributes\Transient;
use Traversable;

trait MediaTrait
{

    /**
     * @var iterable|Media[]
     */
    #[Transient]
    private iterable $media = [];

    public function media(): iterable
    {
        if ($this->media instanceof Traversable) {
            $this->media = iterator_to_array($this->media);
        }

        return $this->media;
    }

    public function getImages(): iterable
    {
        foreach ($this->media() as $media) {
            if ($media->getType() === 'image') {
                yield $media;
            }
        }
    }

    public function getAudios(): iterable
    {
        foreach ($this->media() as $media) {
            if ($media->getType() === 'audio') {
                yield $media;
            }
        }
    }

    public function getVideos(): iterable
    {
        foreach ($this->media() as $media) {
            if ($media->getType() === 'video') {
                yield $media;
            }
        }
    }

    public function getFiles(): iterable
    {
        foreach ($this->media() as $media) {
            if ($media->getType() === 'file') {
                yield $media;
            }
        }
    }

    public function loadMediaFrom(iterable $source): void
    {
        $this->media = $source;
    }
}