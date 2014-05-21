<?php

class GFFormDisplay{

    public static function get_form($form_id, $display_title=true, $display_description=true, $force_display=false, $field_values=null, $ajax=false){

        //reading form metadata
        $form = RGFormsModel::get_form_meta($form_id, true);
        $form = RGFormsModel::add_default_properties($form);

        //disable ajax if form has a reCAPTCHA field (not supported).
        if($ajax && self::has_recaptcha_field($form))
            $ajax = false;

        //adds honeypot field if configured
        if($form["enableHoneypot"])
            $form["fields"][] = self::get_honeypot_field($form);

        //Fired right before the form rendering process. Allow users to manipulate the form object before it gets displayed in the front end
        $form = apply_filters("gform_pre_render_$form_id", apply_filters("gform_pre_render", $form));

        if($form == null)
            return "<p>" . __("Oops! We could not locate your form.", "gravityforms") . "</p>";

        //calling tab index filter
        GFCommon::$tab_index = apply_filters("gform_tabindex_{$form_id}",apply_filters("gform_tabindex", 1, $form), $form);

        //Don't display inactive forms
        if(!$force_display){

            $form_info = RGFormsModel::get_form($form_id);
            if(!$form_info->is_active)
                return "";

            //If form has a schedule, make sure it is within the configured start and end dates
            if($form["scheduleForm"]){
                $local_time_start = sprintf("%s %02d:%02d %s", $form["scheduleStart"], $form["scheduleStartHour"], $form["scheduleStartMinute"], $form["scheduleStartAmpm"]);
                $local_time_end = sprintf("%s %02d:%02d %s", $form["scheduleEnd"], $form["scheduleEndHour"], $form["scheduleEndMinute"], $form["scheduleEndAmpm"]);
                $timestamp_start = strtotime($local_time_start . ' +0000');
                $timestamp_end = strtotime($local_time_end . ' +0000');
                $now = current_time("timestamp");

                if( (!empty($form["scheduleStart"]) && $now < $timestamp_start) || (!empty($form["scheduleEnd"]) && $now > $timestamp_end))
                    return  empty($form["scheduleMessage"]) ? "<p>" . __("Sorry. This form is no longer available.", "gravityforms") . "</p>" : "<p>" . $form["scheduleMessage"] . "</p>";
            }

            //If form has a limit of entries, check current entry count
            if($form["limitEntries"]) {
                $entry_count = RGFormsModel::get_lead_count($form_id, "");
                if($entry_count >= $form["limitEntriesCount"])
                    return  empty($form["limitEntriesMessage"]) ? "<p>" . __("Sorry. This form is no longer accepting new submissions.", "gravityforms"). "</p>" : "<p>" . $form["limitEntriesMessage"] . "</p>";
            }
        }

        $form_string = "";

        //When called via a template, this will enqueue the proper scripts
        //When called via a shortcode, this will be ignored (too late to enqueue), but the scripts will be enqueued via the enqueue_scripts event
        self::enqueue_form_scripts($form, $ajax);

        //handling postback if form was submitted
        $is_postback = isset($_POST["is_submit_" . $form_id]);
        $is_valid = true;
        if($is_postback){

            $is_valid = self::validate($form, $field_values);
            if($is_valid){

                $failed_honeypot = $form["enableHoneypot"] && !self::validate_honeypot($form);
                if($failed_honeypot){
                    //display confirmation but doesn't process the form when honeypot fails
                    $confirmation_message = self::handle_confirmation($form, $lead, $ajax);
                }
                else{
                    //pre submission action
                    do_action("gform_pre_submission", $form);
                    do_action("gform_pre_submission_{$form["id"]}", $form);

                    //pre submission filter
                    $form = apply_filters("gform_pre_submission_filter_{$form["id"]}", apply_filters("gform_pre_submission_filter", $form));

                    //handle submission
                    $lead = array();
                    $confirmation_message = self::handle_submission($form, $lead, $ajax);

                    //post submission hook
                    do_action("gform_post_submission", $lead, $form);
                    do_action("gform_post_submission_{$form["id"]}", $lead, $form);
                }
            }
        }
        else{
            //recording form view. Ignores views from administrators
            if(!current_user_can("administrator")){
                RGFormsModel::insert_form_view($form_id, $_SERVER['REMOTE_ADDR']);
            }
        }

        if(empty($confirmation_message)){
            //Hidding entire form if conditional logic is on to prevent "hidden" fields from blinking. Form will be set to visible in the conditional_logic.php after the rules have been applied.
            $style = self::has_conditional_logic($form) ? "style='display:none'" : "";
            $form_string .= "
                <div class='gform_wrapper' id='gform_wrapper_$form_id' " . $style . ">";

            $action="";
            if(apply_filters("gform_confirmation_anchor", 0)){
                $form_string .="<a name='gf_$form_id' class='gform_anchor' ></a>";
                $action="#gf_$form_id";
            }
            $target = $ajax ? "target='gform_ajax_frame_{$form_id}'" : "";
            $form_string .= apply_filters("gform_form_tag_{$form_id}", apply_filters("gform_form_tag", "<form method='post' enctype='multipart/form-data' {$target} id='gform_$form_id' class='" . $form["cssClass"] . "' action='{$action}'>", $form), $form);

            if($display_title || $display_description){
                $form_string .= "
                        <div class='gform_heading'>";
                if($display_title){
                    $form_string .= "
                            <h3 class='gform_title'>" . $form['title'] . "</h3>";
                }
                if($display_description){
                    $form_string .= "
                            <span class='gform_description'>" . $form['description'] ."</span>";
                }
                $form_string .= "
                        </div>";
            }

            if($is_postback && !$is_valid){
                $validation_message = "<div class='validation_error'>" . __("There was a problem with your submission.", "gravityforms") . "<br /> " . __("Errors have been highlighted below ", "gravityforms") . "</div>";
                $form_string .= apply_filters("gform_validation_message_{$form["id"]}", apply_filters("gform_validation_message", $validation_message, $form), $form);
            }

            $form_string .= "
                        <div class='gform_body'>
                            <input type='hidden' class='gform_hidden' name='is_submit_$form_id' value='1'/>
                            <ul id='gform_fields_$form_id' class='gform_fields " . $form['labelPlacement'] . "'>";

                                if(is_array($form['fields']))
                                {
                                    foreach($form['fields'] as $field){
                                        $field["conditionalLogicFields"] = self::get_conditional_logic_fields($form, $field["id"]);
                                        $form_string .= self::get_field($field, RGFormsModel::get_field_value($field, $field_values), false, $form, $field_values);
                                    }
                                }
            $form_string .= "
                            </ul>
                        </div>
                        <div class='gform_footer " . $form['labelPlacement'] . "'>";

                        $tabindex = GFCommon::get_tabindex();
                        if($form["button"]["type"] == "text" || empty($form["button"]["imageUrl"])){
                            $button_text = empty($form["button"]["text"]) ? __("Submit", "gravityforms") : $form["button"]["text"];
                            $button_input = "<input type='submit' id='gform_submit_button_{$form["id"]}' class='button' value='" . esc_attr($button_text) . "' $tabindex/>";
                        }
                        else{
                            $imageUrl = $form["button"]["imageUrl"];
                            $button_input= "<input type='image' src='$imageUrl' id='gform_submit_button_{$form["id"]}' alt='submit' $tabindex/>";
                        }

                        $button_input = apply_filters("gform_submit_button", $button_input, $form);
                        $button_input = apply_filters("gform_submit_button_$form_id", $button_input, $form);
                        $form_string .= $button_input;
                        if(current_user_can("gform_full_access"))
                            $form_string .= "<div class='gform_edit_link'><a href='" . get_bloginfo("wpurl") . "/wp-admin/admin.php?page=gf_edit_forms&amp;id=" . $form_id . "'>" . __("Edit this form", "gravityforms") . "</a></div>";

                        if($ajax){
                            $spinner_url = apply_filters("gform_ajax_spinner_url", GFCommon::get_base_url() . "/images/spinner.gif");
                            $field_values_str = is_array($field_values) ? http_build_query($field_values) : "";
                            $form_string .="<iframe style='display:none; width:0px; height:0px;' src='about:blank' name='gform_ajax_frame_{$form_id}' id='gform_ajax_frame_{$form_id}'></iframe>" .
                                            "<script type='text/javascript'>" .
                                                "jQuery(document).ready(function(){" .
                                                    "jQuery('#gform_{$form_id}').submit(function(){" .
                                                        "jQuery('#gform_submit_button_{$form_id}').attr('disabled', 'disabled').after('<img id=\"gform_ajax_spinner_{$form_id}\"  class=\"gform_ajax_spinner\" src=\"{$spinner_url}\" />');" .

                                                        "jQuery('#gform_ajax_frame_{$form_id}').load( function(){" .
                                                            "var form_content = jQuery(this).contents().find('#gform_wrapper_{$form_id}');" .
                                                            "var confirmation_content = jQuery(this).contents().find('#gforms_confirmation_message');" .
                                                            "jQuery('#gform_submit_button_{$form_id}').removeAttr('disabled');" .
                                                            "if(form_content.length > 0){" .
                                                                "jQuery('#gform_wrapper_{$form_id}').html(form_content.html());" .
                                                                "gformInitDatepicker();" .
                                                            "}" .
                                                            "else if(confirmation_content.length > 0){" .
                                                                "setTimeout(function(){jQuery('#gform_wrapper_{$form_id}').replaceWith('<' + 'div id=\'gforms_confirmation_message\'' + '>' + confirmation_content.html() + '<' + '/div' + '>');}, 50);" .
                                                            "}" .
                                                            "else{" .
                                                                "jQuery('#gform_wrapper_{$form_id}').append(jQuery(this).contents().find('*').html());" .
                                                                "gformRedirect();" .
                                                            "}" .

                                                        "});" .
                                                    "});" .
                                                "});" .
                                            "</script>" .
                                            "<input type='hidden' name='gform_ajax' value='form_id={$form_id}&title={$display_title}&description={$display_description}' />" .
                                            "<input type='hidden' name='gform_ajax_field_values' value='{$field_values_str}' />";
                        }

            $form_string .="
                        </div>
                </form>
                </div>";

            if(self::has_conditional_logic($form))
                $form_string .= self::get_conditional_logic($form);
			if($is_postback && $ajax)
                $form_string = "<!DOCTYPE html><html><head><meta charset='UTF-8' /></head><body>" . $form_string . "</body></html>";

			return $form_string;
        }
        else{
            if($ajax)
                $confirmation_message = "<!DOCTYPE html><html><head><meta charset='UTF-8' /></head><body>" . $confirmation_message . "</body></html>";

            return $confirmation_message;
        }
    }

    private static function get_honeypot_field($form){
        $max_id = self::get_max_field_id($form);
        $labels = self::get_honeypot_labels();
        return array("type" => "honeypot", "label" => $labels[rand(0, 3)], "id" => $max_id + 1, "cssClass" => "gform_validation_container", "description" => "This field is for validation purposes and should be left unchanged.");
    }

    private static function get_max_field_id($form){
        $max = 0;
        foreach($form["fields"] as $field){
            if(floatval($field["id"]) > $max)
                $max = floatval($field["id"]);
        }
        return $max;
    }

    private static function get_honeypot_labels(){
        return array("Name", "Email", "Phone", "Comments");
    }

    public static function is_empty($field){
        switch(RGFormsModel::get_input_type($field)){
            case "post_image" :
            case "fileupload" :
                $input_name = "input_" . $field["id"];
                return empty($_FILES[$input_name]['name']);
            break;
        }

        if(is_array($field["inputs"]))
        {
            foreach($field["inputs"] as $input){
                $value = $_POST["input_" . str_replace('.', '_', $input["id"])];
                if(strlen(trim($value)) > 0)
                    return false;
            }
            return true;
        }
        else{
            $value = $_POST["input_" . $field["id"]];
            if(is_array($value)){
                //empty if any of the inputs are empty (for inputs with the same name)
                foreach($value as $input){
                    if(strlen(trim($input)) <= 0 )
                        return true;
                }
                return false;
            }
            else{
                return strlen(trim($value)) <= 0 ? true : false;
            }
        }
    }

    private static function clean_extensions($extensions){
        $count = sizeof($extensions);
        for($i=0; $i<$count; $i++){
            $extensions[$i] = str_replace(".", "",str_replace(" ", "", $extensions[$i]));
        }
        return $extensions;
    }

    private static function validate_range($field, $value){
        if( !GFCommon::is_numeric($value) )
            return false;

        $number = GFCommon::clean_number($value);
        if( (is_numeric($field["rangeMin"]) && $number < $field["rangeMin"]) ||
            (is_numeric($field["rangeMax"]) && $number > $field["rangeMax"])
        )
            return false;
        else
            return true;
    }

    private static function validate_honeypot($form){
        $honeypot_id = self::get_max_field_id($form);
        return empty($_POST["input_{$honeypot_id}"]);
    }

    public static function handle_submission($form, &$lead, $ajax=false){

        //insert submissing in DB
        RGFormsModel::save_lead($form, $lead);

        //reading lead that was just saved
        $lead = RGFormsModel::get_lead($lead["id"]);

        //send auto-responder and notification emails
        self::send_emails($form, $lead);

        //display confirmation message or redirect to confirmation page
        return self::handle_confirmation($form, $lead, $ajax);

    }

    public static function handle_confirmation($form, $lead, $ajax=false){
        if($form["confirmation"]["type"] == "message"){
            $anchor = apply_filters("gform_confirmation_anchor_{$form["id"]}", apply_filters("gform_confirmation_anchor", 0)) ? "<a name='gf_{$form["id"]}' class='gform_anchor' ></a>" : "";
            return "{$anchor}<div id='gforms_confirmation_message'>" . GFCommon::replace_variables($form["confirmation"]["message"], $form, $lead) . "</div>";
        }
        else{
            if(!empty($form["confirmation"]["pageId"])){
                $url = get_permalink($form["confirmation"]["pageId"]);
            }
            else{
                $url_info = parse_url($form["confirmation"]["url"]);
                $query_string = $url_info["query"];
                $dynamic_query = GFCommon::replace_variables($form["confirmation"]["queryString"], $form, $lead, true);
                $query_string .= empty($url_info["query"]) || empty($dynamic_query) ? $dynamic_query : "&" . $dynamic_query;

                if(!empty($url_info["fragment"]))
                    $query_string .= "#" . $url_info["fragment"];

                $url = $url_info["scheme"] . "://" . $url_info["host"] . $url_info["path"] . "?" . $query_string;
            }
            $str = "<script>function gformRedirect(){document.location.href='$url';}";
            if(!$ajax)
                $str .="gformRedirect();";

            $str .="</script>";

            return $str;
        }
    }

    private static function send_email($from, $to, $bcc, $reply_to, $subject, $message){

        //invalid to email address or no content. can't send email
        if(!GFCommon::is_valid_email($to) || (empty($subject) && empty($message)))
            return;

        if(!GFCommon::is_valid_email($from))
            $from = get_bloginfo("admin_email");

        //invalid from address. can't send email
        if(!GFCommon::is_valid_email($from))
            return;

        $headers = "From: \"$from\" <$from> \r\n";
        $headers .= GFCommon::is_valid_email($reply_to) ? "Reply-To: $reply_to\r\n" :"";
        $headers .= GFCommon::is_valid_email($bcc) ? "Bcc: $bcc\r\n" :"";
        $headers .= 'Content-type: text/html; charset=' . get_option('blog_charset') . "\r\n";

        $result = wp_mail($to, $subject, $message, $headers);
    }

    public static function send_emails($form, $lead){
        $form_id = $form["id"];

        //handling autoresponder email
        $to = apply_filters("gform_autoresponder_email_$form_id", apply_filters("gform_autoresponder_email", stripslashes($_POST["input_" . $form["autoResponder"]["toField"]]), $form), $form);
        $subject = GFCommon::replace_variables($form["autoResponder"]["subject"], $form, $lead, false, false);
        $message = GFCommon::replace_variables($form["autoResponder"]["message"], $form, $lead, false, false, !$form["autoResponder"]["disableAutoformat"]);
        self::send_email($form["autoResponder"]["from"], $to, $form["autoResponder"]["bcc"], $form["autoResponder"]["replyTo"], $subject, $message);

        //handling admin notification email
        $subject = GFCommon::replace_variables($form["notification"]["subject"], $form, $lead, false, false);
        $message = GFCommon::replace_variables($form["notification"]["message"], $form, $lead, false, false, !$form["notification"]["disableAutoformat"]);
        $from = empty($form["notification"]["fromField"]) ? $form["notification"]["from"] : stripslashes($_POST["input_" . $form["notification"]["fromField"]]);
        $replyTo = empty($form["notification"]["replyToField"]) ? $form["notification"]["replyTo"] : stripslashes($_POST["input_" . $form["notification"]["replyToField"]]);

        if(empty($form["notification"]["routing"])){
            $email_to = $form["notification"]["to"];
        }
        else{
            $email_to = array();

            foreach($form["notification"]["routing"] as $routing){

                $source_field = RGFormsModel::get_field($form, $routing["fieldId"]);
                $field_value = RGFormsModel::get_field_value($source_field, array());
                $is_value_match = is_array($field_value) ? in_array($routing["value"], $field_value) : $field_value == $routing["value"];

                if( ($routing["operator"] == "is" && $is_value_match ) || ($routing["operator"] == "isnot" && !$is_value_match) )
                    $email_to[] = $routing["email"];
            }

            $email_to = join(",", $email_to);
        }

        //Filters the admin notification email to address. Allows users to change email address before notification is sent
        $to = apply_filters("gform_notification_email_$form_id" , apply_filters("gform_notification_email", $email_to, $lead), $lead);

        self::send_email($from, $to, $form["notification"]["bcc"], $replyTo, $subject, $message);
    }

    public static function validate(&$form, $field_values){

        $is_valid = true;
        foreach($form["fields"] as &$field){

            //ignore validation if field is hidden or admin only
            if(RGFormsModel::is_field_hidden($form, $field, $field_values) || $field["adminOnly"])
                continue;

            $value = RGFormsModel::get_field_value($field);

            //display error message if field is marked as required and the submitted value is empty
            if($field["isRequired"] && self::is_empty($field)){
                $field["failed_validation"] = true;
                $field["validation_message"] = empty($field["errorMessage"]) ? __("This field is required. Please enter a value.", "gravityforms") : $field["errorMessage"];
                $is_valid = false;
            }
            //display error if field does not allow duplicates and the submitted value already exists
            else if($field["noDuplicates"] && RGFormsModel::is_duplicate($form["id"], $field, $value)){
                $field["failed_validation"] = true;
                $field["validation_message"] = is_array($value) ? __("This field requires an unique entry and the values you entered have been already been used", "gravityforms") :  __(sprintf("This field requires an unique entry and '%s' has already been used", $value), "gravityforms");
                $is_valid = false;
            }
            else{
                switch(RGFormsModel::get_input_type($field)){
                    case "name" :
                        if($field["isRequired"] && $field["nameFormat"] != "simple")
                        {
                            $first = $_POST["input_" . $field["id"] . "_3"];
                            $last = $_POST["input_" . $field["id"] . "_6"];
                            if(empty($first) || empty($last)){
                                $field["failed_validation"] = true;
                                $field["validation_message"] = empty($field["errorMessage"]) ? __("This field is required. Please enter the first and last name.", "gravityforms") : $field["errorMessage"];
                                $is_valid = false;
                            }
                        }

                    break;

                    case "address" :
                        if($field["isRequired"])
                        {
                            $street = $_POST["input_" . $field["id"] . "_1"];
                            $city = $_POST["input_" . $field["id"] . "_3"];
                            $state = $_POST["input_" . $field["id"] . "_4"];
                            $zip = $_POST["input_" . $field["id"] . "_5"];
                            $country = $_POST["input_" . $field["id"] . "_6"];
                            if(empty($street) || empty($city) || empty($zip) || (empty($state) && !$field["hideState"] ) || (empty($country) && !$field["hideCountry"])){
                                $field["failed_validation"] = true;
                                $field["validation_message"] = empty($field["errorMessage"]) ? __("This field is required. Please enter a complete address.", "gravityforms") : $field["errorMessage"];
                                $is_valid = false;
                            }
                        }

                    break;

                    case "email" :
                        if(!empty($value) && !GFCommon::is_valid_email($value)){
                            $field["failed_validation"] = true;
                            $field["validation_message"] = empty($field["errorMessage"]) ? __("Please enter a valid email address.", "gravityforms"): $field["errorMessage"];
                            $is_valid = false;
                        }
                    break;

                    case "number" :
                        if(trim($value) != '' && !self::validate_range($field, $value)){
                            $field["failed_validation"] = true;
                            $field["validation_message"] = empty($field["errorMessage"]) ? GFCommon::get_range_message($field) : $field["errorMessage"];

                            $is_valid = false;
                        }
                    break;

                    case "phone" :

                        $regex = '/^\D?(\d{3})\D?\D?(\d{3})\D?(\d{4})$/';
                        if($field["phoneFormat"] == "standard" && !empty($value) && !preg_match($regex, $value)){
                            $field["failed_validation"] = true;
                            if(!empty($field["errorMessage"]))
                                $field["validation_message"] = $field["errorMessage"];
                            $is_valid = false;
                        }
                    break;

                    case "date" :
                        if(is_array($value) && empty($value[0]))
                            $value = null;

                        if(!empty($value)){
                            $format = empty($field["dateFormat"]) ? "mdy" : $field["dateFormat"];
                            $date = GFCommon::parse_date($value, $format);

                            if(empty($date) || !checkdate($date["month"], $date["day"], $date["year"])){
                                $field["failed_validation"] = true;
                                $field["validation_message"] = empty($field["errorMessage"]) ? __(sprintf("Please enter a valid date in the format (%s).", $format == "mdy" ? "mm/dd/yyyy" : "dd/mm/yyyy"), "gravityforms") : $field["errorMessage"];
                                $is_valid = false;
                            }
                        }
                    break;

                    case "time" :

                        //create variable values if time came in one field
                        if(!is_array($value) && !empty($value)){
                            preg_match('/^(\d*):(\d*) (.*)$/', $value, $matches);
                            $value = array();
                            $value[0] = $matches[1];
                            $value[1] = $matches[2];
                        }

                        $hour = $value[0];
                        $minute = $value[1];

                        if(empty($hour) && empty($minute))
                            break;

                        $is_valid_format = is_numeric($hour) && is_numeric($minute);

                        if(!$is_valid_format || $hour <= 0 || $hour > 12 || $minute < 0 || $minute >= 60)
                        {
                            $field["failed_validation"] = true;
                            $field["validation_message"] = empty($field["errorMessage"]) ? __("Please enter a valid time." , "gravityforms"): $field["errorMessage"];
                            $is_valid = false;
                        }
                    break;

                    case "website" :
                        if(empty($value) || $value == "http://"){
                            $value = "";
                            if($field["isRequired"]){
                                $field["failed_validation"] = true;
                                $field["validation_message"] = empty($field["errorMessage"]) ? __("This field is required. Please enter a value.", "gravityforms") : $field["errorMessage"];
                                $is_valid = false;
                            }
                        }

                        if(!empty($value) && !GFCommon::is_valid_url($value)){
                            $field["failed_validation"] = true;
                            $field["validation_message"] = empty($field["errorMessage"]) ? __("Please enter a valid Website URL (i.e. http://www.gravityforms.com).", "gravityforms") : $field["errorMessage"];
                            $is_valid = false;
                        }
                    break;

                    case "captcha" :
                        switch($field["captchaType"]){
                            case "simple_captcha" :
                                if(class_exists("ReallySimpleCaptcha")){
                                    $prefix = $_POST["input_captcha_prefix_{$field["id"]}"];
                                    $captcha_obj = GFCommon::get_simple_captcha();

                                    if(!$captcha_obj->check($prefix, str_replace(" ", "", $value))){
                                        $field["failed_validation"] = true;
                                        $field["validation_message"] = empty($field["errorMessage"]) ? __("The CAPTCHA wasn't entered correctly. Go back and try it again.", "gravityforms") : $field["errorMessage"];
                                        $is_valid = false;
                                    }
                                    //removes current captcha file
                                    $captcha_obj->remove($prefix);

                                    //removes old files in captcha folder (older than 1 hour);
                                    $captcha_obj->cleanup();
                                }
                            break;

                            case "math" :
                                $prefixes = explode(",", $_POST["input_captcha_prefix_{$field["id"]}"]);
                                $captcha_obj = GFCommon::get_simple_captcha();

                                //finding first number
                                $first = 0;
                                for($first=0; $first<10; $first++){
                                    if($captcha_obj->check($prefixes[0], $first))
                                        break;
                                }

                                //finding second number
                                $second = 0;
                                for($second=0; $second<10; $second++){
                                    if($captcha_obj->check($prefixes[2], $second))
                                        break;
                                }

                                //if it is a +, perform the sum
                                if($captcha_obj->check($prefixes[1], "+"))
                                    $result = $first + $second;
                                else
                                    $result = $first - $second;


                                if(intval($result) != intval($value)){
                                    $field["failed_validation"] = true;
                                    $field["validation_message"] = empty($field["errorMessage"]) ? __("The CAPTCHA wasn't entered correctly. Go back and try it again.", "gravityforms") : $field["errorMessage"];
                                    $is_valid = false;
                                }
                                //removes current captcha file
                                $captcha_obj->remove($prefix);

                                //removes old files in captcha folder (older than 1 hour);
                                $captcha_obj->cleanup();

                            break;

                            default :
                                if(!function_exists("recaptcha_get_html")){
                                    require_once(GFCommon::get_base_path() . '/recaptchalib.php');
                                }

                                $privatekey = get_option("rg_gforms_captcha_private_key");
                                $resp = recaptcha_check_answer ($privatekey,
                                        $_SERVER["REMOTE_ADDR"],
                                        $_POST["recaptcha_challenge_field"],
                                        $_POST["recaptcha_response_field"]);

                                if (!$resp->is_valid) {
                                    $field["failed_validation"] = true;
                                    $field["validation_message"] = empty($field["errorMessage"]) ? __("The reCAPTCHA wasn't entered correctly. Go back and try it again.", "gravityforms") : $field["errorMessage"];
                                    $is_valid = false;
                                }
                        }
                    break;

                    case "fileupload" :
                    case "post_image" :
                        $info = pathinfo($_FILES["input_" . $field["id"]]["name"]);
                        $allowedExtensions = self::clean_extensions(explode(",", strtolower($field["allowedExtensions"])));
                        $extension = strtolower($info["extension"]);

                        if(empty($field["allowedExtensions"]) && in_array($extension, array("php", "asp", "exe", "com", "htaccess"))){
                            $field["failed_validation"] = true;
                            $field["validation_message"] = empty($field["errorMessage"]) ? __("The uploaded file type is not allowed.", "gravityforms")  : $field["errorMessage"];
                            $is_valid = false;
                        }
                        else if(!empty($field["allowedExtensions"]) && !empty($info["basename"]) && !in_array($extension, $allowedExtensions)){
                            $field["failed_validation"] = true;
                            $field["validation_message"] = empty($field["errorMessage"]) ? sprintf(__("The uploaded file type is not allowed. Must be one of the following: %s", "gravityforms"), strtolower($field["allowedExtensions"]) )  : $field["errorMessage"];
                            $is_valid = false;
                        }
                    break;
                }
            }
        }

        $validation_result = apply_filters("gform_validation", array("is_valid" => $is_valid, "form" => $form));
        $is_valid = $validation_result["is_valid"];
        $form = $validation_result["form"];

        return $is_valid;
    }

    public static function enqueue_scripts(){
        global $wp_query;
        if(isset($wp_query->posts) && is_array($wp_query->posts)){
            foreach($wp_query->posts as $post){
                $forms = self::get_embedded_forms($post->post_content, $ajax);
                foreach($forms as $form){
                    self::enqueue_form_scripts($form, $ajax);
                }
            }
        }
    }

    private static function get_embedded_forms($post_content, &$ajax){

        $forms = array();
        if(preg_match_all('/\[gravityform.*?id=(\d*).*?\]/is', $post_content, $matches, PREG_SET_ORDER)){
            $ajax = false;
            foreach($matches as $match){
                //parsing shortcode attributes
                $attr = shortcode_parse_atts($match[0]);
                $form_id = $attr["id"];
                $forms[] = RGFormsModel::get_form_meta($form_id);
                $ajax = isset($attr["ajax"]) && strtolower(substr($attr["ajax"],0, 4)) == "true";
            }
        }
        return $forms;
    }

    public static function enqueue_form_scripts($form, $ajax=false){
        if(!get_option('rg_gforms_disable_css')){
            wp_enqueue_style("gforms_css", GFCommon::get_base_url() . "/css/forms.css", null, GFCommon::$version);
        }

        $jquery_enqueued = false;
        if(self::has_conditional_logic($form)){
            wp_enqueue_script("gforms_conditional_logic_lib", GFCommon::get_base_url() . "/js/conditional_logic.js", array("jquery"), GFCommon::$version);
            $jquery_enqueued = true;
        }

        if(self::has_date_field($form)){
            wp_enqueue_script("gforms_ui_datepicker", GFCommon::get_base_url() . "/js/jquery-ui/ui.datepicker.js", array("jquery"), GFCommon::$version, true);
            wp_enqueue_script("gforms_datepicker", GFCommon::get_base_url() . "/js/datepicker.js", array("gforms_ui_datepicker"), GFCommon::$version, true);
            $jquery_enqueued = true;
        }

        if($ajax && !$jquery_enqueued){
            wp_enqueue_script("jquery");
        }
    }

    private static $printed_scripts = array();

    public static function print_form_scripts($form, $ajax){

        if(!get_option('rg_gforms_disable_css')){
            if(!self::$printed_scripts["gforms_css"] && !wp_style_is("gforms_css", "queue")){
                wp_register_style("gforms_css", GFCommon::get_base_url() . "/css/forms.css", GFCommon::$version);
                wp_print_styles(array("gforms_css"));
            }

            if(!self::$printed_scripts["gforms_widget_css"] && !wp_style_is("gforms_widget_css", "queue")){
                wp_register_style("gforms_widget_css", GFCommon::get_base_url() . "/css/forms_widget.css", GFCommon::$version);
                wp_print_styles(array("gforms_widget_css"));
            }
        }

        $jquery_printed = false;
        if(self::has_conditional_logic($form) && !self::$printed_scripts["gforms_conditional_logic_lib"] && !wp_script_is("gforms_conditional_logic_lib", "queue")){
            wp_register_script("gforms_conditional_logic_lib", GFCommon::get_base_url() . "/js/conditional_logic.js", array("jquery"), GFCommon::$version);
            wp_print_scripts(array("gforms_conditional_logic_lib"));
            $jquery_printed = true;
        }

        if(self::has_date_field($form) && !self::$printed_scripts["gforms_datepicker"] && !wp_script_is("gforms_datepicker", "queue")){
            wp_register_script("gforms_ui_datepicker", GFCommon::get_base_url() . "/js/jquery-ui/ui.datepicker.js", array("jquery"), GFCommon::$version, true);
            wp_register_script("gforms_datepicker", GFCommon::get_base_url() . "/js/datepicker.js", array("gforms_ui_datepicker"), GFCommon::$version, true);
            wp_print_scripts(array("gforms_datepicker"));
            $jquery_printed = true;
        }

        if($ajax && !$jquery_printed && !wp_script_is("jquery", "queue")){
            wp_print_scripts(array("jquery"));
        }
    }


    private static function has_conditional_logic($form){
        if(empty($form))
            return false;

        if(isset($form["button"]["conditionalLogic"]))
            return true;

        foreach($form["fields"] as $field){
            if(!empty($field["conditionalLogic"]))
                return true;
        }

        return false;
    }

    private static function get_conditional_logic($form){
        $logics = "";
        $dependents = "";
        $fields_with_logic = array();
        foreach($form["fields"] as $field){

            //use section's logic if one exists
            $section = RGFormsModel::get_section($form, $field["id"]);
            $section_logic = !empty($section) ? $section["conditionalLogic"] : null;

            $logic = $field["conditionalLogic"];

            if(!empty($logic)){
                $field_section_logic = array("field" => $logic, "section" => $section_logic);
                $logics .= $field["id"] . ": " . GFCommon::json_encode($field_section_logic) . ",";
                $fields_with_logic[] = $field["id"];

                $peers = $field["type"] == "section" ? GFCommon::get_section_fields($form, $field["id"]) : array($field);
                $peer_ids = array();

                foreach ($peers as $peer)
                    $peer_ids[] = $peer["id"];

                $dependents .= $field["id"] . ": " . GFCommon::json_encode($peer_ids) . ",";
            }
        }

        $button_conditional_script = "";

        //adding form button conditional logic if enabled
        if(isset($form["button"]["conditionalLogic"])){
            $logics .= "0: " . GFCommon::json_encode(array("field"=>$form["button"]["conditionalLogic"], "section" => null)) . ",";
            $dependents .= "0: " . GFCommon::json_encode(array(0)) . ",";
            $fields_with_logic[] = 0;

            $button_conditional_script = "
                jQuery('#gform_{$form['id']}').submit(
                    function(){
                        //prevents 'Enter' key from submitting the form when submit button is hidden
                        var btn = jQuery('#gform_submit_button_{$form['id']}');
                        return btn.length == 0 || btn.is(':visible');
                    }
                );";
        }

        if(!empty($logics))
            $logics = substr($logics, 0, strlen($logics) - 1); //removing last comma;

        if(!empty($dependents))
            $dependents = substr($dependents, 0, strlen($dependents) - 1); //removing last comma;

        $animation = $form["enableAnimation"] ? "1" : "0";
        $str = "<script type='text/javascript'>" .
                "jQuery(document).ready(function(){" .
                    "gf_apply_rules({$form['id']}, " . GFCommon::json_encode($fields_with_logic) . ", true);" .
                    "jQuery('#gform_wrapper_{$form['id']}').show();" . $button_conditional_script . "});" .

                    "if(!window['gf_form_conditional_logic'])" .
                        "window['gf_form_conditional_logic'] = new Array();" .
                    "window['gf_form_conditional_logic'][{$form['id']}] = {'logic' : {" . $logics . "}, 'dependents' : {" . $dependents . "}, 'animation' : " . $animation . "};</script>";

        return $str;
    }

    private static function has_date_field($form){
        if(is_array($form["fields"])){
            foreach($form["fields"] as $field){
                if($field["type"] == "date" || $field["inputType"] == "date")
                    return true;
            }
        }
        return false;
    }

    private static function has_recaptcha_field($form){
        if(is_array($form["fields"])){
            foreach($form["fields"] as $field){
                if(($field["type"] == "captcha" || $field["inputType"] == "captcha") && !in_array($field["captchaType"], array("simple_captcha", "math")))
                    return true;
            }
        }
        return false;
    }

    //Getting all fields that have a rule based on the specified field id
    private static function get_conditional_logic_fields($form, $fieldId){
        $fields = array();

        //adding submit button field if enabled
        if(isset($form["button"]["conditionalLogic"])){
            $fields[] = "0";
        }

        foreach($form["fields"] as $field){
            if(!empty($field["conditionalLogic"])){
                foreach($field["conditionalLogic"]["rules"] as $rule){
                    if($rule["fieldId"] == $fieldId){
                        $fields[] = $field["id"];

                        //if field is a section, add all fields in the section that have conditional logic (to support nesting)
                        if($field["type"] == "section"){
                            $section_fields = GFCommon::get_section_fields($form, $field["id"]);
                            foreach($section_fields as $section_field)
                                if(!empty($section_field["conditionalLogic"]))
                                    $fields[] = $section_field["id"];
                        }
                        break;
                    }
                }
            }
        }
        return $fields;
    }

        public static function get_field($field, $value="", $force_frontend_label = false, $form=null, $field_values=null){
        if(!IS_ADMIN && $field["adminOnly"])
        {
            if($field["allowsPrepopulate"])
                $field["inputType"] = "adminonly_hidden";
            else
                return;
        }

        $id = $field["id"];
        $type = $field["type"];
        $error_class = $field["failed_validation"] ? "gfield_error" : "";
        $custom_class = $field["cssClass"];
        $admin_only_class =  $field["adminOnly"] ? "field_admin_only" : "";
        $selectable_class = IS_ADMIN ? "selectable" : "";
        $hidden_class = RGFormsModel::get_input_type($field) == "hidden" ? "gform_hidden" : "";

        $section_class = $field["type"] == "section" ? "gsection" : "";
        $html_block_class = $field["type"] == "html" ? "gfield_html" : "";
        $html_formatted_class = $field["type"] == "html" && !IS_ADMIN && !$field["disableMargins"] ? "gfield_html_formatted" : "";
        $html_no_follows_desc_class = $field["type"] == "html" && !IS_ADMIN && !self::prev_field_has_description($form, $field["id"]) ? "gfield_no_follows_desc" : "";

        $css_class = "$selectable_class gfield $error_class $section_class $admin_only_class $custom_class $hidden_class $html_block_class $html_formatted_class $html_no_follows_desc_class";
        $css_class = trim($css_class);

        $style = !empty($form) && !IS_ADMIN && RGFormsModel::is_field_hidden($form, $field, $field_values) ? "style='display:none;'" : "";

        $field_id = IS_ADMIN || empty($form) ? "field_$id" : "field_" . $form["id"] . "_$id";

        return "<li id='$field_id' class='$css_class' $style>" . self::get_field_content($field, $value, $force_frontend_label, $form == null ? 0 : $form["id"]) . "</li>";
    }

    private static function prev_field_has_description($form, $field_id){
        if(!is_array($form["fields"]))
            return false;

        $prev = null;
        foreach($form["fields"] as $field){
            if($field["id"] == $field_id){
                return $prev != null && !empty($prev["description"]);
            }
            $prev = $field;
        }
        return false;
    }

    public static function get_field_content($field, $value="", $force_frontend_label = false, $form_id=0){
        $id = $field["id"];
        $size = $field["size"];
        $validation_message = ($field["failed_validation"] && !empty($field["validation_message"])) ? sprintf("<div class='gfield_description validation_message'>%s</div>", $field["validation_message"]) : "";

        $delete_field_link = "<a class='field_delete_icon' id='gfield_delete_$id' title='" . __("click to delete this field", "gravityforms") . "' href='javascript:void(0);' onclick='StartDeleteField(this);'>" . __("Delete", "gravityforms") . "</a>";
        $delete_field_link = apply_filters("gform_delete_field_link", $delete_field_link);

        $admin_buttons = IS_ADMIN ? $delete_field_link . " <a class='field_edit_icon edit_icon_collapsed' href='javascript:void(0);' title='" . __("click to edit this field", "gravityforms") . "'>" . __("Edit", "gravityforms") . "</a>" : "";

        $field_label = $force_frontend_label ? $field["label"] : GFCommon::get_label($field);
        $field_id = IS_ADMIN || $form_id == 0 ? "input_$id" : "input_" . $form_id . "_$id";

        $required_div = IS_ADMIN || $field["isRequired"] ? sprintf("<span class='gfield_required'>%s</span>", $field["isRequired"] ? "*" : "") : "";

        switch(RGFormsModel::get_input_type($field)){
            case "section" :
                $description = self::get_description($field["description"], "gsection_description");
                $field_content = sprintf("%s<h2 class='gsection_title'>%s</h2>%s", $admin_buttons,  esc_html($field_label), $description);
            break;

            case "adminonly_hidden":
            case "hidden" :
            case "html" :
                $field_content = !IS_ADMIN ? "{FIELD}" : $field_content = sprintf("%s<label class='gfield_label' for='%s'>%s</label>{FIELD}", $admin_buttons, $field_id, esc_html($field_label));
            break;
            default :
                $description = self::get_description($field["description"],"gfield_description");
                $field_content = sprintf("%s<label class='gfield_label' for='%s'>%s%s</label>{FIELD}%s%s", $admin_buttons, $field_id, esc_html($field_label), $required_div , $description, $validation_message);
            break;
        }

        if(empty($value))
            $value = IS_ADMIN ? $field["defaultValue"] : GFCommon::replace_variables_prepopulate($field["defaultValue"]);

        $field_content = str_replace("{FIELD}", GFCommon::get_field_input($field, $value, 0, $form_id), $field_content);

        return $field_content;
    }

    private static function get_description($description, $css_class){
        return IS_ADMIN || !empty($description) ? "<div class='$css_class'>" . $description . "</div>" : "";
    }
}
?>
