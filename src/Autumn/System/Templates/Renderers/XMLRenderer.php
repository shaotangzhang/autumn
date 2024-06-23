<?php

namespace Autumn\System\Templates\Renderers;

use Autumn\Http\HTMLDocument;
use Autumn\System\Templates\RendererInterface;

class XMLRenderer implements RendererInterface
{
    /**
     * Outputs the rendered XML or HTML data.
     *
     * @param mixed $data The data to be rendered, which could be a SimpleXMLElement, HTMLDocument, DOMDocument, or DOMNode.
     * @param \ArrayAccess|array|null $args Optional arguments to pass to the renderer.
     * @param array|null $context The rendering context.
     * @return mixed
     */
    public function output(mixed $data, \ArrayAccess|array $args = null, array $context = null): mixed
    {
        if ($data instanceof HTMLDocument) {
            // Output HTML string for HTMLDocument
            if ($html = $data->saveHTML()) {
                echo $html;
            }
            return null;
        }

        if ($data instanceof \SimpleXMLElement) {
            // Output XML string for SimpleXMLElement
            if ($xml = $data->asXML()) {
                echo $xml;
            }
            return null;
        }

        if ($data instanceof \DOMDocument) {
            // Output XML string for DOMDocument
            if ($xml = $data->saveXML()) {
                echo $xml;
            }
            return null;
        }

        if ($data instanceof \DOMNode) {
            // Output XML string for DOMNode
            if ($xml = $data->ownerDocument?->saveXML($data)) {
                echo $xml;
            }
            return null;
        }

        return $data;
    }
}
