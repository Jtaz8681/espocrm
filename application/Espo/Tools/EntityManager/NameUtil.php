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

namespace Espo\Tools\EntityManager;

use Espo\Core\Exceptions\Error;
use Espo\Core\Name\Field;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Metadata;
use Espo\Core\Utils\Route;
use Espo\Core\Utils\Util;
use Espo\Core\ServiceFactory;
use Espo\ORM\Defs\Params\EntityParam;
use Espo\ORM\EntityManager;
use Espo\ORM\Entity;
use Espo\ORM\Name\Attribute;

class NameUtil
{
    public const MAX_ENTITY_NAME_LENGTH = 64;
    public const MIN_ENTITY_NAME_LENGTH = 3;

    /**
     * @var string[]
     */
    public const RESERVED_WORLD_LIST = [
        '__halt_compiler',
        'abstract',
        'and',
        'array',
        'as',
        'break',
        'callable',
        'case',
        'catch',
        'class',
        'clone',
        'const',
        'continue',
        'declare',
        'default',
        'die',
        'do',
        'echo',
        'else',
        'elseif',
        'empty',
        'enddeclare',
        'endfor',
        'endforeach',
        'endif',
        'endswitch',
        'endwhile',
        'eval',
        'exit',
        'extends',
        'final',
        'for',
        'foreach',
        'function',
        'global',
        'goto',
        'if',
        'implements',
        'include',
        'include_once',
        'instanceof',
        'insteadof',
        'interface',
        'isset',
        'list',
        'namespace',
        'new',
        'or',
        'print',
        'private',
        'protected',
        'public',
        'require',
        'require_once',
        'return',
        'static',
        'switch',
        'throw',
        'trait',
        'try',
        'unset',
        'use',
        'var',
        'while',
        'xor',
        'common',
        'fn',
        'parent',
        'int',
        'float',
        'bool',
        'string',
        'true',
        'false',
        'null',
        'void',
        'iterable',
        'object',
        'mixed',
        'never',
    ];

    /**
     * @var string[]
     */
    public const FIELD_FORBIDDEN_NAME_LIST = [
        Attribute::ID,
        Attribute::DELETED,
        'deleteId',
        'skipDuplicateCheck',
        'null',
        'false',
        'true',
        Field::VERSION_NUMBER,
        Field::IS_STARRED,
        Field::IS_FOLLOWED,
        Field::FOLLOWERS,
        Field::TEAMS,
        Field::ASSIGNED_USER,
        Field::ASSIGNED_USERS,
        Field::COLLABORATORS,
        Field::STREAM_UPDATED_AT,
        Field::CREATED_BY,
        Field::CREATED_AT,
        Field::MODIFIED_BY,
        Field::MODIFIED_AT,
        Field::IS_LOCKED,
        Field::PIPELINE,
        Field::PIPELINE_STAGE,

        'emailAddressList',
        'userEmailAddressList',
        'excludeFromReplyEmailAddressList',
    ];

    /**
     * @var string[]
     */
    public const LINK_FORBIDDEN_NAME_LIST = [
        'posts',
        'stream',
        'streamAttachments',
        'subscription',
        'starSubscription',
        'action',
        'null',
        'false',
        'true',
        'layout',
        'system',
        Field::FOLLOWERS,
        Field::TEAMS,
        Field::ASSIGNED_USER,
        Field::ASSIGNED_USERS,
        Field::COLLABORATORS,
        Field::CREATED_BY,
        Field::MODIFIED_BY,
    ];

    /**
     * @var string[]
     */
    public const ENTITY_TYPE_FORBIDDEN_NAME_LIST = [
        'Common',
        'PortalUser',
        'ApiUser',
        'Timeline',
        'About',
        'Admin',
        'Null',
        'False',
        'True',
        'Base',
        'Layout',
        'Home',
    ];

    public function __construct(
        private Metadata $metadata,
        private ServiceFactory $serviceFactory,
        private EntityManager $entityManager,
        private Route $routeUtil,
        private Config $config
    ) {}

    public function nameIsBad(string $name): bool
    {
        if (!$name) {
            return true;
        }

        if (preg_match('/[^a-zA-Z\d]/', $name)) {
            return true;
        }

        if (preg_match('/[^A-Z]/', $name[0])) {
            return true;
        }

        return false;
    }

    public function nameIsTooShort(string $name): bool
    {
        return strlen($name) < NameUtil::MIN_ENTITY_NAME_LENGTH;
    }

    public function nameIsTooLong(string $name): bool
    {
        return strlen(Util::camelCaseToUnderscore($name)) > NameUtil::MAX_ENTITY_NAME_LENGTH;
    }

    public function nameIsNotAllowed(string $name): bool
    {
        if (in_array($name, self::ENTITY_TYPE_FORBIDDEN_NAME_LIST)) {
            return true;
        }

        if (in_array(strtolower($name), NameUtil::RESERVED_WORLD_LIST)) {
            return true;
        }

        if ($name !== Util::normalizeScopeName($name)) {
            return true;
        }

        return false;
    }

    public function nameIsUsed(string $name): bool
    {
        if ($this->metadata->get(['scopes', $name])) {
            return true;
        }

        if ($this->metadata->get(['entityDefs', $name])) {
            return true;
        }

        if ($this->metadata->get(['clientDefs', $name])) {
            return true;
        }

        if ($this->relationshipExists($name)) {
            return true;
        }

        if ($this->controllerExists($name)) {
            return true;
        }

        if ($this->serviceFactory->checkExists($name)) {
            return true;
        }

        if ($this->routeExists($name)) {
            return true;
        }

        return false;
    }

    private function routeExists(string $name): bool
    {
        foreach ($this->routeUtil->getFullList() as $route) {
            if (
                $route->getRoute() === '/' . $name ||
                str_starts_with($route->getRoute(), '/' . $name . '/')
            ) {
                return true;
            }
        }

        return false;
    }

    private function controllerExists(string $name): bool
    {
        $controllerClassName = 'Espo\\Custom\\Controllers\\' . Util::normalizeClassName($name);

        if (class_exists($controllerClassName)) {
            return true;
        }

        foreach ($this->metadata->getModuleList() as $moduleName) {
            $controllerClassName =
                'Espo\\Modules\\' . $moduleName . '\\Controllers\\' . Util::normalizeClassName($name);

            if (class_exists($controllerClassName)) {
                return true;
            }
        }

        $controllerClassName = 'Espo\\Controllers\\' . Util::normalizeClassName($name);

        if (class_exists($controllerClassName)) {
            return true;
        }

        return false;
    }

    public function relationshipExists(string $name): bool
    {
        /** @var string[] $scopeList */
        $scopeList = array_keys($this->metadata->get(['scopes'], []));

        foreach ($scopeList as $entityType) {
            $relationsDefs = $this->entityManager
                ->getMetadata()
                ->get($entityType, EntityParam::RELATIONS);

            if (empty($relationsDefs)) {
                continue;
            }

            foreach ($relationsDefs as $item) {
                if (empty($item['type']) || empty($item['relationName'])) {
                    continue;
                }

                if (
                    $item['type'] === Entity::MANY_MANY &&
                    ucfirst($item['relationName']) === ucfirst($name)
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    public function addCustomPrefix(string $name, bool $ucFirst = false): string
    {
        if ($this->config->get('customPrefixDisabled')) {
            return $name;
        }

        $prefix = $ucFirst ? 'C' : 'c';

        return $prefix . ucfirst($name);
    }

    public function fieldExists(string $entityType, string $name): bool
    {
        return (bool) $this->metadata->get("entityDefs.$entityType.fields.$name");
    }

    public function linkExists(string $entityType, string $name): bool
    {
        return (bool) $this->metadata->get("entityDefs.$entityType.links.$name");
    }

    /**
     * @since 9.2.4
     */
    public function linkNameIsBad(string $name): bool
    {
        if ($name === '') {
            return true;
        }

        if (preg_match('/[^a-z]/', $name[0])) {
            return true;
        }

        return $this->nameIsBad(ucfirst($name));
    }
}
