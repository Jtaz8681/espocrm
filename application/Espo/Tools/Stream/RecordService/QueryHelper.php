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

namespace Espo\Tools\Stream\RecordService;

use Espo\Core\Acl\Permission;
use Espo\Core\Acl\Table;
use Espo\Core\AclManager;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Name\Field;
use Espo\Core\Select\SearchParams;
use Espo\Core\Select\SelectBuilderFactory;
use Espo\Entities\Note;
use Espo\Entities\User;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\Part\Condition as Cond;
use Espo\ORM\Query\Part\Expression as Expr;
use Espo\ORM\Query\Select;
use Espo\ORM\Query\SelectBuilder;

class QueryHelper
{
    public function __construct(
        private EntityManager $entityManager,
        private SelectBuilderFactory $selectBuilderFactory,
        private AclManager $aclManager
    ) {}

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    public function buildBaseQueryBuilder(SearchParams $searchParams): SelectBuilder
    {
        $builder = $this->entityManager
            ->getQueryBuilder()
            ->select()
            ->from(Note::ENTITY_TYPE);

        if (
            $searchParams->getWhere() ||
            $searchParams->getTextFilter() ||
            $searchParams->getPrimaryFilter() ||
            $searchParams->getBoolFilterList() !== []
        ) {
            $builder = $this->selectBuilderFactory
                ->create()
                ->from(Note::ENTITY_TYPE)
                ->withComplexExpressionsForbidden()
                ->withWherePermissionCheck()
                ->withSearchParams(
                    $searchParams
                        ->withOffset(null)
                        ->withMaxSize(null)
                )
                ->buildQueryBuilder()
                ->order([]);
        }

        return $builder;
    }

    /**
     * @return string[]
     */
    public function getUserQuerySelect(): array
    {
        return [
            'id',
            'number',
            'type',
            'post',
            'data',
            'parentType',
            'parentId',
            'relatedType',
            'relatedId',
            'targetType',
            Field::CREATED_AT,
            'createdById',
            'createdByName',
            'isGlobal',
            'isInternal',
            'createdByGender',
        ];
    }

    public function buildPostedToUserQuery(User $user, SelectBuilder $baseBuilder): Select
    {
        return (clone $baseBuilder)
            ->where([
                'type' => Note::TYPE_POST,
                'targetType' => Note::TARGET_USERS,
                'parentId' => null,
                'createdById!=' => $user->getId(),
                'isGlobal' => false,
            ])
            ->where(
                Cond::in(
                    Expr::column('id'),
                    SelectBuilder::create()
                        ->select('noteId')
                        ->from('NoteUser')
                        ->where(['userId' => $user->getId()])
                        ->build()
                )
            )
            ->build();
    }

    public function buildPostedToPortalQuery(User $user, SelectBuilder $baseBuilder): ?Select
    {
        if (!$user->isPortal()) {
            if ($this->aclManager->getPermissionLevel($user, Permission::PORTAL) !== Table::LEVEL_YES) {
                return null;
            }

            return (clone $baseBuilder)
                ->where([
                    'parentId' => null,
                    'type' => Note::TYPE_POST,
                    'targetType' => Note::TARGET_PORTALS,
                    'createdById!=' => $user->getId(),
                    'isGlobal' => false,
                ])
                ->build();
        }

        $portalIdList = $user->getPortals()->getIdList();

        if ($portalIdList === []) {
            return null;
        }

        return (clone $baseBuilder)
            ->where([
                'parentId' => null,
                'type' => Note::TYPE_POST,
                'targetType' => Note::TARGET_PORTALS,
                'createdById!=' => $user->getId(),
                'isGlobal' => false,
            ])
            ->where(
                Cond::in(
                    Expr::column('id'),
                    SelectBuilder::create()
                        ->select('noteId')
                        ->from('NotePortal')
                        ->where(['portalId' => $portalIdList])
                        ->build()
                )
            )
            ->build();
    }

    public function buildPostedToTeamsQuery(User $user, SelectBuilder $baseBuilder): ?Select
    {
        if ($user->getTeamIdList() === []) {
            return null;
        }

        return (clone $baseBuilder)
            ->where([
                'parentId' => null,
                'type' => Note::TYPE_POST,
                'targetType' => Note::TARGET_TEAMS,
                'createdById!=' => $user->getId(),
                'isGlobal' => false,
            ])
            ->where(
                Cond::in(
                    Expr::column('id'),
                    SelectBuilder::create()
                        ->select('noteId')
                        ->from('NoteTeam')
                        ->where(['teamId' => $user->getTeamIdList()])
                        ->build()
                )
            )
            ->build();
    }

    public function buildPostedByUserQuery(User $user, SelectBuilder $baseBuilder): Select
    {
        return (clone $baseBuilder)
            ->where([
                'parentId' => null,
                'type' => Note::TYPE_POST,
                'createdById' => $user->getId(),
            ])
            ->build();
    }

    public function buildPostedToGlobalQuery(User $user, SelectBuilder $baseBuilder): ?Select
    {
        if ($user->isPortal() || $user->isApi()) {
            return null;
        }

        return (clone $baseBuilder)
            ->where([
                'type' => Note::TYPE_POST,
                'targetType' => Note::TARGET_ALL,
                'parentId' => null,
                'createdById!=' => $user->getId(),
                'isGlobal' => true,
            ])
            ->build();
    }
}
