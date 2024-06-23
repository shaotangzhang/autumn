<?php
/**
 * Autumn PHP Framework
 *
 * Date:        25/02/2024
 */

namespace Autumn\Http;

use DOMNode;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\CssSelector\CssSelectorConverter;

class HTMLDocument extends \DOMDocument
{
    /**
     * Create an HTMLDocument instance from a ResponseInterface.
     *
     * @param ResponseInterface $response The HTTP response.
     * @return static An instance of HTMLDocument.
     * @throws \RuntimeException If unable to parse the body as HTMLDocument.
     */
    public static function fromResponse(ResponseInterface $response): static
    {
        $bodyContents = $response->getBody()->getContents();

        return static::fromHTML($bodyContents);
    }

    public static function fromHTML(string $bodyContents, string $charset = null): static
    {
        // 创建 HTMLDocument 对象
        $htmlDocument = new static;

        // 忽略解析错误
        libxml_use_internal_errors(true);

        // 尝试载入响应体内容
        //  LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_PARSEHUGE | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD, 'UTF-8');

        $options = LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_PARSEHUGE | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD;
        $success = $htmlDocument->loadHTML($bodyContents, $options);

        // 恢复解析错误处理
        libxml_use_internal_errors(false);

        if (!$success) {
            throw new \RuntimeException('Unable to parse body as HTMLDocument.');
        }

        return $htmlDocument;
    }

    /**
     * Get the inner HTML content of an element.
     *
     * @param \DOMElement $element The target element.
     * @return string The inner HTML content.
     */
    public function getInnerHTML(\DOMElement $element): string
    {
        $innerHtml = '';
        foreach ($element->childNodes as $child) {
            $innerHtml .= $element->ownerDocument->saveHTML($child);
        }
        return $innerHtml;
    }

    /**
     * Get the outer HTML content of an element.
     *
     * @param \DOMElement $element The target element.
     * @return string The outer HTML content.
     */
    public function getOuterHTML(\DOMElement $element): string
    {
        return $element->ownerDocument->saveHTML($element);
    }

    /**
     * Select elements based on a CSS selector.
     *
     * @param string $selector The CSS selector.
     * @return \DOMElement|null The list of matching elements.
     */
    public function querySelector(string $selector): ?\DOMElement
    {
        foreach ($this->querySelectorAll($selector) as $element) {
            if ($element instanceof \DOMElement) {
                return $element;
            }
        }

        return null;
    }

    /**
     * Select all nodes based on a CSS selector.
     *
     * @param string $selector The CSS selector.
     * @return \DOMNodeList An array of matching nodes.
     */
    public function querySelectorAll(string $selector): \DOMNodeList
    {
        $converter = new CssSelectorConverter();
        $xpathSelector = $converter->toXPath($selector);

        $xpath = new \DOMXPath($this);
        $list = $xpath->query($xpathSelector);

        if ($list instanceof \DOMNodeList) {
            return $list;
        }

        return $this->createDocumentFragment()->childNodes;
    }

    /**
     * Select all elements based on a CSS selector.
     *
     * @param string $selector The CSS selector.
     * @return iterable A list of matching elements.
     */
    public function querySelectorElements(string $selector): iterable
    {
        foreach ($this->querySelectorAll($selector) as $node) {
            if ($node instanceof \DOMElement) {
                yield $node;
            }
        }
    }

    /**
     * Select elements based on a CSS selector.
     *
     * @param \DOMElement $element The root element
     * @param string $selector The CSS selector.
     * @return \DOMNode|null The list of matching elements.
     */
    public static function select(\DOMElement $element, string $selector): ?\DOMNode
    {
        foreach (static::selectAll($element, $selector) as $node) {
            return $node;
        }

        return null;
    }

    /**
     * Select all elements based on a CSS selector.
     *
     * @param \DOMElement $element The root element
     * @param string $selector The CSS selector.
     * @return \DOMNodeList An array of matching elements.
     */
    public static function selectAll(\DOMElement $element, string $selector): \DOMNodeList
    {
        $converter = new CssSelectorConverter();
        $xpathSelector = $converter->toXPath($selector);

        $xpath = new \DOMXPath($element->ownerDocument);
        $query = $xpath->query($xpathSelector, $element);
        if ($query instanceof \DOMNodeList) {
            return $query;
        }

        return $element->ownerDocument->createDocumentFragment()->childNodes;
    }

    public static function selectElements(\DOMElement $element, string $selector): iterable
    {
        foreach (static::selectAll($element, $selector) as $item) {
            if ($item instanceof \DOMElement) {
                yield $item;
            }
        }
    }
}
