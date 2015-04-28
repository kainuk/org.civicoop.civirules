{* block for rule condition data *}
<h3>{$ruleActionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_action-block">
  <div class="crm-section">
    <div class="label">{$form.rule_action_select.label}</div>
    <div class="content">{$form.rule_action_select.html}</div>
    <div class="clear"></div>
  </div>
</div>
<h3>{ts}Delay action{/ts}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_action_delay-block">
    <div class="crm-section">
        <div class="label">{$form.delay_select.label}</div>
        <div class="content">{$form.delay_select.html}</div>
        <div class="clear"></div>
    </div>
    {foreach from=$delayClasses item=delayClass}
        <div class="crm-section crm-delay-class" id="{$delayClass->getName()}">
            <div class="label"></div>
            <div class="content"><strong>{$delayClass->getDescription()}</strong></div>
            <div class="clear"></div>
            {include file=$delayClass->getTemplateFilename()}
        </div>
    {/foreach}
</div>
<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

{literal}
<script type="text/javascript">
cj(function() {
    cj('select#delay_select').change(triggerDelayChange);

    triggerDelayChange();
});

function triggerDelayChange() {
    cj('.crm-delay-class').css('display', 'none');
    var val = cj('#delay_select').val();
    if (val) {
        cj('#'+val).css('display', 'block');
    }
}
</script>
{/literal}
