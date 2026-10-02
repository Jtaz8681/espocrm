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

class AfterUpgrade
{
    public function run($container)
    {
        $this->container = $container;

        $this->populateOpportunityContactId();

        $this->manageIndexes();

        $config = $container->get('config');
        $config->set('personNameFormat', 'firstLast');
        $config->set('streamEmailWithContentEntityTypeList', ['Case']);
        $config->save();

        $from = 'custom/Espo/Custom/Resources/templates/noteEmailRecieved';
        $to = 'custom/Espo/Custom/Resources/templates/noteEmailReceived';
        if (is_dir($from)) {
            rename($from, $to);
        }
    }

    protected function populateOpportunityContactId()
    {
        $pdo = $this->container->get('entityManager')->getPdo();

        $sql = "
            SELECT opportunity.id AS 'opportunityId', contact.id AS `contactId` FROM `opportunity`
            JOIN contact_opportunity ON contact_opportunity.opportunity_id = opportunity.id AND contact_opportunity.deleted = 0
            JOIN contact ON contact.id = contact_opportunity.contact_id AND contact.deleted = 0
            WHERE
            contact.id IN (
              SELECT MIN(contact.id)
              FROM `opportunity`
              JOIN contact_opportunity ON contact_opportunity.opportunity_id = opportunity.id AND contact_opportunity.deleted = 0
              JOIN contact ON contact.id = contact_opportunity.contact_id AND contact.deleted = 0
              GROUP BY opportunity.id
            ) AND
            opportunity.contact_id IS NULL AND
            opportunity.deleted = 0
        ";

        $sth = $pdo->prepare($sql);
        $sth->execute();

        while ($row = $sth->fetch()) {
            $cId = $row['contactId'] ?? null;
            $oId = $row['opportunityId'] ?? null;
            if (!$cId || !$oId) continue;

            $q = "
                UPDATE `opportunity` SET contact_id = ".$pdo->quote($cId)." WHERE id = ".$pdo->quote($oId)."
            ";
            $pdo->query($q);
        }
    }

    protected function manageIndexes()
    {
        $pdo = $this->container->get('entityManager')->getPdo();

        $sth = $pdo->prepare("SHOW INDEX FROM `note`");
        $sth->execute();
        $rows = [];
        while ($row = $sth->fetch()) {
            $rows[] = $row;
        }

        $indexes = [];

        foreach ($rows as $item) {
            $k = 0;
            foreach ($rows as $item2) {
                if ($item['Key_name'] === $item2['Key_name']) {
                    $k++;
                }
            }
            if ($k === 1 && $item['Column_name'] === 'number') {
                $indexes[] = $item['Key_name'];
            }
        }

        $isFound = false;

        $oldIndexes = [];

        foreach ($indexes as $key) {
            if ($key === 'UNIQ_NUMBER') {
                $isFound = true;
            }
        }

        if (!$isFound) {
            try {
                $sql = "CREATE UNIQUE INDEX UNIQ_NUMBER ON `note` (`number`)";
                $pdo->query($sql);
            } catch (\Exception $e) {}
        }

        foreach ($indexes as $item) {
            if ($item === 'UNIQ_NUMBER') continue;
            try {
                $sql = "DROP INDEX {$item} ON `note`";
                $pdo->query($sql);
            } catch (\Exception $e) {}
        }
    }
}
