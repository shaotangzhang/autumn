<?php
/**
 * Autumn PHP Framework
 *
 * Date:        26/03/2024
 */

namespace Autumn\Extensions\Cms\Models\Traits;

use Autumn\Database\Attributes\Column;

trait ConfigColumnTrait
{
    #[Column(type: Column::TYPE_JSON, name: 'config')]
    private ?array $config = null;

    /**
     * @return array|null
     */
    public function getConfig(): ?string
    {
        if ($this->config !== null) {
            return json_encode($this->config);
        }

        return $this->config;
    }

    /**
     * @param array|string|null $config
     */
    public function setConfig(array|string|null $config): void
    {
        if (is_string($config)) {
            $config = json_decode($config, true);
        }

        $this->config = $config;
    }

    public function config(string $key = null, mixed $value = null): mixed
    {
        switch (func_num_args()) {
            case 1:
                if ($key !== null) {
                    return $this->config[$key] ?? null;
                }
            // no break
            case 0:
                return $this->config;

            default:
                $this->config[$key] = $value;
                return null;
        }
    }


}