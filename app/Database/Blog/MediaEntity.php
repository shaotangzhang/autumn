<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/8
 */

namespace App\Database\Blog;

use Autumn\Database\AbstractEntity;
use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\Index;

class MediaEntity extends AbstractEntity
{
    public const ENTITY_NAME = 'blog_media';

    #[Index('i_slug')]
    #[Index(index: true, unique: true)]
    #[Column(type: 'bigint')]
    private int $siteId = 0;

    private string $title = ''; // display name

    #[Index('i_slug')]
    #[Index(index: true, unique: true)]
    #[Column(type: 'char', size: 32, collation: 'ascii_general_ci')]
    private string $slug = '';  // digest

    #[Column(collation: 'ascii_general_ci')]
    private string $link = '';  // storage path

    #[Index(index: true)]
    #[Column(type: 'char', size: 32, collation: 'ascii_general_ci')]
    private string $source = '';  // digest

    #[Index('i_slug')]
    #[Index(index: true, unique: true)]
    #[Column(type: 'char', size: 10, collation: 'ascii_general_ci')]
    private string $type = '';  // image, audio, video, file

    #[Column(size: 128, collation: 'ascii_general_ci')]
    private string $mimeType = '';

    #[Index]
    private int $size = 0;      // file size

    #[Index]
    private int $width = 0;     // image/video width

    #[Index]
    private int $height = 0;    // image/video height

    #[Index]
    private int $duration = 0;

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
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink(string $link): void
    {
        $this->link = $link;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     */
    public function setMimeType(string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @param int $width
     */
    public function setWidth(int $width): void
    {
        $this->width = $width;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @param int $height
     */
    public function setHeight(int $height): void
    {
        $this->height = $height;
    }

    /**
     * @return int
     */
    public function getDuration(): int
    {
        return $this->duration;
    }

    /**
     * @param int $duration
     */
    public function setDuration(int $duration): void
    {
        $this->duration = $duration;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource(string $source): void
    {
        $this->source = $source;
    }  // audio/video duration in mill-seconds


}