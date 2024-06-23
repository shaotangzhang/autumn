<?php
/**
 * Autumn PHP Framework
 *
 * Date:        28/02/2024
 */

namespace Autumn\Lang;

use Psr\Http\Message\StreamInterface;

class XML
{
    public static function import(string $file, bool $silent = null): ?\SimpleXMLElement
    {
        $data = file_get_contents($file);
        $xml = static::importXML($data, $silent);
        if (($xml === null) && !$silent) {
            throw new \InvalidArgumentException('Unable to parse the XML file.');
        }
        return $xml;
    }

    public static function importXML(string  $data,
                                     ?string $class_name = "SimpleXMLElement",
                                     int     $options = 0,
                                     string  $namespace_or_prefix = "",
                                     bool    $is_prefix = false): ?\SimpleXMLElement
    {
        $e = simplexml_load_string($data, $class_name, $options, $namespace_or_prefix, $is_prefix);
        if ($e === false) {
            return null;
        }
        return $e;
    }

    public static function importFromStream(StreamInterface $stream,
                                            ?string         $class_name = "SimpleXMLElement",
                                            int             $options = 0,
                                            string          $namespace_or_prefix = "",
                                            bool            $is_prefix = false): ?\SimpleXMLElement
    {
        $stream->rewind();
        if ($xml = $stream->getContents()) {
            return static::importXML($xml, $class_name, $options, $namespace_or_prefix, $is_prefix);
        }

        return null;
    }

    public static function load(string $file, bool $silent = null): ?\DOMDocument
    {

    }

    public static function loadXML(string $xml, bool $silent = null): ?\DOMDocument
    {
        // 创建 HTMLDocument 对象
        $dom = new \DOMDocument();

        // 忽略解析错误
        $origin = libxml_use_internal_errors(!!$silent);

        // 尝试载入响应体内容
        $success = $dom->loadXML($xml, LIBXML_NOWARNING | LIBXML_NOERROR);

        // 恢复解析错误处理
        libxml_use_internal_errors($origin);

        if ($success === false) {
            if (!$silent) {
                throw new \RuntimeException('Unable to parse body as XMLDocument.');
            }

            return null;
        }

        return $dom;
    }
}