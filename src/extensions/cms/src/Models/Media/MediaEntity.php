<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Media;

use Autumn\App;
use Autumn\Attributes\Transient;
use Autumn\Database\Attributes\Column;
use Autumn\Extensions\Cms\Models\Page\PageEntity;
use Autumn\Extensions\Cms\Models\Traits\AuthorIdColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\SiteIdColumnTrait;

class MediaEntity extends PageEntity
{
    use SiteIdColumnTrait;
    use AuthorIdColumnTrait;

    public const ENTITY_NAME = 'cms_media';

    public const TYPE_IMAGE = 'image';
    public const TYPE_AUDIO = 'audio';
    public const TYPE_VIDEO = 'video';
    public const TYPE_FILE = 'file';

    #[Transient]
    private ?string $template = null;

    #[Column(type: Column::TYPE_TEXT, name: 'source')]
    private ?string $source = null;

    #[Column(type: Column::TYPE_STRING, name: 'mime', size: 255, charset: Column::CHARSET_ASCII)]
    private ?string $mime = null;

    #[Column(type: Column::TYPE_CHAR, name: 'ext', size: 10, charset: Column::CHARSET_ASCII)]
    private ?string $ext = null;

    #[Column(type: Column::TYPE_CHAR, name: 'digest', size: 32, charset: Column::CHARSET_ASCII)]
    private string $digest = '';

    #[Column(type: Column::TYPE_BIG_INT, name: 'size', unsigned: true)]
    private int $size = 0;

    #[Column(type: Column::TYPE_INT, name: 'width', unsigned: true)]
    private int $width = 0;

    #[Column(type: Column::TYPE_INT, name: 'height', unsigned: true)]
    private int $height = 0;

    #[Column(type: Column::TYPE_DECIMAL, name: 'duration', unsigned: true)]
    private float $duration = 0.00;

    public static function defaultUrlPrefix(): ?string
    {
        return env('CMS_URL_MEDIA_PREFIX', '/media/');
    }

    public static function defaultUrlSuffix(): ?string
    {
        return env('CMS_URL_MEDIA_SUFFIX');
    }

    public static function defaultMediaRoot(): ?string
    {
        return env('CMS_MEDIA_UPLOAD_ROOT', 'public');
    }

    public static function defaultUploadPath(): ?string
    {
        return App::map(static::defaultMediaRoot(), 'upload');
    }


    public function isImage(): bool
    {
        return $this->getType() === static::TYPE_IMAGE;
    }

    public function isAudio(): bool
    {
        return $this->getType() === static::TYPE_AUDIO;
    }

    public function isVideo(): bool
    {
        return $this->getType() === static::TYPE_VIDEO;
    }

    public function isFile(): bool
    {
        return $this->getType() === static::TYPE_FILE;
    }

    public function getPath(): string
    {
        return $this->getLink();
    }

    public function getFile(): string
    {
        if ($path = $this->getPath()) {
            if (str_contains($path, '://')) {
                return $path;
            }

            return App::map(static::defaultMediaRoot(), $path);
        }

        if ($digest = $this->getDigest()) {
            return App::map(
                static::defaultUploadPath(),
                $this->getType(),
                substr($digest, 0, 2),
                substr($digest, 2) . $this->getExt()
            );
        }

        return '';
    }

    /**
     * @return string|null
     */
    public function getSource(): ?string
    {
        return $this->source;
    }

    /**
     * @param string|null $source
     */
    public function setSource(?string $source): void
    {
        $this->source = $source;
    }

    /**
     * @return string|null
     */
    public function getExt(): ?string
    {
        return $this->ext;
    }

    /**
     * @param string|null $ext
     */
    public function setExt(?string $ext): void
    {
        $this->ext = $ext;
    }

    /**
     * @return string
     */
    public function getDigest(): string
    {
        return $this->digest;
    }

    /**
     * @param string $digest
     */
    public function setDigest(string $digest): void
    {
        $this->digest = $digest;
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
     * @return float
     */
    public function getDuration(): float
    {
        return $this->duration;
    }

    /**
     * @param float $duration
     */
    public function setDuration(float $duration): void
    {
        $this->duration = $duration;
    }

    /**
     * @return string|null
     */
    public function getMime(): ?string
    {
        return $this->mime;
    }

    /**
     * @param string|null $mime
     */
    public function setMime(?string $mime): void
    {
        $this->mime = $mime;
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


}