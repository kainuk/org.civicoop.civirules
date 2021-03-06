<?php

class CRM_CivirulesConditions_Participant_ParticipantRole extends CRM_Civirules_Condition {

  private $conditionParams = array();

  /**
   * Method to set the Rule Condition data
   *
   * @param array $ruleCondition
   * @access public
   */
  public function setRuleConditionData($ruleCondition) {
    parent::setRuleConditionData($ruleCondition);
    $this->conditionParams = array();
    if (!empty($this->ruleCondition['condition_params'])) {
      $this->conditionParams = unserialize($this->ruleCondition['condition_params']);
    }
  }

  /**
   * Method to determine if the condition is valid
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool
   */

  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $isConditionValid = FALSE;
    $participant = $triggerData->getEntityData('Participant');
    $participant_role_ids = explode(CRM_Core_DAO::VALUE_SEPARATOR, $participant['participant_role_id']);
    foreach($participant_role_ids as $participant_role_id) {
      switch ($this->conditionParams['operator']) {
        case 0:
          if (in_array($participant_role_id, $this->conditionParams['participant_role_id'])) {
            $isConditionValid = TRUE;
          }
          break;
        case 1:
          if (!in_array($participant_role_id, $this->conditionParams['participant_role_id'])) {
            $isConditionValid = TRUE;
          }
          break;
      }
    }
    return $isConditionValid;
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a condition
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleConditionId
   * @return bool|string
   * @access public
   * @abstract
   */
  public function getExtraDataInputUrl($ruleConditionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/condition/participant_role', 'rule_condition_id='.$ruleConditionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   * @throws Exception
   */
  public function userFriendlyConditionParams() {
    $friendlyText = "";
    if ($this->conditionParams['operator'] == 0) {
      $friendlyText = 'Participant Role is one of: ';
    }
    if ($this->conditionParams['operator'] == 1) {
      $friendlyText = 'Participant Role is NOT one of: ';
    }
    $roleText = array();
    $participantRoles = civicrm_api3('OptionValue', 'get', array(
      'value' => array('IN' => $this->conditionParams['participant_role_id']),
      'option_group_id' => 'participant_role',
      'options' => array('limit' => 0)
    ));
    foreach($participantRoles['values'] as $role) {
      $roleText[] = $role['label'];
    }

    if (!empty($roleText)) {
      $friendlyText .= implode(", ", $roleText);
    }
    return $friendlyText;
  }

  /**
   * Returns an array with required entity names
   *
   * @return array
   * @access public
   */
  public function requiredEntities() {
    return array('Participant');
  }

}