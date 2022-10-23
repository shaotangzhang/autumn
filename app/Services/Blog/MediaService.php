<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/9
 */

namespace App\Services\Blog;

use App\Models\Blog\Media;
use App\Services\AbstractService;
use Autumn\App;
use Autumn\Core\Http\Stream;
use Autumn\Core\Http\UploadedFile;
use Autumn\Http\Exceptions\NotAcceptableException;
use Autumn\Http\MimeType;
use Autumn\System\Attributes\Service;
use Autumn\System\Interfaces\UploadHandlerInterface;
use InvalidArgumentException;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

#[Service]
class MediaService extends AbstractService
{
    public const DEFAULT_UPLOAD_ROOT = '/upload';
    public const DEFAULT_DOWNLOAD_MAX_SIZE = 1024 * 1024 * 10;
    public const DEFAULT_EXTENSION = 'dat';

    private int $downloadMaxSize = self::DEFAULT_DOWNLOAD_MAX_SIZE;
    private string $uploadRoot = PUB_ROOT;
    private string $uploadPath = self::DEFAULT_UPLOAD_ROOT;
    private string $defaultExtension = self::DEFAULT_EXTENSION;

    public function getUploadPathRoot(string $mimeType): string
    {
        return $this->getUploadPath() . '/' . match ($mime = explode('/', $mimeType)[0]) {
                'image', 'audio', 'video' => $mime,
                'application' => 'file',
                default => 'data',
            };
    }

    public function getUploadPathName(string $digest, string $mimeType): string
    {
        return $this->getUploadPathRoot($mimeType)
            . '/'
            . substr($digest, 0, 2)
            . '/'
            . substr($digest, 2)
            . '.'
            . (MimeType::toExtension($mimeType) ?: $this->getDefaultExtension());
    }

    public function getUploadFileName(string $digest, string $mimeType, string &$path = null): string
    {
        return $this->getUploadRoot()
            . ($path = $this->getUploadPathName($digest, $mimeType));
    }

    public function findMedia(int|string|array $path, string $type = null): ?Media
    {
        Media::withoutTrashed()
            ->with('siteId', $this->getSiteId())
            ->when(is_int($path), 'id', $path)
            ->when(is_string($path), 'link', $path)
            ->when(is_array($path), $path)
            ->when($type, 'type', $type);

        return Media::find();
    }

    /**
     * @throws NotAcceptableException
     */
    public function fromUpload(UploadedFile $upload, string $path = null): Media
    {
        if (is_null($path)) {
            $path = $this->upload($upload);
        }

        return $this->fromLocalFile($path, $upload->getClientFilename());
    }

    public function fromLocalFile(string $path, ?string $title = null, ?string $mimeType = null): Media
    {
        if ($media = $this->findMedia($path)) {
            return $media;
        }

        $media = new Media;
        $media->setSiteId($this->getSiteId());
        $media->setTitle($title ?? '');
        $media->setSize(0);
        $media->setMimeType($mimeType ?: '');
        $media->setType(explode('/', $mimeType ?: '')[0]);
        $media->setLink($path);
        $media->setSlug(explode('.', basename(dirname($path)) . basename($path))[0]);

        if (realpath($file = PUB_ROOT . $path)) {
            if ($info = getimagesize($file)) {
                $media->setWidth($info[0] ?: 0);
                $media->setHeight($info[1] ?: 0);
                $media->setMimeType($mimeType = $info['mime'] ?? $mimeType);
                $media->setType(explode('/', $mimeType ?: '')[0]);
            }
            $media->setSize(filesize($file));
        }

        return $media;
    }

    /**
     * @throws NotAcceptableException
     */
    public function createFromUpload(UploadedFile $upload, string $path = null): Media
    {
        $media = $this->fromUpload($upload, $path);
        if ($media->isNew()) $media->save();

        return $media;
    }

    public function downloadFromUrl(string $url, int $maxSize = null, int $bufferSize = null): string
    {
        $data = Stream::load($url, ($maxSize = $maxSize ?? $this->getDownloadMaxSize()) + 1, $bufferSize ?? 4096);
        if (strlen($data) > $maxSize) {
            throw new InvalidArgumentException('The file is larger than the limit in allowed size.');
        }
        return $data;
    }

    public function downloadImageFromUrl(string $url, int $minWidth = null, int $minHeight = null, int $maxWidth = null, int $maxHeight = null): string
    {
        if ($data = $this->downloadFromUrl($url)) {
            $info = MimeType::fromData($data);
            if ($info->isImage()) {

                if (!$minWidth && ($info->getWidth() >= $minWidth)) {
                    if (!$minHeight && ($info->getHeight() >= $minHeight)) {
                        if (!$maxWidth && ($info->getWidth() <= $maxWidth)) {
                            if (!$maxHeight && ($info->getHeight() <= $maxHeight)) {
                                Stream::save(
                                    $this->getUploadFileName(md5($data), (string)$info, $path),
                                    $data, true
                                );
                                return $path;
                            }
                        }

                        throw new InvalidArgumentException('The image size is too large.');
                    }
                }

                throw new InvalidArgumentException('The image size is too small.');
            }

            throw new InvalidArgumentException('The target of url is not an image.');
        }

        throw new InvalidArgumentException('The target of url is not found.');
    }

    public function createImageFromUrl(string $url): Media
    {
        $source = md5($url);
        if ($media = $this->findMedia(['source' => $source], 'image')) {
            return $media;
        }

        $path = $this->downloadImageFromUrl($url, 300, 300);
        $media = $this->fromLocalFile($path, substr($url, 0, 255));

        if ($media->isNew()) {
            $media->setSource($source);
            $media->save();
        }

        return $media;
    }

    /**
     * @throws NotAcceptableException
     */
    public function upload(UploadedFile $upload): string
    {
        if ($error = $upload->getError()) {
            throw new NotacceptableException($error);
        }

        $this->getUploadFileName($upload->digest(), $upload->getClientMediaType(), $path);
        $this->store($path, $upload);
        return $path;
    }

    public function store(string $path, UploadedFileInterface $upload, string $acl = null): void
    {
        if ($handler = App::factory(UploadHandlerInterface::class)) {
            $handler->onUpload($path, $upload, $acl);
        } elseif (!realpath($file = $this->getUploadPath() . $path)) {
            if (!$upload->moveTo($file)) {
                throw new RuntimeException('Unknown error occurs on storing the upload file.');
            }
        }
    }

    /**
     * @return string
     */
    public function getUploadPath(): string
    {
        return $this->uploadPath;
    }

    /**
     * @param string $uploadPath
     */
    public function setUploadPath(string $uploadPath): void
    {
        $this->uploadPath = $uploadPath;
    }

    /**
     * @return string
     */
    public function getUploadRoot(): string
    {
        return $this->uploadRoot;
    }

    /**
     * @param string $uploadRoot
     */
    public function setUploadRoot(string $uploadRoot): void
    {
        $this->uploadRoot = $uploadRoot;
    }

    /**
     * @return float|int
     */
    public function getDownloadMaxSize(): float|int
    {
        return $this->downloadMaxSize;
    }

    /**
     * @param float|int $downloadMaxSize
     */
    public function setDownloadMaxSize(float|int $downloadMaxSize): void
    {
        $this->downloadMaxSize = $downloadMaxSize;
    }

    /**
     * @return string
     */
    public function getDefaultExtension(): string
    {
        return $this->defaultExtension;
    }

    /**
     * @param string $defaultExtension
     */
    public function setDefaultExtension(string $defaultExtension): void
    {
        $this->defaultExtension = $defaultExtension;
    }


}