<?php
/**
 * Autumn PHP Framework
 *
 * Date:        12/06/2024
 */

namespace Autumn\Extensions\Cms\Models\Message;

use Autumn\Database\Interfaces\TypeInterface;
use Autumn\Database\Models\ExtendedEntity;
use Autumn\Database\Traits\ExtendedEntityTrait;
use Autumn\Database\Traits\TypeColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\MessageIdColumnTrait;

class MessageRecipient extends ExtendedEntity implements TypeInterface
{
    use ExtendedEntityTrait;
    use TypeColumnTrait;
    use MessageIdColumnTrait;

    public const ENTITY_NAME = 'cms_message_recipients';
    public const DEFAULT_TYPE = self::TYPE_CC;
    public const TYPE_CC = 'cc';
    public const TYPE_BCC = 'bcc';

    public const RELATION_PRIMARY_CLASS = Message::class;
    public const RELATION_PRIMARY_COLUMN = 'message_id';
}