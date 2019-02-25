<?php

namespace Nasc\Hook\Post;

class RecurringActivityCustomDataCopier
{
    /**
     * @param $op
     * @param $objectName
     * @param $objectId
     * @param \CRM_Core_DAO_RecurringEntity $objectRef
     * @return bool
     */
    public function applies($op, $objectName, $objectId, $objectRef)
    {
        return $objectName === 'RecurringEntity' && $op === 'create' && $objectRef->entity_table === 'civicrm_activity';
    }

    /**
     * @param $op
     * @param $objectName
     * @param $objectId
     * @param \CRM_Core_DAO_RecurringEntity $objectRef
     * @return void
     */
    public function apply($op, $objectName, $objectId, $objectRef)
    {
        $parentId = $objectRef->parent_id;
        $childId = $objectRef->entity_id;
        $customFieldService = \Civi::container()->get(\Nasc\Service\CustomFieldService::class);
        $customFields = $customFieldService->findAllByGroupName('Interventions');
        $customParams = [];
        foreach ($customFields as $customField) {
            $customParams[] = 'custom_' . $customField['id'];
        }
        $getParams = [];
        $getParams['return'] = $customParams;
        $getParams['id'] = $parentId;
        $parent = civicrm_api3('Activity', 'getsingle', $getParams);

        $editParams = ['id' => $childId];
        foreach ($customParams as $customParam) {
            $editParams[$customParam] = $parent[$customParam];
        }

        // copy the custom data from parent to child
        civicrm_api3('Activity', 'create', $editParams);
    }
}