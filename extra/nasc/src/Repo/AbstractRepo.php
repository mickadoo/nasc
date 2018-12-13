<?php

namespace Nasc\Repo;

class AbstractRepo
{
    public function findOneBy(array $params) : ?array {
        $result = $this->findBy($params);

        if (count($result) !== 1) {
            $err = sprintf('Expected single %s but found %d', $this->getEntityName(), count($result));
            throw new \RuntimeException($err);
        }

        return reset($result);
    }

    public function findBy(array $params) : array {
        return civicrm_api3($this->getEntityName(), 'get', $params)['values'];
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