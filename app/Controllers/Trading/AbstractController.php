<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/8
 */

namespace App\Controllers\Trading;

use App\Services\Trading\TradingServicesTrait;
use Autumn\System\Controller;
use Autumn\System\View;

abstract class AbstractController extends Controller
{
    use TradingServicesTrait;

    protected string $templateLayout = '/trading/common/layout';
    protected string $templatePrefix = '/trading/';

    public function getSiteId(): int
    {
        return (int)($_ENV['SITE_ID'] ?? 0);
    }
}