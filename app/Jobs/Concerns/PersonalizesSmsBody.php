<?php

namespace App\Jobs\Concerns;

trait PersonalizesSmsBody
{
    /**
     * Replaces the {first_name} merge tag in an SMS body with the
     * recipient's first name, falling back to "there" when no name is on
     * file for the recipient.
     */
    protected function personalizeSms(string $body, ?string $name): string
    {
        $firstName = trim(strtok(trim((string) $name), ' ') ?: '') ?: 'there';

        return str_replace('{first_name}', $firstName, $body);
    }
}
