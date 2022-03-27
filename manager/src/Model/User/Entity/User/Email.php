<?php

namespace App\Model\User\Entity\User;

use InvalidArgumentException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

class Email
{
    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        Assert::notEmpty($value);

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Некорректный email');
        }

        $this->value = mb_strtolower($value);
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}