<?php
/**
 * Autumn PHP Framework
 *
 * Date:        4/02/2024
 */

namespace Autumn\Lang;

class Image
{
    private array $sizeInfo = [];
    private array $imageInfo = [];

    public function __construct(private \GdImage $image)
    {
    }

    /**
     * @return \GdImage
     */
    public function getImage(): \GdImage
    {
        return $this->image;
    }

    /**
     * @return array
     */
    public function getSizeInfo(): array
    {
        return $this->sizeInfo;
    }

    /**
     * @return array
     */
    public function getImageInfo(): array
    {
        return $this->imageInfo;
    }

    public function getWidth(): int
    {
        return $this->sizeInfo[0] ??= imagesx($this->image);
    }

    public function getHeight(): int
    {
        return $this->sizeInfo[1] ??= imagesy($this->image);
    }

    public function getFileName(): string
    {
        return $this->imageInfo['uri'] ?? '';
    }

    public static function fromFile(string $file): static
    {
        $size = getimagesize($file, $info);
        if ($size === false) {
            throw new \InvalidArgumentException('The image file is not found or the data is not in a recognised format.');
        }

        if (!str_starts_with($size['mime'] ?? '', 'image/')) {
            throw new \InvalidArgumentException('The image type is unsupported.');
        }

        $data = file_get_contents($file);
        $img = imagecreatefromstring($data);
        if ($img === false) {
            throw new \InvalidArgumentException('The image is corrupt and cannot be loaded.');
        }

        $info['uri'] = $file;
        $instance = new static($img);
        $instance->imageInfo = $info;
        $instance->sizeInfo = $size;
        return $instance;
    }

    public static function fromString(string $data): static
    {
        $size = getimagesizefromstring($data, $info);
        if ($size === false) {
            throw new \InvalidArgumentException('The image file is not found or the data is not in a recognised format.');
        }

        if (!str_starts_with($size['mime'] ?? '', 'image/')) {
            throw new \InvalidArgumentException('The image type is unsupported.');
        }

        $img = imagecreatefromstring($data);
        if ($img === false) {
            throw new \InvalidArgumentException('The image is corrupt and cannot be loaded.');
        }

        $instance = new static($img);
        $instance->imageInfo = $info;
        $instance->sizeInfo = $size;
        return $instance;
    }

    public function restrictSizeTo(int $width = null, int $height = null): static
    {
        if (!$width && !$height) {
            return $this;
        }

        $ratioW = $ratioH = 1;
        if ($width) {
            $ratioW = $this->getWidth() / $width;
        }

        if ($height) {
            $ratioH = $this->getHeight() / $height;
        }

        if ($ratio = max($ratioW, $ratioH)) {
            if ($ratio === 1) {
                return $this;
            }

            $w = $this->getWidth() / $ratio;
            $h = $this->getHeight() / $ratio;

            $img = imagecreatetruecolor((int)$w, (int)$h);

            $backgroundColor = imagecolorallocatealpha($img, 0, 0, 0, 127); // 透明黑色
            imagefill($img, 0, 0, $backgroundColor);
            imagesavealpha($img, true);

            if (imagecopyresampled($img, $this->image,
                0, 0, 0, 0,
                $w, $h, $this->getWidth(), $this->getHeight()
            )) {
                return new static($img);
            }
        }

        throw new \RuntimeException(sprintf(
            'Failed to redraw the image to the size of (%s, %s)', $width, $height
        ));
    }

    public function saveTo(string $file, string $type = null, int $quality = null, int $filters = null, array $extra = null): bool
    {
        $path = dirname($file);
        if (!is_dir($path)) {
            if (!mkdir($path, 0777, true)) {
                throw new \RuntimeException(sprintf(
                        'Unable to create the path "%s" for image storage.', $path)
                );
            }
        }

        if (method_exists($this, $func = 'saveImageAs' . $type)) {
            return $this->$func($file, $quality, $filters, $extra);
        }

        throw new \RuntimeException('Unsupported image type for output.');
    }

    protected function saveImageAsJpeg(?string $file, int $quality = null): bool
    {
        return imagejpeg($this->image, $file, $quality);
    }

    protected function saveImageAsJpg(?string $file, int $quality = null): bool
    {
        return $this->saveImageAsJpeg($file, $quality);
    }

    protected function saveImageAsPng(?string $file, int $quality = null, int $filters = null): bool
    {
        return imagepng($this->image, $file, intval(($quality ?? -10) / 10), $filters ?? -1);
    }

    protected function saveImageAsGif(?string $file): bool
    {
        return imagegif($this->image, $file);
    }

    protected function saveImageAsBmp(?string $file, bool $compressed = null): bool
    {
        return imagebmp($this->image, $file, $compressed ?? true);
    }
}