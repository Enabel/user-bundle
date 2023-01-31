<?php

declare(strict_types=1);

namespace Enabel\UserBundle\Utils;

use Symfony\Component\Console\Exception\InvalidArgumentException;

use function Symfony\Component\String\u;

/**
 * Custom validator to validate command arguments
 * @author Damien Lagae <damien.lagae@enabel.be>
 */
class Validator
{
    public function validateEmail(?string $email): ?string
    {
        $email = $this->stringNotEmpty($email, 'The email');

        return $this->stringAsEmail($email, 'The email', '@');
    }

    public function validatePassword(?string $plainPassword): ?string
    {
        $plainPassword = $this->stringNotEmpty($plainPassword, 'The password');

        return $this->stringMinLength($plainPassword, 'The password', 6);
    }

    public function validateDisplayName(?string $displayName): ?string
    {
        return $this->stringNotEmpty($displayName, 'The display name');
    }

    private function stringNotEmpty(?string $value, string $field): string
    {
        if (empty($value)) {
            throw new InvalidArgumentException(sprintf('%s can not be empty.', $field));
        }

        return $value;
    }

    private function stringMinLength(?string $value, string $field, int $minLength): ?string
    {
        if (u($value)->trim()->length() < 6) {
            throw new InvalidArgumentException(sprintf('%s must be at least %s characters long.', $field, $minLength));
        }

        return $value;
    }

    private function stringAsEmail(?string $value, string $field, string $contains): ?string
    {
        if (null === u($value)->indexOf($contains)) {
            throw new InvalidArgumentException(sprintf('%s should look like a real email.', $field));
        }

        return $value;
    }
}
