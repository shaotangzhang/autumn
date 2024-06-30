<?php

namespace Autumn\Mailing;

enum RecipientTypeEnum: string
{
    case TO = 'to';
    case CC = 'cc';
    case BCC = 'bcc';
}
