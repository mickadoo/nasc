<?php

namespace Nasc\Repo;

class CustomFieldRepo extends AbstractRepo
{
    public function findByGroup(int $groupId)
    {
        return civicrm_api3($this->getEntityName(), 'get', ['custom_group_id' => $groupId])['values'];
    }
}