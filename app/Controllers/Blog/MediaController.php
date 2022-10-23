<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/9
 */

namespace App\Controllers\Blog;

use App\Models\Blog\Media;
use Autumn\Http\Exceptions\NotAcceptableException;
use Autumn\System\Request;
use InvalidArgumentException;

class MediaController extends AbstractController
{
    /**
     * @throws NotAcceptableException
     */
    public function create(Request $request, ?string $url): Media
    {
        if($url) {
            return $this->getMediaService()->createImageFromUrl($url);
        }

        if($upload = $request->upload('fileField')) {
            return $this->getMediaService()->createFromUpload($upload);
        }

        throw new InvalidArgumentException('No file uploaded.');
    }
}