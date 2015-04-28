<?php
/**
 * Form controller class to manage CiviRule/RuleAction
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
require_once 'CRM/Core/Form.php';

class CRM_Civirules_Form_RuleAction extends CRM_Core_Form {

  protected $ruleId = NULL;

  /**
   * Function to buildQuickForm (extends parent function)
   *
   * @access public
   */
  function buildQuickForm() {
    $this->setFormTitle();
    $this->createFormElements();
    parent::buildQuickForm();
  }

  /**
   * Function to perform processing before displaying form (overrides parent function)
   *
   * @access public
   */
  function preProcess() {
    $this->ruleId = CRM_Utils_Request::retrieve('rid', 'Integer');
    $redirectUrl = CRM_Utils_System::url('civicrm/civirule/form/rule', 'action=update&id='.$this->ruleId, TRUE);
    $session = CRM_Core_Session::singleton();
    $session->pushUserContext($redirectUrl);
    if ($this->_action == CRM_Core_Action::DELETE) {
      $ruleActionId = CRM_Utils_Request::retrieve('id', 'Integer');
      CRM_Civirules_BAO_RuleAction::deleteWithId($ruleActionId);
      CRM_Utils_System::redirect($redirectUrl);
    }
  }

  /**
   * Function to perform post save processing (extends parent function)
   *
   * @access public
   */
  function postProcess() {

    $saveParams = array(
      'rule_id' => $this->_submitValues['rule_id'],
      'action_id' => $this->_submitValues['rule_action_select'],
      'delay' => 'null',
    );

    if (!empty($this->_submitValues['delay_select'])) {
      $delayClass = CRM_Civirules_Delay_Factory::getDelayClassByName($this->_submitValues['delay_select']);
      $delayClass->setValues($this->_submitValues);
      $saveParams['delay'] = serialize($delayClass);
    }

    $ruleAction = CRM_Civirules_BAO_RuleAction::add($saveParams);

    $session = CRM_Core_Session::singleton();
    $session->setStatus('Action added to CiviRule '.CRM_Civirules_BAO_Rule::getRuleLabelWithId($this->_submitValues['rule_id']),
      'Action added', 'success');

    $action = CRM_Civirules_BAO_Action::getActionObjectById($ruleAction['action_id'], true);
    $redirectUrl = $action->getExtraDataInputUrl($ruleAction['id']);
    if (empty($redirectUrl)) {
      $redirectUrl = CRM_Utils_System::url('civicrm/civirule/form/rule', 'action=update&id=' . $this->_submitValues['rule_id'], TRUE);
    }

    CRM_Utils_System::redirect($redirectUrl);
  }

  /**
   * Function to add the form elements
   *
   * @access protected
   */
  protected function createFormElements() {
    $this->add('hidden', 'rule_id');
    $actionList = array(' - select - ') + CRM_Civirules_Utils::buildActionList();
    asort($actionList);
    $this->add('select', 'rule_action_select', ts('Select Action'), $actionList);

    $delayList = array(' - No Delay - ') + CRM_Civirules_Delay_Factory::getOptionList();
    $this->add('select', 'delay_select', ts('Delay action to'), $delayList);
    foreach(CRM_Civirules_Delay_Factory::getAllDelayClasses() as $delay_class) {
      $delay_class->addElements($this);
    }
    $this->assign('delayClasses', CRM_Civirules_Delay_Factory::getAllDelayClasses());

    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));
  }

  public function setDefaultValues() {
    $defaults['rule_id'] = $this->ruleId;

    foreach(CRM_Civirules_Delay_Factory::getAllDelayClasses() as $delay_class) {
      $delay_class->setDefaultValues($defaults);
    }

    return $defaults;
  }

  /**
   * Function to set the form title based on action and data coming in
   *
   * @access protected
   */
  protected function setFormTitle() {
    $title = 'CiviRules Add Action';
    $this->assign('ruleActionHeader', 'Add Action to CiviRule '.CRM_Civirules_BAO_Rule::getRuleLabelWithId($this->ruleId));
    CRM_Utils_System::setTitle($title);
  }

  /**
   * Function to add validation action rules (overrides parent function)
   *
   * @access public
   */
  public function addRules() {
    $this->addFormRule(array('CRM_Civirules_Form_RuleAction', 'validateRuleAction'));
  }

  /**
   * Function to validate value of rule action form
   *
   * @param array $fields
   * @return array|bool
   * @access public
   * @static
   */
  static function validateRuleAction($fields) {
    $errors = array();
    if (isset($fields['rule_action_select']) && empty($fields['rule_action_select'])) {
      $errors['rule_action_select'] = ts('Action has to be selected, press CANCEL if you do not want to add an action');
    }
    if (!empty($fields['delay_select'])) {
      $delayClass = CRM_Civirules_Delay_Factory::getDelayClassByName($fields['delay_select']);
      $delayClass->validate($fields, $errors);
    }

    if (count($errors)) {
      return $errors;
    }

    return TRUE;
  }
}
