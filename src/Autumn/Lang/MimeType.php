<?php

namespace Autumn\Lang;

use ArrayAccess;
use Psr\Http\Message\StreamInterface;
use Stringable;

class MimeType implements ArrayAccess, Stringable
{
    static array $mappings = array(
        'video/3gpp2' => '3g2',
        'video/3gp' => '3gp',
        'video/3gpp' => '3gp',
        'application/x-compressed' => '7zip',
        'audio/x-acc' => 'aac',
        'audio/ac3' => 'ac3',
        'application/postscript' => 'ai',
        'audio/x-aiff' => 'aif',
        'audio/aiff' => 'aif',
        'audio/x-au' => 'au',
        'video/x-msvideo' => 'avi',
        'video/msvideo' => 'avi',
        'video/avi' => 'avi',
        'application/x-troff-msvideo' => 'avi',
        'application/macbinary' => 'bin',
        'application/mac-binary' => 'bin',
        'application/x-binary' => 'bin',
        'application/x-macbinary' => 'bin',
        'image/bmp' => 'bmp',
        'image/x-bmp' => 'bmp',
        'image/x-bitmap' => 'bmp',
        'image/x-xbitmap' => 'bmp',
        'image/x-win-bitmap' => 'bmp',
        'image/x-windows-bmp' => 'bmp',
        'image/ms-bmp' => 'bmp',
        'image/x-ms-bmp' => 'bmp',
        'application/bmp' => 'bmp',
        'application/x-bmp' => 'bmp',
        'application/x-win-bitmap' => 'bmp',
        'application/cdr' => 'cdr',
        'application/coreldraw' => 'cdr',
        'application/x-cdr' => 'cdr',
        'application/x-coreldraw' => 'cdr',
        'image/cdr' => 'cdr',
        'image/x-cdr' => 'cdr',
        'zz-application/zz-winassoc-cdr' => 'cdr',
        'application/mac-compactpro' => 'cpt',
        'application/pkix-crl' => 'crl',
        'application/pkcs-crl' => 'crl',
        'application/x-x509-ca-cert' => 'crt',
        'application/pkix-cert' => 'crt',
        'text/css' => 'css',
        'text/comma-separated-values' => 'csv',
        'text/x-comma-separated-values' => 'csv',
        'application/vnd.msexcel' => 'csv',
        'application/x-director' => 'dcr',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/x-dvi' => 'dvi',
        'message/rfc822' => 'eml',
        'application/x-msdownload' => 'exe',
        'video/x-f4v' => 'f4v',
        'audio/x-flac' => 'flac',
        'video/x-flv' => 'flv',
        'image/gif' => 'gif',
        'application/gpg-keys' => 'gpg',
        'application/x-gtar' => 'gtar',
        'application/x-gzip' => 'gzip',
        'application/mac-binhex40' => 'hqx',
        'application/mac-binhex' => 'hqx',
        'application/x-binhex40' => 'hqx',
        'application/x-mac-binhex40' => 'hqx',
        'text/html' => 'html',
        'image/x-icon' => 'ico',
        'image/x-ico' => 'ico',
        'image/vnd.microsoft.icon' => 'ico',
        'text/calendar' => 'ics',
        'application/java-archive' => 'jar',
        'application/x-java-application' => 'jar',
        'application/x-jar' => 'jar',
        'image/jp2' => 'jp2',
        'video/mj2' => 'jp2',
        'image/jpx' => 'jp2',
        'image/jpm' => 'jp2',
        'image/jpeg' => 'jpeg',
        'image/pjpeg' => 'jpeg',
        'application/x-javascript' => 'js',
        'application/json' => 'json',
        'text/json' => 'json',
        'application/vnd.google-earth.kml+xml' => 'kml',
        'application/vnd.google-earth.kmz' => 'kmz',
        'text/x-log' => 'log',
        'audio/x-m4a' => 'm4a',
        'application/vnd.mpegurl' => 'm4u',
        'audio/midi' => 'mid',
        'application/vnd.mif' => 'mif',
        'video/quicktime' => 'mov',
        'video/x-sgi-movie' => 'movie',
        'audio/mpeg' => 'mp3',
        'audio/mpg' => 'mp3',
        'audio/mpeg3' => 'mp3',
        'audio/mp3' => 'mp3',
        'video/mp4' => 'mp4',
        'video/mpeg' => 'mpeg',
        'application/oda' => 'oda',
        'audio/ogg' => 'ogg',
        'video/ogg' => 'ogg',
        'application/ogg' => 'ogg',
        'application/x-pkcs10' => 'p10',
        'application/pkcs10' => 'p10',
        'application/x-pkcs12' => 'p12',
        'application/x-pkcs7-signature' => 'p7a',
        'application/pkcs7-mime' => 'p7c',
        'application/x-pkcs7-mime' => 'p7c',
        'application/x-pkcs7-certreqresp' => 'p7r',
        'application/pkcs7-signature' => 'p7s',
        'application/pdf' => 'pdf',
        'application/octet-stream' => 'pdf',
        'application/x-x509-user-cert' => 'pem',
        'application/x-pem-file' => 'pem',
        'application/pgp' => 'pgp',
        'application/x-httpd-php' => 'php',
        'application/php' => 'php',
        'application/x-php' => 'php',
        'text/php' => 'php',
        'text/x-php' => 'php',
        'application/x-httpd-php-source' => 'php',
        'image/png' => 'png',
        'image/x-png' => 'png',
        'application/powerpoint' => 'ppt',
        'application/vnd.ms-powerpoint' => 'ppt',
        'application/vnd.ms-office' => 'ppt',
        'application/msword' => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        'application/x-photoshop' => 'psd',
        'image/vnd.adobe.photoshop' => 'psd',
        'audio/x-realaudio' => 'ra',
        'audio/x-pn-realaudio' => 'ram',
        'application/x-rar' => 'rar',
        'application/rar' => 'rar',
        'application/x-rar-compressed' => 'rar',
        'audio/x-pn-realaudio-plugins' => 'rpm',
        'application/x-pkcs7' => 'rsa',
        'text/rtf' => 'rtf',
        'text/richtext' => 'rtx',
        'video/vnd.rn-realvideo' => 'rv',
        'application/x-stuffit' => 'sit',
        'application/smil' => 'smil',
        'text/srt' => 'srt',
        'image/svg+xml' => 'svg',
        'application/x-shockwave-flash' => 'swf',
        'application/x-tar' => 'tar',
        'application/x-gzip-compressed' => 'tgz',
        'image/tiff' => 'tiff',
        'text/plain' => 'txt',
        'text/x-vcard' => 'vcf',
        'application/videolan' => 'vlc',
        'text/vtt' => 'vtt',
        'audio/x-wav' => 'wav',
        'audio/wave' => 'wav',
        'audio/wav' => 'wav',
        'application/wbxml' => 'wbxml',
        'video/webm' => 'webm',
        'image/webp' => 'webp',
        'audio/x-ms-wma' => 'wma',
        'application/wmlc' => 'wmlc',
        'video/x-ms-wmv' => 'wmv',
        'video/x-ms-asf' => 'wmv',
        'application/xhtml+xml' => 'xhtml',
        'application/excel' => 'xl',
        'application/msexcel' => 'xls',
        'application/x-msexcel' => 'xls',
        'application/x-ms-excel' => 'xls',
        'application/x-excel' => 'xls',
        'application/x-dos_ms_excel' => 'xls',
        'application/xls' => 'xls',
        'application/x-xls' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.ms-excel' => 'xlsx',
        'application/xml' => 'xml',
        'text/xml' => 'xml',
        'text/xsl' => 'xsl',
        'application/xspf+xml' => 'xspf',
        'application/x-compress' => 'z',
        'application/x-zip' => 'zip',
        'application/zip' => 'zip',
        'application/x-zip-compressed' => 'zip',
        'application/s-compressed' => 'zip',
        'multipart/x-zip' => 'zip',
        'text/x-scriptzsh' => 'zsh',
        'application/x-yaml' => 'yaml',
    );


    /**
     * The major part of mime-type.
     *
     * @var string
     */
    private string $mime;

    /**
     * The minor part of mime-type.
     *
     * @var string
     */
    private string $type;

    private array $info = [];

    /**
     * MimeType constructor.
     *
     * @param string|null $mime
     * @param string|null $type
     */
    public function __construct(string $mime = null, string $type = null, array $info = null)
    {
        list($this->mime, $this->type) = explode('/', $mime . '/' . $type . '/');
        $this->info = $info;
    }

    public static function isValidMimeType(string $mime): bool
    {
        $pattern = '/^[a-z]+\/[a-z0-9\-\+]+$/i';
        return (bool)preg_match($pattern, $mime);
    }

    public static function fromFile(string $file): ?static
    {
        if ($mime = getimagesize($file)['mime'] ?? null) {
            return new static($mime);
        }

        return null;
    }

    public static function fromData(string $data): ?static
    {
        if ($info = getimagesizefromstring($data)) {
            if ($mime = $info['mime'] ?? null) {
                return new static($mime, null, $info);
            }
        }

        return null;
    }

    /**
     * Converts mime-type to extension.
     *
     * @param string $mimeType
     * @return string|null
     */
    public static function toExtension(string $mimeType): ?string
    {
        return static::$mappings[strtolower($mimeType)] ?? null;
    }

    /**
     * Gets the most possible mime-type from an extension.
     *
     * @param string $extension
     * @return false|string
     */
    public static function fromExtension(string $extension): false|string
    {
        return ($extension ? array_search(strtolower($extension), static::$mappings) : false) ?: '';
    }

    /**
     * Returns the mime-type from a file name.
     *
     * @param string $file
     * @param null $info
     * @return false|string
     */
    public static function detect(string $file, &$info = null): false|string
    {
        $info = static::detectImageInfo($file);
        return $info['mime'] ?? static::detectFromExtension(pathinfo($file, PATHINFO_EXTENSION));
    }

    public static function detectImageInfo(string $file): false|array
    {
        $info = getimagesize($file);
        if (isset($info['mime'])) return $info;

        if (function_exists('mime_content_type')) {
            if ($mime = mime_content_type($file)) {
                return [0, 0, 'mime' => $mime];
            }
        }

        return false;
    }

    public static function detectImageInfoFromStream(StreamInterface $stream): false|array
    {
        $data = null;

        $stream->rewind();
        for ($i = 1; $i <= 2; $i++) {
            $data .= $stream->read(4096);
            if (getimagesizefromstring($data, $info)) {
                return $info;
            }

            if ($fi ??= class_exists('finfo', false) ? new \finfo(\FILEINFO_MIME) : false) {
                if ($mime = $fi->buffer($data)) {
                    return [0, 0, 'mime' => $mime];
                }
            }
        }

        return false;
    }

    /**
     * Returns the mime-type from a string.
     *
     * @param string $data
     * @param null $info
     * @return string|null
     */
    public static function detectFromString(string $data, &$info = null): ?string
    {
        $info = getimagesizefromstring($data);

        if (!isset($info['mime']) && class_exists('finfo', false)) {
            $f = new \finfo(\FILEINFO_MIME);
            $info = [0, 0, 'mime' => $f->buffer($data)];
        }

        return $info['mime'] ?? null;
    }

    /**
     * Returns a mime-type related to an extension.
     *
     * @param string $extension
     * @return string|false
     */
    public static function detectFromExtension(string $extension): false|string
    {
        return array_search(trim(strtolower($extension), '.'), static::$mappings);
    }

    /**
     *
     * @return string
     */
    public function __toString(): string
    {
        return ($this->mime && $this->type) ? "$this->mime/$this->type" : ($this->info['mime'] ?? '');
    }

    /**
     * Returns the major part of this mime-type.
     *
     * @return string
     */
    public function getMime(): string
    {
        return $this->mime;
    }

    /**
     * Returns the minor part of this mime-type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function getExtension(): string
    {
        return static::toExtension($this->__toString());
    }

    public function getWidth(): ?int
    {
        return $this->info[0] ?? null;
    }

    public function getHeight(): ?int
    {
        return $this->info[1] ?? null;
    }

    /**
     * Checks if this mime-type is image.
     *
     * @return bool
     */
    public function isImage(): bool
    {
        return $this->mime === 'image';
    }

    /**
     * Checks if this mime-type is text.
     *
     * @return bool
     */
    public function isText(): bool
    {
        return $this->mime === 'text';
    }

    /**
     * Checks if this mime-type is application.
     *
     * @return bool
     */
    public function isApplication(): bool
    {
        return $this->mime === 'application';
    }

    /**
     * Checks if this mime-type is audio.
     *
     * @return bool
     */
    public function isAudio(): bool
    {
        return $this->mime === 'audio';
    }

    /**
     * Checks if this mime-type is video.
     *
     * @return bool
     */
    public function isVideo(): bool
    {
        return $this->mime === 'video';
    }

    /**
     * Checks if this mime-type is message.
     *
     * @return bool
     */
    public function isMessage(): bool
    {
        return $this->mime === 'message';
    }

    /**
     * Checks if this mime-type is multipart.
     *
     * @return bool
     */
    public function isMultipart(): bool
    {
        return $this->mime === 'multipart';
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->info[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->info[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->info[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->info[$offset]);
    }
}