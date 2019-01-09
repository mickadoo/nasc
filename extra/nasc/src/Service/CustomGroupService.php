<?php

namespace Nasc\Service;

use Nasc\Repo\CustomFieldRepo;
use Nasc\Repo\CustomGroupRepo;
use Nasc\Repo\OptionGroupRepo;

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
     * @var OptionGroupRepo
     */
    private $optionGroupRepo;

    /**
     * @param CustomGroupRepo $groupRepo
     * @param CustomFieldRepo $fieldRepo
     * @param OptionGroupRepo $optionGroupRepo
     */
    public function __construct(
        CustomGroupRepo $groupRepo,
        CustomFieldRepo $fieldRepo,
        OptionGroupRepo $optionGroupRepo
    ) {
        $this->groupRepo = $groupRepo;
        $this->fieldRepo = $fieldRepo;
        $this->optionGroupRepo = $optionGroupRepo;
    }

    public function deleteByName(string $name)
    {
        $group = $this->groupRepo->findOneBy(['name' => $name]);
        if ($group) {
            $this->delete((int)$group['id']);
        }
    }

    public function delete(int $id)
    {
        $fields = $this->fieldRepo->findBy(['custom_group_id' => $id]);
        foreach ($fields as $field) {
            $this->ensureNonDeletionOfSystemGroups($field);
            $this->fieldRepo->delete((int)$field['id']);
        }
        $this->groupRepo->delete($id);
    }

    private function ensureNonDeletionOfSystemGroups(array $field)
    {
        $optionGroupId = $field['option_group_id'];
        if (!$optionGroupId) {
            return;
        }
        $group = $this->optionGroupRepo->findOneBy(['id' => $optionGroupId]);
        // these are the groups used by our custom fields that are also system groups
        $protectedNames = ['languages'];
        if (!in_array($group['name'], $protectedNames)) {
            return;
        }

        // set the option group ID to null so it won't be deleted when we delete the field
        $this->fieldRepo->create(['id' => $field['id'], 'option_group_id' => null]);
    }
}