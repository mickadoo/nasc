<?php

namespace Nasc\Service;

use Nasc\Repo\CustomFieldRepo;
use Nasc\Repo\CustomGroupRepo;

class CustomFieldService
{
    /**
     * @var CustomFieldRepo
     */
    private $fieldRepo;

    /**
     * @var CustomGroupRepo
     */
    private $groupRepo;

    /**
     * @param CustomFieldRepo $fieldRepo
     * @param CustomGroupRepo $groupRepo
     */
    public function __construct(CustomFieldRepo $fieldRepo, CustomGroupRepo $groupRepo)
    {
        $this->fieldRepo = $fieldRepo;
        $this->groupRepo = $groupRepo;
    }

    public function findAllByGroupName(string $groupName): array
    {
        $group = $this->groupRepo->findOneBy(['name' => $groupName]);
        if (!$group) {
            return [];
        }

        return $this->fieldRepo->findByGroup((int)$group['id']);
    }
}