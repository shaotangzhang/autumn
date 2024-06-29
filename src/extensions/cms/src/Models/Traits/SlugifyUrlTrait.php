<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Traits;

use Autumn\Attributes\Transient;

/**
 * Trait UrlPrefixSuffixTrait
 *
 * @package     Autumn\Extensions\Cms\Models\Traits
 * @since       19/01/2024
 */
trait SlugifyUrlTrait
{
    use SlugColumnTrait;

    #[Transient]
    private ?string $urlPrefix = null;

    #[Transient]
    private ?string $urlSuffix = null;

    #[Transient]
    private ?string $urlPath = null;

    public static function defaultUrlPath(): ?string
    {
        if ($suffix = static::defaultUrlPrefix()) {
            return rtrim($suffix, '/\\') . '/index';
        }

        return null;
    }

    public static function defaultUrlPrefix(): ?string
    {
        return null;
    }

    public static function defaultUrlSuffix(): ?string
    {
        return null;
    }

    public static function slugify(string $url): string
    {
        // 将非文字字符替换为连字符
        $string = preg_replace('/[^\p{L}\p{N}\/\s]/u', '-', $url);

        // 删除连续的连字符
        $string = preg_replace('/-+/', '-', $string);

        // 将空格替换为连字符
        $string = str_replace(' ', '-', $string);

        // 转换为小写
        return mb_strtolower($string, 'UTF-8');
    }

    public function getUrl(): string
    {
        if ($slug = $this->getSlug()) {
            $prefix = $this->getUrlPrefix() ?? static::defaultUrlPrefix();
            $suffix = $this->getUrlSuffix() ?? static::defaultUrlSuffix();

            if ($prefix) {
                if (!str_starts_with($slug, $prefix)) {
                    $slug = $prefix . $slug;
                }
            }

            if ($suffix) {
                if (!str_ends_with($slug, $suffix)) {
                    $slug .= $suffix;
                }
            }
        } elseif ($path = $this->getUrlPath() ?? static::defaultUrlPath()) {
            return $path . '?id=' . $this->getId();
        }

        return $slug;
    }

    public function getUrlPath(): ?string
    {
        return $this->urlPath;
    }

    /**
     * @param string|null $urlPath
     */
    public function setUrlPath(?string $urlPath): void
    {
        $this->urlPath = $urlPath;
    }

    public function getUrlPrefix(): ?string
    {
        return $this->urlPrefix;
    }

    /**
     * @param string|null $urlPrefix
     */
    public function setUrlPrefix(?string $urlPrefix): void
    {
        $this->urlPrefix = $urlPrefix;
    }

    public function getUrlSuffix(): ?string
    {
        return $this->urlSuffix;
    }

    /**
     * @param string|null $urlSuffix
     */
    public function setUrlSuffix(?string $urlSuffix): void
    {
        $this->urlSuffix = $urlSuffix;
    }
}