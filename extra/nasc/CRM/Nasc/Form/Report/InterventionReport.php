<?php

/**
 * We want "Intervention", "Number of people", "Outcomes (by count)"
 */
class CRM_Nasc_Form_Report_InterventionReport extends CRM_Report_Form_Activity
{
    protected $_customGroupExtends = ['Activity'];

    public function __construct()
    {
        parent::__construct();
        $this->unsetUnusedColumns();

    }

    public function unsetUnusedColumns(): void
    {
        unset($this->_columns['civicrm_email']);
        unset($this->_columns['civicrm_phone']);
        // to avoid warning about missing indices
        $this->_columns['civicrm_address'] = ['order_bys' => []];
        unset($this->_columns['civicrm_activity']['fields']['priority_id']);
        unset($this->_columns['civicrm_activity']['filters']['priority_id']);
        unset($this->_columns['civicrm_activity']['fields']['location']);
        unset($this->_columns['civicrm_activity']['filters']['location']);
        unset($this->_columns['civicrm_activity']['fields']['duration']);
        unset($this->_columns['civicrm_activity']['fields']['details']);
        unset($this->_columns['civicrm_activity']['filters']['details']);
        unset($this->_columns['civicrm_contact']['filters']['current_user']);
        unset($this->_columns['civicrm_group']);
    }
}
