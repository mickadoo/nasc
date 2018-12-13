<?php

namespace Nasc\Setup\Step;

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
     * @param \CRM_Utils_Migrate_Import $importer
     * @param CustomGroupService $customGroupService
     */
    public function __construct(\CRM_Utils_Migrate_Import $importer, CustomGroupService $customGroupService)
    {
        $this->importer = $importer;
        $this->customGroupService = $customGroupService;
    }

    public function apply()
    {
        $files = glob(NASC_EXT_ROOT . '/xml/*_install.xml');
        if (is_array($files)) {
            foreach ($files as $file) {
                $this->importer->run($file);
            }
        }
    }

    public function remove()
    {
        $this->customGroupService->deleteByName('Additional_Contact_Information');
    }
}