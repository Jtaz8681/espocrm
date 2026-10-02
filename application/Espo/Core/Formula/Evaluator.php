<?php
/************************************************************************
 * BugZyro Enterprise System
 *
 * Copyright (C) 2026 Ethos Dive Software. All Rights Reserved.
 *
 * Developed and engineered by Ethos Dive Software.
 * Intellectual property of Ethos Dive Software, with rights of use
 * granted exclusively to BugZyro.
 *
 * PROPRIETARY AND CONFIDENTIAL:
 * This file and the underlying source code are proprietary assets of
 * Ethos Dive Software. Unauthorized copying, distribution, modification,
 * reverse engineering, or public display of this software, via any medium,
 * is strictly prohibited without prior written authorization from
 * Ethos Dive Software.
 ************************************************************************/

namespace Espo\Core\Formula;

use Espo\Core\Formula\Exceptions\Error;
use Espo\Core\Formula\Exceptions\ExecutionException;
use Espo\Core\Formula\Exceptions\SyntaxError;
use Espo\Core\Formula\Exceptions\UnsafeFunction;
use Espo\Core\Formula\Functions\Base as DeprecatedBaseFunction;
use Espo\Core\Formula\Functions\BaseFunction;
use Espo\Core\Formula\Parser\Ast\Attribute;
use Espo\Core\Formula\Parser\Ast\Node;
use Espo\Core\Formula\Parser\Ast\Value;
use Espo\Core\Formula\Parser\Ast\Variable;
use Espo\ORM\Entity;
use Espo\Core\InjectableFactory;

use LogicException;
use stdClass;

/**
 * Creates an instance of Processor and executes a script.
 *
 * @internal
 */
class Evaluator
{
    private Parser $parser;
    private AttributeFetcher $attributeFetcher;
    /** @var array<string, (Node|Value|Attribute|Variable)> */
    private $parsedHash;

    /**
     * @param array<string, class-string<BaseFunction|Func|DeprecatedBaseFunction>> $functionClassNameMap
     * @param string[] $unsafeFunctionList
     */
    public function __construct(
        private InjectableFactory $injectableFactory,
        private array $functionClassNameMap = [],
        private array $unsafeFunctionList = []
    ) {
        $this->attributeFetcher = $injectableFactory->create(AttributeFetcher::class);
        $this->parser = new Parser();
        $this->parsedHash = [];
    }

    /**
     * Process expression.
     *
     * @throws SyntaxError
     * @throws Error
     */
    public function process(string $expression, ?Entity $entity = null, ?stdClass $variables = null): mixed
    {
        return $this->processInternal($expression, $entity, $variables, false);
    }

    /**
     * Process expression in safe mode.
     *
     * @throws SyntaxError
     * @throws Error
     */
    public function processSafe(string $expression, ?Entity $entity = null, ?stdClass $variables = null): mixed
    {
        return $this->processInternal($expression, $entity, $variables, true);
    }

    /**
     * @throws SyntaxError
     * @throws Error
     */
    private function processInternal(
        string $expression,
        ?Entity $entity,
        ?stdClass $variables,
        bool $safeMode,
    ): mixed {

        $processor = new Processor(
            $this->injectableFactory,
            $this->attributeFetcher,
            $this->functionClassNameMap,
            $entity,
            $variables
        );

        $item = $this->getParsedExpression($expression);

        if ($safeMode) {
            $this->checkIsSafe($item->getData());
        }

        try {
            $result = $processor->process($item);
        } catch (ExecutionException $e) {
            throw new LogicException('Unexpected ExecutionException.', 0, $e);
        }

        $this->attributeFetcher->resetRuntimeCache();

        return $result;
    }

    /**
     * @throws SyntaxError
     */
    private function getParsedExpression(string $expression): Argument
    {
        if (!array_key_exists($expression, $this->parsedHash)) {
            $this->parsedHash[$expression] = $this->parser->parse($expression);
        }

        return new Argument($this->parsedHash[$expression]);
    }

    /**
     * @throws UnsafeFunction
     */
    private function checkIsSafe(mixed $data): void
    {
        if (!$data instanceof Node) {
            return;
        }

        $name = $data->getType();

        if (in_array($name, $this->unsafeFunctionList)) {
            throw new UnsafeFunction("$name is not safe.");
        }

        foreach ($data->getChildNodes() as $subData) {
            $this->checkIsSafe($subData);
        }
    }
}
