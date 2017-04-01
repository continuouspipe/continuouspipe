<?php

namespace ContinuousPipe\River\Filter;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Filter\CodeChanges\CodeChangesResolver;
use ContinuousPipe\River\Tide;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

final class Filter
{
    /**
     * @var CodeChangesResolver
     */
    private $codeChangesResolver;
    /**
     * @var UuidInterface
     */
    private $flowUuid;
    /**
     * @var CodeReference
     */
    private $codeReference;
    /**
     * @var array
     */
    private $context;

    public function __construct(CodeChangesResolver $codeChangesResolver, UuidInterface $flowUuid, CodeReference $codeReference, array $context)
    {
        $this->codeChangesResolver = $codeChangesResolver;
        $this->flowUuid = $flowUuid;
        $this->codeReference = $codeReference;
        $this->context = $context;
    }

    public static function forTide(CodeChangesResolver $codeChangesResolver, Tide $tide, array $context) : self
    {
        return new self($codeChangesResolver, $tide->getFlowUuid(), $tide->getCodeReference(), $context);
    }

    /**
     * Evaluates the filter with the given context.
     *
     * @param string $expression
     *
     * @throws FilterException
     *
     * @return bool
     */
    public function evaluates(string $expression) : bool
    {
        $language = new ExpressionLanguage();
        $language->register('has_changes_for_files', function () {
            throw new \RuntimeException('This function is not compilable');
        }, function (array $context, $files) {
            if (!is_array($files)) {
                $files = [$files];
            }

            return $this->codeChangesResolver->hasChangesInFiles($this->flowUuid, $this->codeReference, $files);
        });

        try {
            $evaluated = $language->evaluate($expression, $this->context);
        } catch (SyntaxError $e) {
            throw new FilterException(sprintf(
                'The expression provided ("%s") is not valid: %s',
                $expression,
                $e->getMessage()
            ), $e->getCode(), $e);
        } catch (\InvalidArgumentException $e) {
            throw new FilterException($e->getMessage(), $e->getCode(), $e);
        } catch (\RuntimeException $e) {
            throw new FilterException('The filter seems to be wrong, we will investigate', $e->getCode(), $e);
        }

        if (is_int($evaluated)) {
            $evaluated = (bool) $evaluated;
        }

        if (!is_bool($evaluated)) {
            throw new FilterException(sprintf(
                'Expression "%s" is not valid as it does not return a boolean',
                $expression
            ));
        }

        return $evaluated;
    }
}
