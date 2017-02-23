<?php

namespace AppBundle\Validator\Constraints;

use ContinuousPipe\Authenticator\EarlyAccess\EarlyAccessCodeNotFoundException;
use ContinuousPipe\Authenticator\EarlyAccess\EarlyAccessCodeRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EarlyAccessCodeValidator extends ConstraintValidator
{
    /**
     * @var EarlyAccessCodeRepository
     */
    private $earlyAccessCodeRepository;

    public function __construct(EarlyAccessCodeRepository $earlyAccessCodeRepository)
    {
        $this->earlyAccessCodeRepository = $earlyAccessCodeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        try {
            $this->earlyAccessCodeRepository->findByCode($value);
        } catch (EarlyAccessCodeNotFoundException $e) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%code%', $value)
                ->addViolation();
        }
    }
}
