<?php

namespace Nasc\Service;

class DirectQueryService
{
    /**
     * Unfortunately this is needed sometimes (because it's CiviCRM)
     *
     * @param string $sql
     */
    public function runDirectQuery(string $sql) {
        \CRM_Core_DAO::executeQuery($sql);
    }
}