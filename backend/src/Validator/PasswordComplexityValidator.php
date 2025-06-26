<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Custom validator for checking password complexity.
 * 
 * This validator checks if the password meets the following criteria:
 * - Minimum length specified by the constraint
 * - Contains at least one uppercase letter
 * - Contains at least one digit
 * - Contains at least one special character (non-alphanumeric)
 * 
 * If any of these criteria are not met, it adds a violation to the context.
 * 
 */
class PasswordComplexityValidator extends ConstraintValidator
{
    /**
     * Validates the password against the defined constraints.
     *
     * @param mixed $value The value to validate (expected to be a string).
     * @param Constraint $constraint The constraint that contains validation rules.
     */
    public function validate($value, Constraint $constraint)
    {
        // Check if the value is null or empty
        if (null === $value || '' === $value) {
            return;
        }

        // Check for minimum length
        if (strlen($value) < $constraint->minLength) {
            $this->context->buildViolation($constraint->minLengthMessage)
                ->setParameter('{{ min_length }}', $constraint->minLength)
                ->addViolation();
        }
        
        // Check for uppercase letters
        if (!preg_match('/[A-Z]/', $value)) {
                    $this->context->buildViolation($constraint->uppercaseMessage)
                        ->addViolation();
                }

        // Check for digits
        if (!preg_match('/[0-9]/', $value)) {
            $this->context->buildViolation($constraint->digitMessage)
                ->addViolation();
        }

        // Check for special characters
        // `\W` matches any non-word character (equivalent to [^a-zA-Z0-9_]).
        // `_` (underscore) is often considered a word character, so `\W_` ensures it's also
        // counted as special character.
        if (!preg_match('/[\W_]/', $value)) {
            $this->context->buildViolation($constraint->specialCharMessage)
                ->addViolation();
        }
    }
}