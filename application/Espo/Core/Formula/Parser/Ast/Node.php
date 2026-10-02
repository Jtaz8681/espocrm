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

namespace Espo\Core\Formula\Parser\Ast;

/**
 * An AST node.
 */
class Node
{
    /**
     * @param (Node|Value|Attribute|Variable)[] $childNodes
     */
    public function __construct(private string $type, private array $childNodes)
    {}

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return (Node|Value|Attribute|Variable)[]
     */
    public function getChildNodes(): array
    {
        return $this->childNodes;
    }
}
