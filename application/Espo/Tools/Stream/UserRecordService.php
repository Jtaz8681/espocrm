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

namespace Espo\Tools\Stream;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Name\Field;
use Espo\Core\Select\SearchParams;
use Espo\Modules\Crm\Entities\Account;
use Espo\ORM\EntityManager;
use Espo\Entities\StreamSubscription;
use Espo\Entities\User;
use Espo\Entities\Note;
use Espo\Entities\Email;
use Espo\Core\Utils\Metadata;
use Espo\Core\Acl;
use Espo\Core\AclManager;
use Espo\Core\Acl\Table;
use Espo\Core\Record\Collection as RecordCollection;
use Espo\Core\Utils\Acl\UserAclManagerProvider;
use Espo\ORM\Query\Part\Condition as Cond;
use Espo\ORM\Query\Part\Expression as Expr;
use Espo\ORM\Query\Part\Order;
use Espo\ORM\Query\Part\Where\OrGroup;
use Espo\ORM\Query\Select;
use Espo\ORM\Query\SelectBuilder;
use Espo\Tools\Stream\RecordService\Helper;
use Espo\Tools\Stream\RecordService\NoteHelper;
use Espo\Tools\Stream\RecordService\QueryHelper;

class UserRecordService
{
    private const FILTER_POSTS = 'posts';

    public function __construct(
        private EntityManager $entityManager,
        private User $user,
        private Metadata $metadata,
        private Acl $acl,
        private UserAclManagerProvider $userAclManagerProvider,
        private NoteAccessControl $noteAccessControl,
        private Helper $helper,
        private QueryHelper $queryHelper,
        private NoteHelper $noteHelper,
        private MassNotePreparator $massNotePreparator,
    ) {}

    /**
     * Find user stream records.
     *
     * @return RecordCollection<Note>
     * @throws Forbidden
     * @throws BadRequest
     * @throws NotFound
     */
    public function find(?string $userId, SearchParams $searchParams): RecordCollection
    {
        $userId ??= $this->user->getId();

        $user = $userId === $this->user->getId() ?
            $this->user :
            $this->entityManager->getRDBRepositoryByClass(User::class)->getById($userId);

        if (!$user) {
            throw new NotFound("User not found.");
        }

        /** @noinspection PhpRedundantOptionalArgumentInspection */
        if (!$this->acl->checkUserPermission($user, Acl\Permission::USER)) {
            throw new Forbidden("No user permission access.");
        }

        $offset = $searchParams->getOffset() ?? 0;
        $maxSize = $searchParams->getMaxSize();

        $baseBuilder = $this->queryHelper->buildBaseQueryBuilder($searchParams)
            ->select($this->queryHelper->getUserQuerySelect())
            ->leftJoin(Field::CREATED_BY)
            ->order('number', Order::DESC)
            ->limit(0, $offset + $maxSize + 1);

        $queryList = [];

        $this->buildSubscriptionQueries($user, $baseBuilder, $queryList, $searchParams);
        $this->buildSubscriptionSuperQuery($user, $baseBuilder, $queryList, $searchParams);
        $this->buildPostedToUserQuery($user, $baseBuilder, $queryList);
        $this->buildPostedToPortalQuery($user, $baseBuilder, $queryList);
        $this->buildPostedToTeamsQuery($user, $baseBuilder, $queryList);
        $this->buildPostedByUserQuery($user, $baseBuilder, $queryList);
        $this->buildPostedToGlobalQuery($user, $baseBuilder, $queryList);

        return $this->processQueryList($user, $queryList, $offset, $maxSize);
    }

    /**
     * Find notes created by a user.
     *
     * @return RecordCollection<Note>
     * @throws NotFound
     * @throws Forbidden
     * @throws BadRequest
     */
    public function findOwn(string $userId, SearchParams $searchParams): RecordCollection
    {
        $user = $this->entityManager->getRDBRepositoryByClass(User::class)->getById($userId);

        if (!$user) {
            throw new NotFound("User not found.");
        }

        /** @noinspection PhpRedundantOptionalArgumentInspection */
        if (!$this->acl->checkUserPermission($user, Acl\Permission::USER)) {
            throw new Forbidden("No user permission access.");
        }

        $offset = $searchParams->getOffset() ?? 0;
        $maxSize = $searchParams->getMaxSize();

        $baseBuilder = $this->queryHelper->buildBaseQueryBuilder($searchParams)
            ->select($this->queryHelper->getUserQuerySelect())
            ->leftJoin(Field::CREATED_BY)
            ->order('number', Order::DESC)
            ->limit(0, $offset + $maxSize + 1);

        $queryList[] = (clone $baseBuilder)
            ->where(['createdById' => $user->getId()])
            ->build();

        return $this->processQueryList($user, $queryList, $offset, $maxSize);
    }

    /**
     * @return array<string|int, mixed>
     */
    private function getSubscriptionIgnoreWhereClause(User $user): array
    {
        $ignoreScopeList = $this->helper->getIgnoreScopeList($user, true);
        $ignoreRelatedScopeList = $this->helper->getIgnoreScopeList($user);

        if (empty($ignoreScopeList)) {
            return [];
        }

        $whereClause = [];

        $whereClause[] = [
            'OR' => [
                'relatedType' => null,
                'relatedType!=' => $ignoreRelatedScopeList,
            ]
        ];

        $whereClause[] = [
            'OR' => [
                'parentType' => null,
                'parentType!=' => $ignoreScopeList,
            ]
        ];

        if (in_array(Email::ENTITY_TYPE, $ignoreRelatedScopeList)) {
            $whereClause[] = [
                'type!=' => [
                    Note::TYPE_EMAIL_RECEIVED,
                    Note::TYPE_EMAIL_SENT,
                ],
            ];
        }

        return $whereClause;
    }

    private function loadNoteAdditionalFields(Note $note): void
    {
        $note->loadAdditionalFields();
    }

    private function getUserAclManager(User $user): ?AclManager
    {
        try {
            return $this->userAclManagerProvider->get($user);
        } catch (Acl\Exceptions\NotAvailable) {
            return null;
        }
    }

    /**
     * @return string[]
     */
    private function getNotAllEntityTypeList(User $user): array
    {
        if (!$user->isPortal()) {
            return [];
        }

        $aclManager = $this->getUserAclManager($user);

        $list = [];

        $scopes = $this->metadata->get('scopes', []);

        foreach ($scopes as $scope => $item) {
            if ($scope === User::ENTITY_TYPE) {
                continue;
            }

            if (empty($item['entity']) || empty($item['object'])) {
                continue;
            }

            if (
                !$aclManager ||
                $aclManager->getLevel($user, $scope, Table::ACTION_READ) !== Table::LEVEL_ALL
            ) {
                $list[] = $scope;
            }
        }

        return $list;
    }

    /**
     * @param Select[] $queryList
     */
    private function buildSubscriptionQueriesPortal(
        User $user,
        SelectBuilder $builder,
        array &$queryList
    ): void {

        if (!$user->isPortal()) {
            return;
        }

        $builder->where([
            'isInternal' => false,
        ]);

        $notAllEntityTypeList = $this->getNotAllEntityTypeList($user);

        $orGroup = [
            [
                'relatedId' => null,
            ],
            [
                'relatedId!=' => null,
                'relatedType!=' => $notAllEntityTypeList,
            ],
        ];

        $aclManager = $this->getUserAclManager($user);

        if ($aclManager && $aclManager->check($user, Email::ENTITY_TYPE, Table::ACTION_READ)) {
            $orGroup[] = [
                'relatedId!=' => null,
                'relatedType' => Email::ENTITY_TYPE,
                'noteUser.userId' => $user->getId(),
            ];

            $builder->leftJoin(
                'noteUser',
                'noteUser', [
                    'noteUser.noteId=:' => 'id',
                    'noteUser.deleted' => false,
                    'note.relatedType' => Email::ENTITY_TYPE,
                ]
            );
        }

        $builder->where([
            'OR' => $orGroup,
        ]);

        $queryList[] = $builder->build();
    }

    /**
     * @param SearchParams $searchParams
     * @param Select[] $queryList
     */
    private function buildSubscriptionQueries(
        User $user,
        SelectBuilder $baseBuilder,
        array &$queryList,
        SearchParams $searchParams
    ): void {

        $ignoreWhereClause = $this->getSubscriptionIgnoreWhereClause($user);

        $builder = clone $baseBuilder;

        $builder
            ->join(
                StreamSubscription::ENTITY_TYPE,
                'subscription',
                [
                    'entityType:' => 'parentType',
                    'entityId:' => 'parentId',
                    'subscription.userId' => $user->getId(),
                ]
            )
            ->where($ignoreWhereClause);

        if ($user->isPortal()) {
            $this->buildSubscriptionQueriesPortal($user, $builder, $queryList);

            return;
        }

        if ($searchParams->getPrimaryFilter() === self::FILTER_POSTS) {
            // No need access control as posts do not have a 'related' link.
            $queryList[] = $builder->build();

            return;
        }

        $this->buildAccessQueries($user, $builder, $queryList, true);
    }

    /**
     * @param SearchParams $searchParams
     * @param Select[] $queryList
     */
    private function buildSubscriptionSuperQuery(
        User $user,
        SelectBuilder $baseBuilder,
        array &$queryList,
        SearchParams $searchParams
    ): void {

        if ($user->isPortal()) {
            return;
        }

        if ($searchParams->getPrimaryFilter() === self::FILTER_POSTS) {
            // Posts do not have a 'super-parent'.
            // They are not visible to super parent subscribers.
            // Bypassing for a performance reason.
            return;
        }

        $ignoreWhereClause = $this->getSubscriptionIgnoreWhereClause($user);

        $builder = clone $baseBuilder;

        $builder
            ->join(
                StreamSubscription::ENTITY_TYPE,
                'subscription',
                [
                    // Improves performance significantly.
                    'entityType' => Account::ENTITY_TYPE,
                    //'entityType:' => 'superParentType',
                    'entityId:' => 'superParentId',
                    'subscription.userId' => $user->getId(),
                ]
            )
            // NOT EXISTS sub-query would perform very slow.
            ->leftJoin(
                StreamSubscription::ENTITY_TYPE,
                'subscriptionExclude',
                [
                    'entityType:' => 'parentType',
                    'entityId:' => 'parentId',
                    'subscriptionExclude.userId' => $user->getId(),
                ]
            )
            ->where([
                'OR' => [
                    'parentId!=:' => 'superParentId',
                    'parentType!=:' => 'superParentType',
                ],
                'subscriptionExclude.id' => null,
            ])
            ->where(['superParentType' => Account::ENTITY_TYPE])
            ->where($ignoreWhereClause);

        $this->buildAccessQueries($user, $builder, $queryList);
    }

    /**
     * @param Select[] $queryList
     */
    private function buildPostedToUserQuery(User $user, SelectBuilder $baseBuilder, array &$queryList): void
    {
        $queryList[] = $this->queryHelper->buildPostedToUserQuery($user, $baseBuilder);
    }

    /**
     * @param Select[] $queryList
     */
    private function buildPostedToPortalQuery(User $user, SelectBuilder $baseBuilder, array &$queryList): void
    {
        $query = $this->queryHelper->buildPostedToPortalQuery($user, $baseBuilder);

        if (!$query) {
            return;
        }

        $queryList[] = $query;
    }

    /**
     * @param Select[] $queryList
     */
    private function buildPostedToTeamsQuery(User $user, SelectBuilder $baseBuilder, array &$queryList): void
    {
        $query = $this->queryHelper->buildPostedToTeamsQuery($user, $baseBuilder);

        if (!$query) {
            return;
        }

        $queryList[] = $query;
    }

    /**
     * @param Select[] $queryList
     */
    private function buildPostedByUserQuery(User $user, SelectBuilder $baseBuilder, array &$queryList): void
    {
        $queryList[] = $this->queryHelper->buildPostedByUserQuery($user, $baseBuilder);
    }

    /**
     * @param Select[] $queryList
     */
    private function buildPostedToGlobalQuery(User $user, SelectBuilder $baseBuilder, array &$queryList): void
    {
        $query = $this->queryHelper->buildPostedToGlobalQuery($user, $baseBuilder);

        if (!$query) {
            return;
        }

        $queryList[] = $query;
    }

    /**
     * Split into tree queries for all, team and own.
     * Note that only notes with 'related' and 'superParent' are subject
     * to access control. Notes with only 'parent' don't have teams and
     * users set.
     *
     * @param Select[] $queryList
     * @param bool $noParentFilter False is for 'superParent'.
     */
    private function buildAccessQueries(
        User $user,
        SelectBuilder $baseBuilder,
        array &$queryList,
        bool $noParentFilter = false
    ): void {

        $onlyTeamEntityTypeList = $this->helper->getOnlyTeamEntityTypeList($user);
        $onlyOwnEntityTypeList = $this->helper->getOnlyOwnEntityTypeList($user);

        $allBuilder = clone $baseBuilder;

        $orWhere = [
            [
                'relatedId!=' => null,
                'relatedType!=' => array_merge($onlyTeamEntityTypeList, $onlyOwnEntityTypeList),
            ],
        ];

        $orWhere[] = $noParentFilter ?
            ['relatedId=' => null] :
            [
                'relatedId=' => null,
                'parentType!=' => array_merge($onlyTeamEntityTypeList, $onlyOwnEntityTypeList),
            ];

        $allBuilder->where(['OR' => $orWhere]);

        $queryList[] = $allBuilder->build();

        if ($onlyTeamEntityTypeList !== []) {
            $teamBuilder = clone $baseBuilder;

            $orWhere = [
                ['relatedType=' => $onlyTeamEntityTypeList],
            ];

            if (!$noParentFilter) {
                $orWhere[] = [
                    'relatedId=' => null,
                    'parentType=' => $onlyTeamEntityTypeList,
                ];
            }

            $teamBuilder
                ->where(['OR' => $orWhere])
                ->where(
                    // Separate sub-queries perform faster that a single with two LEFT JOINs inside.
                    OrGroup::create(
                        Cond::in(
                            Expr::column('id'),
                            SelectBuilder::create()
                                ->from('NoteTeam')
                                ->select('noteId')
                                ->where(['teamId' => $user->getTeamIdList()])
                                ->build()
                        ),
                        Cond::in(
                            Expr::column('id'),
                            SelectBuilder::create()
                                ->from('NoteUser')
                                ->select('noteId')
                                ->where(['userId' => $user->getId()])
                                ->build()
                        ),
                    )
                );

            $queryList[] = $teamBuilder->build();
        }

        if ($onlyOwnEntityTypeList !== []) {
            $ownBuilder = clone $baseBuilder;

            $orWhere = [
                ['relatedType=' => $onlyOwnEntityTypeList],
            ];

            if (!$noParentFilter) {
                $orWhere[] = [
                    'relatedId=' => null,
                    'parentType=' => $onlyOwnEntityTypeList,
                ];
            }

            $ownBuilder
                ->where(['OR' => $orWhere])
                ->where(
                    Cond::in(
                        Expr::column('id'),
                        SelectBuilder::create()
                            ->from('NoteUser')
                            ->select('noteId')
                            ->where(['userId' => $user->getId()])
                            ->build()
                    )
                );

            $queryList[] = $ownBuilder->build();
        }
    }

    /**
     * @param Select[] $queryList
     * @return RecordCollection<Note>
     */
    private function processQueryList(
        User $user,
        array $queryList,
        int $offset,
        ?int $maxSize
    ): RecordCollection {

        $builder = $this->entityManager
            ->getQueryBuilder()
            ->union()
            ->all()
            ->order('number', Order::DESC)
            ->limit($offset, $maxSize + 1);

        foreach ($queryList as $query) {
            $builder->query($query);
        }

        $unionQuery = $builder->build();

        $sql = $this->entityManager
            ->getQueryComposer()
            ->compose($unionQuery);

        $sthCollection = $this->entityManager
            ->getRDBRepositoryByClass(Note::class)
            ->findBySql($sql);

        $collection = $this->entityManager
            ->getCollectionFactory()
            ->createFromSthCollection($sthCollection);

        foreach ($collection as $e) {
            $this->loadNoteAdditionalFields($e);
            $this->noteAccessControl->apply($e, $user);
            $this->noteHelper->prepare($e);
        }

        $this->massNotePreparator->prepare($collection);

        return RecordCollection::createNoCount($collection, $maxSize);
    }
}
