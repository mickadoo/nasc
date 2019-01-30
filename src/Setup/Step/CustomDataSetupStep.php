<?php

namespace Nasc\Setup\Step;

use Nasc\Repo\CustomFieldRepo;
use Nasc\Service\CustomGroupService;

class CustomDataSetupStep implements StepInterface
{
    /**
     * @var \CRM_Utils_Migrate_Import
     */
    private $importer;

    /**
     * @var CustomGroupService
     */
    private $customGroupService;

    /**
     * @var CustomFieldRepo
     */
    private $customFieldRepo;

    /**
     * @param \CRM_Utils_Migrate_Import $importer
     * @param CustomGroupService $customGroupService
     * @param CustomFieldRepo $customFieldRepo
     */
    public function __construct(
        \CRM_Utils_Migrate_Import $importer,
        CustomGroupService $customGroupService,
        CustomFieldRepo $customFieldRepo
    ) {
        $this->importer = $importer;
        $this->customGroupService = $customGroupService;
        $this->customFieldRepo = $customFieldRepo;
    }

    public function apply()
    {
        $files = glob(NASC_EXT_ROOT . '/xml/*_install.xml');
        if (is_array($files)) {
            foreach ($files as $file) {
                $this->importer->run($file);
            }
        }

        $this->fixLinkToLanguagesOptionGroup();
    }

    public function remove()
    {
        $this->customGroupService->deleteByName('Additional_Contact_Information');
        $this->customGroupService->deleteByName('Contact_Log_Information');
    }

    /**
     * When importing using a system option group it seems that Civi will not set it correctly
     */
    private function fixLinkToLanguagesOptionGroup()
    {
        $field = $this->customFieldRepo->findOneBy(['name' => 'Spoken_Languages']);
        if (!$field) {
            return;
        }
        $field['option_group_id'] = 'languages';
        $this->customFieldRepo->create($field);
    }
}