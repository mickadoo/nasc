<?php

/**
 * We want "Intervention", "Number of people", "Outcomes (by count)"
 * This is a total mess of a report because I can't find how to effectively show stats for options in a custom field
 * in a report, e.g. show the number of unique people who had an activity with intervention "Support with school entry"
 */
class CRM_Nasc_Form_Report_InterventionReport extends CRM_Report_Form
{
    const COL_KEY_PERSON_COUNT = 'civicrm_option_value_personCount';
    const COL_KEY_OUTCOMES = 'civicrm_option_value_outcomes';

    protected $_groupFilter = false;

    public function __construct()
    {
        $interventionGroup = civicrm_api3('OptionGroup', 'getsingle', ['name' => 'intervention']);

        parent::__construct();
        $this->_columns = [
            'civicrm_option_value' => [
                'dao' => CRM_Core_DAO_OptionValue::class,
                'fields' => [
                    'label' => [
                        'title' => ts('Label'),
                        'required' => TRUE,
                    ],
                    'value' => [
                        'title' => ts('Value'),
                        'required' => TRUE,
                        'type' => CRM_Utils_Type::T_STRING,
                        'no_display' => TRUE,
                    ],
                    'personCount' => [
                        'title' => ts('Num Clients'),
                        'required' => FALSE,
                        'type' => CRM_Utils_Type::T_STRING,
                        'dbAlias' => 'id' // this is a hack, it will be replaced by count
                    ],
                    'outcomes' => [
                        'title' => ts('Outcome Summary'),
                        'required' => FALSE,
                        'type' => CRM_Utils_Type::T_STRING,
                        'dbAlias' => 'id' // this is a hack, it will be replaced by outcome list
                    ],
                ],
                'filters' => [
                    'option_group_id' => [
                        'title' => ts('Option Group ID'),
                        'type' => CRM_Utils_Type::T_STRING,
                        'operatorType' => CRM_Report_Form::OP_STRING,
                        'default' => $interventionGroup['id'],
                        'no_display' => TRUE,
                    ],
                ],
                'order_bys' => [],
            ],
        ];
    }

    public function from() {
        $this->_from = "FROM civicrm_option_value " . $this->_aliases['civicrm_option_value'];
    }

    public function alterDisplay(&$rows)
    {
        if ($this->wasIncludedInSelect($rows, self::COL_KEY_PERSON_COUNT)) {
            $this->addPersonCount($rows);
        }

        if ($this->wasIncludedInSelect($rows, self::COL_KEY_OUTCOMES)) {
            $this->addOutcomeSummary($rows);
        }
    }

    private function addOutcomeSummary(&$rows) : void
    {
        $activities = $this->getRelatedActivities();
        $outcomeKey = $this->getKeyForCustomField('Outcomes');
        $interventionKey = $this->getKeyForCustomField('Intervention');
        $interventionToOutcomeMapping = [];

        foreach ($activities as $activity) {
            $activityInterventions = $activity[$interventionKey];
            $activityOutcomes = $activity[$outcomeKey];
            foreach ($activityInterventions as $interventionVal) {
                if (!isset($interventionToOutcomeMapping[$interventionVal])) {
                    $interventionToOutcomeMapping[$interventionVal] = [];
                }
                foreach ($activityOutcomes as $outcomeVal) {
                    if (!isset($interventionToOutcomeMapping[$interventionVal][$outcomeVal])) {
                        $interventionToOutcomeMapping[$interventionVal][$outcomeVal] = 0;
                    }
                    $interventionToOutcomeMapping[$interventionVal][$outcomeVal]++;
                }
            }
        }

        foreach ($rows as &$row) {
            $interventionVal = (int) $row['civicrm_option_value_value'];
            if (isset($interventionToOutcomeMapping[$interventionVal])) {
                $row[self::COL_KEY_OUTCOMES] = $this->formatOutcomesForRow($interventionToOutcomeMapping[$interventionVal]);
            } else {
                $row[self::COL_KEY_OUTCOMES] = 'none';
            }
        }
    }

    private function formatOutcomesForRow(array $outcomesRaw) : string
    {
        $outcomeOptions = $this->getOutcomeOptions();
        $getOutcomeLabelByVal = function ($val) use ($outcomeOptions) {
            foreach ($outcomeOptions as $outcome) {
                if ($outcome['value'] == $val) {
                    return $outcome['label'];
                }
            }
            return '';
        };

        $output = "";
        foreach ($outcomesRaw as $outcomeVal => $count) {
            $output .= sprintf('%s (%d),', $getOutcomeLabelByVal($outcomeVal), $count);
        }

        return rtrim($output, ',');
    }

    private function getOutcomeOptions() : array
    {
        $repo = Civi::container()->get(\Nasc\Repo\OptionValueRepo::class);

        return $repo->findBy(['option_group_id' => 'outcomes']);
    }

    private function addPersonCount(&$rows) : void
    {
        $activities = $this->getRelatedActivities();
        $recipients = $this->calculateInterventionRecipients($activities);
        foreach ($rows as &$row) {
            $interventionVal = (int) $row['civicrm_option_value_value'];
            if (isset($recipients[$interventionVal])) {
                $row[self::COL_KEY_PERSON_COUNT] = count($recipients[$interventionVal]);
            } else {
                $row[self::COL_KEY_PERSON_COUNT] = '0';
            }
        }
    }

    private function wasIncludedInSelect($rows, $field) : bool
    {
        $firstRow = reset($rows);

        return isset($firstRow[$field]);
    }

    private function getRelatedActivities()
    {
        $interventionKey = $this->getKeyForCustomField('Intervention');
        $outcomeKey = $this->getKeyForCustomField('Outcomes');

        return civicrm_api3('Activity', 'get', [
            'return' => [$interventionKey, $outcomeKey, 'source_contact_id', 'target_contact_id'],
            $interventionKey => ['IS NOT NULL' => 1],
        ])['values'];
    }

    private function getKeyForCustomField($fieldName)
    {
        $fieldRepo = Civi::container()->get(\Nasc\Repo\CustomFieldRepo::class);
        $field = $fieldRepo->findOneBy(['name' => $fieldName]);

        return 'custom_' . $field['id'];
    }

    /**
     * Maps intervention value to the recipient IDs for it
     *
     * @param array $activities
     * @return array
     */
    public function calculateInterventionRecipients(array $activities): array
    {
        $interventionRecipients = [];
        $interventionKey = $this->getKeyForCustomField('Intervention');

        foreach ($activities as $activity) {
            $activityInterventions = $activity[$interventionKey];
            $attendeeIds = $activity['target_contact_id'];
            foreach ($activityInterventions as $interventionVal) {
                if (!isset($interventionRecipients[$interventionVal])) {
                    $interventionRecipients[$interventionVal] = [];
                }
                foreach ($attendeeIds as $attendeeId) {
                    if (!in_array($attendeeId, $interventionRecipients[$interventionVal])) {
                        $interventionRecipients[$interventionVal][] = $attendeeId;
                    }
                }
            }
        }

        return $interventionRecipients;
    }
}
