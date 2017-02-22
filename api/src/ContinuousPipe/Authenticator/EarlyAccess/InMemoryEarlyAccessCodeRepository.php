<?php

namespace ContinuousPipe\Authenticator\EarlyAccess;

class InMemoryEarlyAccessCodeRepository implements EarlyAccessCodeRepository
{
    /**
     * @var EarlyAccessCode[]
     */
    private $codes = [];

    public function __construct($codes = [])
    {
        foreach ($codes as $code) {
            $this->add(EarlyAccessCode::fromString($code));
        }
    }

    /**
     * @param string $code
     *
     * @return EarlyAccessCode
     *
     * @throws EarlyAccessCodeNotFoundException
     */
    public function findByCode(string $code): EarlyAccessCode
    {
        $code = EarlyAccessCode::fromString($code);
        if (!in_array($code, $this->codes)) {
            throw new EarlyAccessCodeNotFoundException(
                sprintf('Early access code "%s" does not exist or already used.', $code)
            );
        }
        return $code;
    }

    private function add(EarlyAccessCode $code)
    {
        $this->codes[] = $code;
    }
}
