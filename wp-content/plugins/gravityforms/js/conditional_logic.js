
function gf_apply_rules(formId, fields, isInit){
    for(var i=0; i < fields.length; i++)
        gf_apply_field_rule(formId, fields[i], isInit);
}

function gf_apply_field_rule(formId, fieldId, isInit){

    var conditionalLogic = window["gf_form_conditional_logic"][formId]["logic"][fieldId];

    var action = gf_get_field_action(formId, conditionalLogic["section"]);

    //If section is hidden, always hide field. If section is displayed, see if field is supposed to be displayed or hidden
    if(action != "hide")
        action = gf_get_field_action(formId, conditionalLogic["field"]);

    gf_do_field_action(formId, action, fieldId, isInit);
}

function gf_get_field_action(formId, conditionalLogic){
    if(!conditionalLogic)
        return "show";

    var matches = 0;
    for(var i = 0; i < conditionalLogic["rules"].length; i++){
        var rule = conditionalLogic["rules"][i];
        if( (rule["operator"] == "is" && gf_is_value_selected(formId, rule["fieldId"], rule["value"])) || (rule["operator"] == "isnot" && !gf_is_value_selected(formId, rule["fieldId"], rule["value"])) )
            matches++;
    }

    var action;
    if( (conditionalLogic["logicType"] == "all" && matches == conditionalLogic["rules"].length) || (conditionalLogic["logicType"] == "any"  && matches > 0) )
        action = conditionalLogic["actionType"];
    else
        action = conditionalLogic["actionType"] == "show" ? "hide" : "show";

    return action;
}

function gf_is_value_selected(formId, fieldId, value){
    var inputs = jQuery("#input_" + formId + "_" + fieldId + " input");
    if(inputs.length > 0){
        for(var i=0; i< inputs.length; i++){
            if(jQuery(inputs[i]).val() == value && jQuery(inputs[i]).is(":checked"))
                return true;
        }
    }
    else{
        if(jQuery("#input_" + formId + "_" + fieldId).val() == value)
            return true;
    }

    return false;
}

function gf_do_field_action(formId, action, fieldId, isInit){
    var conditional_logic = window["gf_form_conditional_logic"][formId];
    var dependent_fields = conditional_logic["dependents"][fieldId];

    for(var i=0; i < dependent_fields.length; i++){
        var targetId = fieldId == 0 ? "#gform_submit_button_" + formId : "#field_" + formId + "_" + dependent_fields[i];

        if(action == "show"){
            if(conditional_logic["animation"] && !isInit)
                jQuery(targetId).slideDown();
            else
                jQuery(targetId).show();

        }
        else{
            if(conditional_logic["animation"] && !isInit)
                jQuery(targetId).slideUp();
            else
                jQuery(targetId).hide();
        }
    }
}
