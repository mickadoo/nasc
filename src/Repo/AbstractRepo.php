<?php

namespace Nasc\Repo;

class AbstractRepo
{
    public function findOneBy(array $params): ?array
    {
        $result = $this->findBy($params);

        if (count($result) !== 1) {
            return null;
        }

        return reset($result);
    }

    public function findBy(array $params): array
    {
        $params['options']['limit'] = 0;

        return civicrm_api3($this->getEntityName(), 'get', $params)['values'];
    }

    public function delete(int $id)
    {
        civicrm_api3($this->getEntityName(), 'delete', ['id' => $id]);
    }

    public function create(array $params): array
    {
        return civicrm_api3($this->getEntityName(), 'create', $params);
    }

    /**
     * Determines the entity name to use for CiviCRM API calls. By default gets name from repo classname
     *
     * @return string
     */
    public function getEntityName(): string
    {
        return substr(str_replace(__NAMESPACE__ . '\\', '', get_called_class()), 0, -4);
    }
}