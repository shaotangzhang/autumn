<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/12
 */

namespace App\Controllers\Blog\Media;

use App\Controllers\Blog\AbstractController;
use Autumn\System\Attributes\ResponseEntity;
use DOMDocument;

class ImportFromHtmlController extends AbstractController
{
    #[ResponseEntity]
    public function index(
        ?string $url
    ): array
    {
        $dom = new DOMDocument;
        // libxml_use_internal_errors(true);
        $dom->loadHTMLFile($url, LIBXML_NOERROR);

        $list = [];
        foreach($dom->getElementsByTagName('img') as $image) {
            if($src = $image->getAttribute('data-src') ?: $image->getAttribute('src')) {
                $list[] = $src;
            }
        }

        return array_unique($list);
    }
}