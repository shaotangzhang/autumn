<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/11
 */

namespace App\Models\Trading\Util;

use Stringable;

class SizeCode implements Stringable
{
    public const FORMAT = "{country}{gender}{size}";

    private int $size = 0;
    private string $gender = '';
    private string $country = '';

    // Sizing:
    //
    //	UGG:
    //		[AU|UK|EU|US|CN] - [M|W|K|U] - 010-500
    //
    //		AUK020	= AU Kid Size 2
    //		UKM065	= UK Men Size 6.5
    //      CN037   = CN     Size 38

    public static function parse(
        string  $code,
        ?int    $defaultSize = null,
        ?string $defaultGender = null,
        ?string $defaultCountry = null): ?string
    {
        if (preg_match('/^\s([A-Z][A-Z])?([A-Z])?(\d+)\s*$/i', $code, $matches)) {
            $instance = new static;
            $instance->country = $matches[1] ?? $defaultCountry;
            $instance->gender = $matches[2] ?? $defaultGender;
            $instance->size = (int)($matches[3] ?? $defaultSize);
            return $instance;
        }
        return null;
    }

    public function __toString(): string
    {
        return $this->build();
    }

    public function build(): string
    {
        return strtr(static::FORMAT, $this->parameters());
    }

    public function parameters(): array
    {
        return [
            '{country}' => $this->getCountry(),
            '{gender}' => $this->getGender(),
            '{size}' => substr('000' . $this->getSize(), -3),
        ];
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    /**
     * @return string
     */
    public function getGender(): string
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     */
    public function setGender(string $gender): void
    {
        $this->gender = $gender;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry(string $country): void
    {
        $this->country = $country;
    }


}