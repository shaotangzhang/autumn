<?php

namespace Autumn\System\Templates\Renderers;

use Autumn\System\Templates\RendererInterface;

class DatetimeRenderer implements RendererInterface
{
    /**
     * Outputs the formatted date/time or interval data.
     *
     * @param mixed $data The data to be rendered, which could be a DateTimeInterface or DateInterval.
     * @param \ArrayAccess|array|null $args Optional arguments to pass to the renderer.
     * @param array|null $context The rendering context.
     * @return mixed
     */
    public function output(mixed $data, \ArrayAccess|array $args = null, array $context = null): mixed
    {
        if ($data instanceof \DateTimeInterface) {
            // Output ISO 8601 formatted date/time string
            echo $data->format('c');
            return null;
        }

        if ($data instanceof \DateInterval) {
            // Output interval specification string
            echo $this->formatDateInterval($data);
            return null;
        }

        return $data;
    }

    /**
     * Formats a DateInterval instance as a string.
     *
     * @param \DateInterval $interval The DateInterval instance to format.
     * @return string The formatted interval string.
     */
    public function formatDateInterval(\DateInterval $interval): string
    {
        $format = 'P';

        if ($interval->y) {
            $format .= $interval->y . 'Y';
        }

        if ($interval->m) {
            $format .= $interval->m . 'M';
        }

        if ($interval->d) {
            $format .= $interval->d . 'D';
        }

        if ($interval->h || $interval->i || $interval->s) {
            $format .= 'T';

            if ($interval->h) {
                $format .= $interval->h . 'H';
            }

            if ($interval->i) {
                $format .= $interval->i . 'M';
            }

            if ($interval->s) {
                $format .= $interval->s . 'S';
            }
        }

        return $format;
    }
}
