<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class PasswordComplexity extends Constraint
{
    public string $minLengthMessage = 'Le mot de passe doit contenir au moins {{ min_length }} caractères.';
    public string $uppercaseMessage = 'Le mot de passe doit contenir au moins une lettre majuscule.';
    public string $digitMessage = 'Le mot de passe doit contenir au moins un chiffre.';
    public string $specialCharMessage = 'Le mot de passe doit contenir au moins un caractère spécial.';
    
    public int $minLength = 12;
    
    public function __construct(
        int $minLength = null,
        string $minLengthMessage = null,
        string $uppercaseMessage = null,
        string $digitMessage = null,
        string $specialCharMessage = null,
        array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct([], $groups, $payload);
        
        $this->minLength = $minLength ?? $this->minLength;
        $this->minLengthMessage = $minLengthMessage ?? $this->minLengthMessage;
        $this->uppercaseMessage = $uppercaseMessage ?? $this->uppercaseMessage;
        $this->digitMessage = $digitMessage ?? $this->digitMessage;
        $this->specialCharMessage = $specialCharMessage ?? $this->specialCharMessage;
    }
}