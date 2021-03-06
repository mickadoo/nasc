<?php

namespace Nasc\Setup\Step;

use Nasc\Repo\DashboardRepo;
use Nasc\Repo\NavigationRepo;
use Nasc\Repo\OptionGroupRepo;
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
     * @var NavigationRepo
     */
    private $navigationRepo;

    /**
     * @var OptionGroupRepo
     */
    private $optionGroupRepo;

    /**
     * @var DashboardRepo
     */
    private $dashboardRepo;

    /**
     * @param OptionValueRepo $optionValueRepo
     * @param ReportInstanceRepo $reportInstanceRepo
     * @param NavigationRepo $navigationRepo
     * @param OptionGroupRepo $optionGroupRepo
     * @param DashboardRepo $dashboardRepo
     */
    public function __construct(
        OptionValueRepo $optionValueRepo,
        ReportInstanceRepo $reportInstanceRepo,
        NavigationRepo $navigationRepo,
        OptionGroupRepo $optionGroupRepo,
        DashboardRepo $dashboardRepo
    ) {
        $this->optionValueRepo = $optionValueRepo;
        $this->reportInstanceRepo = $reportInstanceRepo;
        $this->navigationRepo = $navigationRepo;
        $this->optionGroupRepo = $optionGroupRepo;
        $this->dashboardRepo = $dashboardRepo;
    }

    public function apply()
    {
        $template = $this->createReportTemplate();
        $this->createReportInstance($template);
    }

    public function remove()
    {
        $template = $this->getReportTemplate();
        if (!$template) {
            return;
        }

        $this->optionValueRepo->delete($template['id']);
        $instances = $this->reportInstanceRepo->findBy([
            'report_id' => $template['value']
        ]);
        foreach ($instances as $instance) {
            $this->reportInstanceRepo->delete($instance['id']);
        }

        $navigation = $this->findNavigation();
        if ($navigation) {
            $this->navigationRepo->delete($navigation['id']);
        }

        $dashboard = $this->getDashboardEntry();
        if ($dashboard) {
            $this->dashboardRepo->delete($dashboard['id']);
        }
    }

    private function createReportInstance($template)
    {
        $instance = $this->reportInstanceRepo->findOneBy([
            'report_id' => $template['value'],
            'title' => 'NASC Monthly Intervention Report',
        ]);

        if ($instance) {
            return;
        }

        $interventionGroup = $this->optionGroupRepo->findOneBy(['name' => 'intervention']);

        $instance = $this->reportInstanceRepo->create([
            'report_id' => $template['value'],
            'title' => 'NASC Monthly Intervention Report',
            'description' => 'Summary of interventions this month',
            'permission' => 'view all contacts',
        ]);

        $formVals = [
            'fields' => [
                'label' => 1,
                'personCount' => 1,
                'outcomes' => 1,
            ],
            'option_group_id_op' => 'has',
            'option_group_id_value' => $interventionGroup['id'],
            'activity_date_relative' => 'this.month',
            'view_mode' => 'view',
            'cache_minutes' => 60,
            'addToDashboard' => 1,
            'permission' => 'view all contacts',
            'parent_id' => 0,
            'instance_id' => $instance['id'],
        ];

        $this->reportInstanceRepo->create([
            'id' => $instance['id'],
            'form_values' => serialize($formVals)
        ]);

        $this->createNavigation($instance['id']);
        $this->createDashboardEntry($instance['id']);
    }

    private function createDashboardEntry($instanceId)
    {
        $existing = $this->getDashboardEntry();
        if ($existing) {
            return;
        }

        $params = [
            'name' => 'nasc_monthly_intervention_report',
            'label' => 'NASC Monthly Intervention Report',
            'url' => sprintf('civicrm/report/instance/%d?reset=1&section=2&context=dashlet', $instanceId),
            'permission' => 'view all contacts',
            'fullscreen_url' => sprintf('civicrm/report/instance/%d?reset=1&section=2&context=dashletFullscreen', $instanceId),
            'is_active' => 1,
            'cache_minutes' => 60
        ];

        $this->dashboardRepo->create($params);
    }

    private function getDashboardEntry()
    {
        return $this->dashboardRepo->findOneBy(['name' => 'nasc_monthly_intervention_report']);
    }

    private function createNavigation($instanceId)
    {
        $existing = $this->findNavigation();
        if ($existing) {
            return;
        }

        $this->navigationRepo->create([
            'parent_id' => 'Reports',
            'name' => 'NASC Monthly Intervention Report',
            'label' => 'NASC Monthly Intervention Report',
            'url' => sprintf('civicrm/report/instance/%d?reset=1&force=1', $instanceId),
            'is_active' => 1,
        ]);
    }

    private function findNavigation()
    {
        return $this->navigationRepo->findOneBy(['name' => 'NASC Monthly Intervention Report']);
    }

    private function createReportTemplate()
    {
        $reportTemplate = $this->getReportTemplate();

        if ($reportTemplate) {
            return $reportTemplate;
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

        // the above call does not actually return the full template :-(
        return $this->getReportTemplate();
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