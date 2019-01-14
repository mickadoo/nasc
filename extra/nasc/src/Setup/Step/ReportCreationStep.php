<?php

namespace Nasc\Setup\Step;

use Nasc\Repo\OptionValueRepo;
use Nasc\Repo\ReportInstanceRepo;
use CRM_Nasc_Form_Report_InterventionReport as InterventionReport;

class ReportCreationStep implements StepInterface
{
    /**
     * @var OptionValueRepo
     */
    private $optionValueRepo;

    /**
     * @var ReportInstanceRepo
     */
    private $reportInstanceRepo;

    /**
     * @param OptionValueRepo $optionValueRepo
     * @param ReportInstanceRepo $reportInstanceRepo
     */
    public function __construct(OptionValueRepo $optionValueRepo, ReportInstanceRepo $reportInstanceRepo)
    {
        $this->optionValueRepo = $optionValueRepo;
        $this->reportInstanceRepo = $reportInstanceRepo;
    }

    public function apply()
    {
        $this->createReportTemplate();
    }

    public function remove()
    {
        $template = $this->getReportTemplate();
        if ($template) {
            $this->optionValueRepo->delete($template['id']);
        }
    }

    private function createReportTemplate()
    {
        $reportTemplate = $this->getReportTemplate();

        if ($reportTemplate) {
            return;
        }

        $this->optionValueRepo->create([
            'version' => 3,
            'label' => 'NASC Intervention Report',
            'description' => 'Intervention Report for clients, showing number of people receiving interventions, and the outcomes',
            'name' => InterventionReport::class,
            'value' => 'nasc/intervention_report',
            'component_id' => 'CiviMember',
            'option_group_id' => 'report_template'
        ]);
    }

    /**
     * @return array|null
     */
    private function getReportTemplate()
    {
        return $this->optionValueRepo->findOneBy([
            'option_group_id' => 'report_template',
            'name' => InterventionReport::class
        ]);
    }
}