<?php

namespace Nasc\Repo;

class AbstractRepo
{
    public function findOneBy(array $params) : ?array {
        try {
            return civicrm_api3($this->getEntityName(), 'getsingle', $params);
        } catch (\Exception $exception) {
            return null;
        }
    }

    public function delete(int $id) {
        civicrm_api3($this->getEntityName(), 'delete', ['id' => $id]);
    }

    /**
     * Determines the entity name to use for CiviCRM API calls. By default gets name from repo classname
     *
     * @return string
     */
    public function getEntityName() : string
    {
        return str_replace([__NAMESPACE__ . '\\', 'Repo'], '', get_called_class());
    }
}