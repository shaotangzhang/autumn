<?php
/**
 * Autumn PHP Framework
 *
 * Date:        25/06/2024
 */

namespace Autumn\System\Events;

use Autumn\Events\EventInterface;
use Autumn\System\Application;
class AppBootEvent implements EventInterface
{
    private Application $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * @return Application
     */
    public function getApplication(): Application
    {
        return $this->application;
    }
}