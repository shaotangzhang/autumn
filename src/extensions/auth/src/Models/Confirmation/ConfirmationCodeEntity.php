<?php

namespace Autumn\Extensions\Auth\Models\Confirmation;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Models\RecyclableEntity;
use Autumn\Database\Traits\DescriptionColumnTrait;
use Autumn\Database\Traits\StatusColumnTrait;
use Autumn\Database\Traits\TypeColumnTrait;
use Autumn\Extensions\Auth\Models\Traits\UserIdColumnTrait;

class ConfirmationCodeEntity extends RecyclableEntity
{
    use UserIdColumnTrait;
    use TypeColumnTrait;
    use StatusColumnTrait;
    use DescriptionColumnTrait;

    public const ENTITY_NAME =  'auth_confirmation_codes';

    public const DEFAULT_TYPE = 'default';
    public const DEFAULT_STATUS = 'active';

    #[Column(type: Column::TYPE_STRING, name: 'code', size: 32, charset: Column::CHARSET_ASCII)]
    private string $code = '';

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }
}