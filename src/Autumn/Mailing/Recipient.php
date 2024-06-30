<?php

namespace Autumn\Mailing;

use Autumn\Exceptions\ValidationException;

class Recipient implements RecipientInterface
{
    private ?string $name;
    private ?string $email;
    private RecipientTypeEnum $type;

    /**
     * Recipient constructor.
     *
     * @param RecipientTypeEnum $type
     * @param string|null $email
     * @param string|null $name
     * @throws ValidationException
     */
    public function __construct(RecipientTypeEnum $type, string $email = null, string $name = null)
    {
        if (!$name && $email) {
            [$name, $email] = $this->parseRecipientString($email);
        }

        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::of('Invalid email format.');
        }

        $this->name = $name;
        $this->email = $email;
        $this->type = $type;
    }

    /**
     * Parse the recipient string into a name and email.
     *
     * @param string $recipient
     * @return array
     */
    private function parseRecipientString(string $recipient): array
    {
        $pos = strpos($recipient, '<');
        if ($pos !== false) {
            $email = rtrim(trim(substr($recipient, $pos + 1)), '>');
            $name = trim(substr($recipient, 0, $pos));
        } elseif (str_contains($recipient, '@')) {
            $email = trim($recipient);
            $name = null;
        } else {
            $name = trim($recipient);
            $email = null;
        }

        return [$name, $email];
    }

    /**
     * Get the recipient's name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?? '';
    }

    /**
     * Set the recipient's name.
     *
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get the recipient's email.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email ?? '';
    }

    /**
     * Set the recipient's email.
     *
     * @param string|null $email
     * @throws ValidationException
     */
    public function setEmail(?string $email): void
    {
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::of('Invalid email format.');
        }

        $this->email = $email;
    }

    /**
     * Get the recipient's type.
     *
     * @return RecipientTypeEnum
     */
    public function getType(): RecipientTypeEnum
    {
        return $this->type;
    }

    /**
     * Set the recipient's type.
     *
     * @param RecipientTypeEnum $type
     */
    public function setType(RecipientTypeEnum $type): void
    {
        $this->type = $type;
    }
}
