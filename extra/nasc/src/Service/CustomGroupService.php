<?php

namespace Nasc\Service;

use Nasc\Repo\CustomFieldRepo;
use Nasc\Repo\CustomGroupRepo;

class CustomGroupService
{
    /**
     * @var CustomGroupRepo
     */
    private $groupRepo;

    /**
     * @var CustomFieldRepo
     */
    private $fieldRepo;

    /**
     * @param CustomGroupRepo $groupRepo
     * @param CustomFieldRepo $fieldRepo
     */
    public function __construct(CustomGroupRepo $groupRepo, CustomFieldRepo $fieldRepo)
    {
        $this->groupRepo = $groupRepo;
        $this->fieldRepo = $fieldRepo;
    }

    public function deleteByName(string $name)
    {
        $group = $this->groupRepo->findOneBy(['name' => $name]);
        if ($group) {
            $this->delete((int) $group['id']);
        }
    }

    public function delete(int $id)
    {
        $fields = $this->fieldRepo->findBy(['custom_group_id' => $id]);
        foreach ($fields as $field) {
            $this->fieldRepo->delete((int) $field['id']);
        }
        $this->groupRepo->delete($id);
    }
}