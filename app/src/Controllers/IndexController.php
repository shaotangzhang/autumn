<?php
/**
 * Autumn PHP Framework
 *
 * Date:        18/06/2024
 */

namespace App\Controllers;

use Autumn\Extensions\Cms\Models\Page\Page;

class IndexController extends AbstractController
{
    public function index(): mixed
    {

        Page::find(1);
        Page::findOrFail([]);
        Page::repository(['alias' => 'abc'])->alias('123')->where('abc', '=', 1231);

        Page::all();        // all
        Page::active();     // status = active
        Page::disabled();   // status = disabled
        Page::standard();   // withoutTrashed
        Page::trashed();    // onlyTrashed

        Page::repository(['status' => 'active']);

        return $this->view('home/index');
    }
}