<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * PasswordComplexity is a custom validation constraint for checking password complexity.
 * It ensures that the password meets specific criteria such as minimum length, presence of uppercase letters,
 * digits, and special characters.
 *
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class PasswordComplexity extends Constraint
{
    // This messages will be used for validation errors.
    public string $minLengthMessage = 'Le mot de passe doit contenir au moins {{ min_length }} caractères.';
    public string $uppercaseMessage = 'Le mot de passe doit contenir au moins une lettre majuscule.';
    public string $digitMessage = 'Le mot de passe doit contenir au moins un chiffre.';
    public string $specialCharMessage = 'Le mot de passe doit contenir au moins un caractère spécial.';
    
    // Minimum length of the password.
    public int $minLength = 12;
    
    /**
     * Constructor for the PasswordComplexity constraint.
     *
     * @param int|null $minLength The minimum length of the password. Defaults to 12 if not provided.
     * @param string|null $minLengthMessage Custom message for minimum length validation.
     * @param string|null $uppercaseMessage Custom message for uppercase letter validation.
     * @param string|null $digitMessage Custom message for digit validation.
     * @param string|null $specialCharMessage Custom message for special character validation.
     * @param array|null $groups The validation groups this constraint belongs to.
     * @param mixed $payload Additional data that can be attached to the constraint.
     */
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
        
        // Override default property values if specific values are provided to the constructor.
        $this->minLength = $minLength ?? $this->minLength;
        $this->minLengthMessage = $minLengthMessage ?? $this->minLengthMessage;
        $this->uppercaseMessage = $uppercaseMessage ?? $this->uppercaseMessage;
        $this->digitMessage = $digitMessage ?? $this->digitMessage;
        $this->specialCharMessage = $specialCharMessage ?? $this->specialCharMessage;
    }
}