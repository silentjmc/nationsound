<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PasswordComplexityValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {

        if (null === $value || '' === $value) {
            return;
        }

        if (strlen($value) < $constraint->minLength) {
            $this->context->buildViolation($constraint->minLengthMessage)
                ->setParameter('{{ min_length }}', $constraint->minLength)
                ->addViolation();
        }
        
        if (!preg_match('/[A-Z]/', $value)) {
                    $this->context->buildViolation($constraint->uppercaseMessage)
                        ->addViolation();
                }

        if (!preg_match('/[0-9]/', $value)) {
            $this->context->buildViolation($constraint->digitMessage)
                ->addViolation();
        }

        if (!preg_match('/[\W_]/', $value)) {
            $this->context->buildViolation($constraint->specialCharMessage)
                ->addViolation();
        }
    }
}