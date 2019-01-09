<?php

namespace Nasc\Setup\Step;

use Nasc\Repo\ContactTypeRepo;

class ContactSubtypeSetupStep implements StepInterface
{
    /**
     * @var ContactTypeRepo
     */
    private $contactTypeRepo;

    /**
     * @param ContactTypeRepo $contactTypeRepo
     */
    public function __construct(ContactTypeRepo $contactTypeRepo)
    {
        $this->contactTypeRepo = $contactTypeRepo;
    }

    public function apply()
    {
        foreach ($this->getTypesToCreate() as $name => $label) {
            $this->contactTypeRepo->create([
                'parent_id' => 'Individual',
                'name' => $name,
                'label' => $label,
            ]);
        }
    }

    public function remove()
    {
        foreach ($this->getTypesToCreate() as $name => $label) {
            $contactType = $this->contactTypeRepo->findOneBy(['name' => $name]);
            if ($contactType) {
                $this->contactTypeRepo->delete($contactType['id']);
            }
        }
    }

    private function getTypesToCreate()
    {
        return [
            'staff' => 'Staff',
            'client' => 'Client',
        ];
    }
}