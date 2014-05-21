<?php
class GFCommon{

    public static $version = "1.4.5";
    public static $tab_index = 1;

    public static function get_selection_fields($form, $selected_field_id){

        $str = "";
        foreach($form["fields"] as $field){
            $input_type = RGFormsModel::get_input_type($field);
            $field_label = RGFormsModel::get_label($field);
            if($input_type == "checkbox" || $input_type == "radio" || $input_type == "select"){
                $selected = $field["id"] == $selected_field_id ? "selected='selected'" : "";
                $str .= "<option value='" . $field["id"] . "' " . $selected . ">" . self::truncate_middle($field_label, 16) . "</option>";
            }
        }
        return $str;
    }

    public static function is_numeric($value){
        return preg_match("/^([0-9]{1,3}(?:,?[0-9]{3})*(?:\.[0-9]+?)?)$/", $value) || preg_match("/^([0-9]{1,3}(?:\.?[0-9]{3})*(?:,[0-9]+?)?)$/", $value);
    }

    public static function clean_number($number){

        //Removing all non-numeric characters
        $array = str_split($number);
        foreach($array as $char)
            if (($char >= '0' && $char <= '9') || $char=="," || $char==".")
                $clean_number .= $char;

        //Removing thousand separators but keeping decimal point
        $array = str_split($clean_number);
        for($i=0, $count = sizeof($array); $i<$count; $i++)
        {
            $char = $array[$i];
            if ($char >= '0' && $char <= '9')
                $float_number .= $char;
            else if(($char == "." || $char == ",") && strlen($clean_number) - $i <= 3)
                $float_number .= ".";
        }

        return $float_number;

    }

    public static function json_encode($value){

        if (!extension_loaded('json')){
            if (!class_exists('Services_JSON'))
                include_once(self::get_base_path() . '/json.php');

            $json = new Services_JSON();
            return $json->encode($value);
        }
        else{
            return json_encode($value);
        }
    }

    public static function json_decode($str, $is_assoc=true){
        if (!extension_loaded('json')){
            if (!class_exists('Services_JSON'))
                include_once(self::get_base_path() . '/json.php');

            $json = $is_assoc ? new Services_JSON(SERVICES_JSON_LOOSE_TYPE) : new Services_JSON();
            return $json->decode($str);
        }
        else{
            return json_decode($str, $is_assoc);
        }
    }

    //Returns the url of the plugin's root folder
    public function get_base_url(){
        $folder = basename(dirname(__FILE__));
        return plugins_url($folder);
    }

    //Returns the physical path of the plugin's root folder
    public function get_base_path(){
        $folder = basename(dirname(__FILE__));
        return WP_PLUGIN_DIR . "/" . $folder;
    }

    public static function get_email_fields($form){
        $fields = array();
        foreach($form["fields"] as $field){
            if(RGForms::get("type", $field) == "email" || RGForms::get("inputType", $field) == "email")
                $fields[] = $field;
        }

        return $fields;
    }

    public static function truncate_middle($text, $max_length){
        if(strlen($text) <= $max_length)
            return $text;

        $middle = intval($max_length / 2);
        return substr($text, 0, $middle) . "..." . substr($text, strlen($text) - $middle, $middle);
    }

    public static function is_invalid_or_empty_email($email){
        return empty($email) || !self::is_valid_email($email);
    }

    public static function is_valid_url($url){
        return preg_match('!^(http|https)://([\w-]+\.)+[\w-]+(/[\w- ./?%&=]*)?$!', $url);
    }

    public static function is_valid_email($email){
        return preg_match('/^(([a-zA-Z0-9_.\-+!#$&\'*+=?^`{|}~])+\@((([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+|localhost) *,? *)+$/', $email);
    }

    public static function get_label($field, $input_id = 0, $input_only = false){
        return RGFormsModel::get_label($field, $input_id, $input_only);
    }

    public static function get_input($field, $id){
       return RGFormsModel::get_input($field, $id);
    }

    public static function insert_post_content_variables($fields, $element_id, $callback, $max_label_size=25){
        self::insert_variables($fields, $element_id, true, "", "InsertPostContentVariable('{$element_id}', '{$callback}');", $max_label_size);
        ?>
        &nbsp;&nbsp;
        <select id="<?php echo $element_id?>_image_size_select" onchange="InsertPostImageVariable('<?php echo $element_id ?>', '<?php echo $element_id ?>');" style="display:none;">
            <option value=""><?php _e("Select image size", "gravityforms") ?></option>
            <option value="thumbnail"><?php _e("Thumbnail") ?></option>
            <option value="thumbnail:left"><?php _e("Thumbnail - Left Aligned") ?></option>
            <option value="thumbnail:center"><?php _e("Thumbnail - Centered") ?></option>
            <option value="thumbnail:right"><?php _e("Thumbnail - Right Aligned", "gravityforms") ?></option>

            <option value="medium"><?php _e("Medium") ?></option>
            <option value="medium:left"><?php _e("Medium - Left Aligned") ?></option>
            <option value="medium:center"><?php _e("Medium - Centered") ?></option>
            <option value="medium:right"><?php _e("Medium - Right Aligned", "gravityforms") ?></option>

            <option value="large"><?php _e("Large") ?></option>
            <option value="large:left"><?php _e("Large - Left Aligned") ?></option>
            <option value="large:center"><?php _e("Large - Centered") ?></option>
            <option value="large:right"><?php _e("Large - Right Aligned", "gravityforms") ?></option>

            <option value="full"><?php _e("Full Size") ?></option>
            <option value="full:left"><?php _e("Full Size - Left Aligned") ?></option>
            <option value="full:center"><?php _e("Full Size - Centered") ?></option>
            <option value="full:right"><?php _e("Full Size - Right Aligned", "gravityforms") ?></option>
        </select>
        <?php
    }

    public static function insert_variables($fields, $element_id, $hide_all_fields=false, $callback="", $onchange="", $max_label_size=40, $exclude = null){
        if($fields == null)
            $fields = array();

        $onchange = empty($onchange) ? "InsertVariable('{$element_id}', '{$callback}');" : $onchange;
        ?>
        <select id="<?php echo $element_id?>_variable_select" onchange="<?php echo $onchange ?>">
            <option value=''><?php _e("Insert form field", "gravityforms"); ?></option>
            <?php
            if(!$hide_all_fields){
                ?>
                <option value='{all_fields}'><?php _e("All Submitted Fields", "gravityforms"); ?></option>
                <?php
            }
            $required_fields = array();
            $optional_fields = array();

            foreach($fields as $field){
                $input_type = RGFormsModel::get_input_type($field);

                //skip field types that should be excluded
                if(is_array($exclude) && in_array($input_type, $exclude))
                    continue;

                if($field["isRequired"]){

                    switch($input_type){
                        case "name" :
                            if($field["nameFormat"] == "extended"){
                                $prefix = GFCommon::get_input($field, $field["id"] + 0.2);
                                $suffix = GFCommon::get_input($field, $field["id"] + 0.8);
                                $optional_field = $field;
                                $optional_field["inputs"] = array($prefix, $suffix);

                                //Add optional name fields to the optional list
                                $optional_fields[] = $optional_field;

                                //Remove optional name field from required list
                                unset($field["inputs"][0]);
                                unset($field["inputs"][3]);
                            }

                            $required_fields[] = $field;
                        break;


                        default:
                            $required_fields[] = $field;
                    }
                }
                else{
                   $optional_fields[] = $field;
                }

            }

            if(!empty($required_fields)){
                ?>
                    <optgroup label="<?php _e("Required form fields", "gravityforms"); ?>">
                    <?php
                    foreach($required_fields as $field){
                        self::insert_field_variable($field, $max_label_size);
                    }
                    ?>
                    </optgroup>
                <?php
            }

            if(!empty($optional_fields)){
                ?>
                <optgroup label="<?php _e("Optional form fields", "gravityforms"); ?>">
                    <?php
                    foreach($optional_fields as $field){
                        self::insert_field_variable($field, $max_label_size);
                    }
                    ?>
                </optgroup>
                <?php
            }
            ?>
            <optgroup label="<?php _e("Other", "gravityforms"); ?>">
                <option value='{ip}'><?php _e("Client IP Address", "gravityforms"); ?></option>
                <option value='{date_mdy}'><?php _e("Date", "gravityforms"); ?> (mm/dd/yyyy)</option>
                <option value='{date_dmy}'><?php _e("Date", "gravityforms"); ?> (dd/mm/yyyy)</option>
                <option value='{embed_post:ID}'><?php _e("Embed Post/Page Id", "gravityforms"); ?></option>
                <option value='{embed_post:post_title}'><?php _e("Embed Post/Page Title", "gravityforms"); ?></option>
                <option value='{embed_url}'><?php _e("Embed URL", "gravityforms"); ?></option>
                <option value='{entry_id}'><?php _e("Entry Id", "gravityforms"); ?></option>
                <option value='{entry_url}'><?php _e("Entry URL", "gravityforms"); ?></option>
                <option value='{form_id}'><?php _e("Form Id", "gravityforms"); ?></option>
                <option value='{form_title}'><?php _e("Form Title", "gravityforms"); ?></option>
                <option value='{user_agent}'><?php _e("HTTP User Agent", "gravityforms"); ?></option>

                <?php if(self::has_post_field($fields)){ ?>
                    <option value='{post_id}'><?php _e("Post Id", "gravityforms"); ?></option>
                    <option value='{post_edit_url}'><?php _e("Post Edit URL", "gravityforms"); ?></option>
                <?php } ?>

                <option value='{user:display_name}'><?php _e("User Display Name", "gravityforms"); ?></option>
                <option value='{user:user_email}'><?php _e("User Email", "gravityforms"); ?></option>
                <option value='{user:user_login}'><?php _e("User Login", "gravityforms"); ?></option>
            </optgroup>

        </select>
        <?php
    }

    public static function insert_field_variable($field, $max_label_size=40){
        if(is_array($field["inputs"]))
        {
            foreach($field["inputs"] as $input){
                ?>
                <option value='<?php echo "{" . esc_html(GFCommon::get_label($field, $input["id"])) . ":" . $input["id"] . "}" ?>'><?php echo esc_html(GFCommon::truncate_middle(GFCommon::get_label($field, $input["id"]), $max_label_size)) ?></option>
                <?php
            }
        }
        else{
            ?>
            <option value='<?php echo "{" . esc_html(GFCommon::get_label($field)) . ":" . $field["id"] . "}" ?>'><?php echo esc_html(GFCommon::truncate_middle(GFCommon::get_label($field), $max_label_size)) ?></option>
            <?php
        }
    }
    private static function get_post_image_variable($media_id, $arg1, $arg2, $is_url = false){

        if($is_url){
            $image = wp_get_attachment_image_src($media_id, $arg1);
            if ( $image )
                list($src, $width, $height) = $image;

            return $src;
        }

        switch($arg1){
            case "title" :
                $media = get_post($media_id);
                return $media->post_title;
            case "caption" :
                $media = get_post($media_id);
                return $media->post_excerpt;
            case "description" :
                $media = get_post($media_id);
                return $media->post_content;

            default :

                $img = wp_get_attachment_image($media_id, $arg1, false, array("class" => "size-{$arg1} align{$arg2} wp-image-{$media_id}"));
                return $img;
        }
    }

    public static function replace_variables_post_image($text, $post_images, $lead){

        preg_match_all('/{[^{]*?:(\d+)(:([^:]*?))?(:([^:]*?))?(:url)?}/mi', $text, $matches, PREG_SET_ORDER);
        if(is_array($matches))
        {
            foreach($matches as $match){
                $input_id = $match[1];

                //ignore fields that are not post images
                if(!isset($post_images[$input_id]))
                    continue;

                //Reading alignment and "url" parameters.
                //Format could be {image:5:medium:left:url} or {image:5:medium:url}
                $size_meta = empty($match[3]) ? "full" : $match[3];
                $align = empty($match[5]) ? "none" : $match[5];
                if($align == "url"){
                    $align = "none";
                    $is_url = true;
                }
                else{
                    $is_url = $match[6] == ":url";
                }

                $media_id = $post_images[$input_id];
                $value = is_wp_error($media_id) ? "" : self::get_post_image_variable($media_id, $size_meta, $align, $is_url);

                $text = str_replace($match[0], $value , $text);
            }
        }

        return $text;
    }


    public static function replace_variables($text, $form, $lead, $url_encode = false, $esc_html=true, $nl2br = true){
        $text = $nl2br ? nl2br($text) : $text;

        //Replacing field variables
        preg_match_all('/{[^{]*?:(\d+(\.\d+)?)(:(.*?))?}/mi', $text, $matches, PREG_SET_ORDER);
        if(is_array($matches))
        {
            foreach($matches as $match){
                $input_id = $match[1];

                $field = RGFormsModel::get_field($form,$input_id);
                $value = RGFormsModel::get_lead_field_value($lead, $field);

                $raw_value = $value;

                if(is_array($value))
                    $value = $value[$input_id];

                if($esc_html)
                    $value = esc_html($value);

                $value = nl2br($value);

                if($url_encode)
                    $value = urlencode($value);

                switch(RGFormsModel::get_input_type($field)){

                    case "fileupload" :
                        $value = str_replace(" ", "%20", $value);
                    break;

                    case "post_image" :
                        list($url, $title, $caption, $description) = explode("|:|", $value);
                        $value = str_replace(" ", "%20", $url);
                    break;

                    case "checkbox" :
                    case "select" :
                    case "radio" :
                        $use_value = $match[4] == "value";
                        if(!$use_value)
                            $value = RGFormsModel::get_choice_text($field, $raw_value, $input_id);
                    break;

                    case "date" :
                        $value = GFCommon::date_display($value, $field["dateFormat"]);
                    break;
                }

                $text = str_replace($match[0], $value , $text);
            }
        }

        //replacing global variables
        //form title
        $text = str_replace("{form_title}", $url_encode ? urlencode($form["title"]) : $form["title"], $text);

        //all submitted fields
        $text = str_replace("{all_fields}", self::get_submitted_fields($form, $lead), $text);

        //all submitted fields including empty fields
        $text = str_replace("{all_fields_display_empty}", self::get_submitted_fields($form, $lead, true), $text);


        //form id
        $text = str_replace("{form_id}", $url_encode ? urlencode($form["id"]) : $form["id"], $text);

        //entry id
        $text = str_replace("{entry_id}", $url_encode ? urlencode($lead["id"]) : $lead["id"], $text);

        //entry url
        $entry_url = get_bloginfo("wpurl") . "/wp-admin/admin.php?page=gf_entries&view=entry&id=" . $form["id"] . "&lid=" . $lead["id"];
        $text = str_replace("{entry_url}", $url_encode ? urlencode($entry_url) : $entry_url, $text);

        //post id
        $text = str_replace("{post_id}", $url_encode ? urlencode($lead["post_id"]) : $lead["post_id"], $text);

        //post edit url
        $post_url = get_bloginfo("wpurl") . "/wp-admin/post.php?action=edit&post=" . $lead["post_id"];
        $text = str_replace("{post_edit_url}", $url_encode ? urlencode($post_url) : $post_url, $text);

        $text = self::replace_variables_prepopulate($text, $url_encode);

        return $text;
    }

    public static function replace_variables_prepopulate($text, $url_encode=false){

        //embed url
        $text = str_replace("{embed_url}", $url_encode ? urlencode(RGFormsModel::get_current_page_url()) : RGFormsModel::get_current_page_url(), $text);

        $local_timestamp = self::get_local_timestamp(time());

        //date (mm/dd/yyyy)
        $local_date_mdy = date_i18n("m/d/Y", $local_timestamp, true);
        $text = str_replace("{date_mdy}", $url_encode ? urlencode($local_date_mdy) : $local_date_mdy, $text);

        //date (dd/mm/yyyy)
        $local_date_dmy = date_i18n("d/m/Y", $local_timestamp, true);
        $text = str_replace("{date_dmy}", $url_encode ? urlencode($local_date_dmy) : $local_date_dmy, $text);

        //ip
        $text = str_replace("{ip}", $url_encode ? urlencode($_SERVER['REMOTE_ADDR']) : $_SERVER['REMOTE_ADDR'], $text);

        //embed post info
        global $post;
        $post_array = self::object_to_array($post);
        preg_match_all("/\{embed_post:(.*?)\}/", $text, $matches, PREG_SET_ORDER);
        foreach($matches as $match){
            $full_tag = $match[0];
            $property = $match[1];
            $text = str_replace($full_tag, $url_encode ? urlencode($post_array[$property]) : $post_array[$property], $text);
        }

        //embed post custom fields
        preg_match_all("/\{custom_field:(.*?)\}/", $text, $matches, PREG_SET_ORDER);
        foreach($matches as $match){
            $full_tag = $match[0];
            $custom_field_name = $match[1];
            $custom_field_value = !empty($post_array["ID"]) ? get_post_meta($post_array["ID"], $custom_field_name, true) : "";
            $text = str_replace($full_tag, $url_encode ? urlencode($custom_field_value) : $custom_field_value, $text);
        }

        //user agent
        $text = str_replace("{user_agent}", $url_encode ? urlencode($_SERVER["HTTP_USER_AGENT"]) : $_SERVER["HTTP_USER_AGENT"], $text);

        //referrer
        $text = str_replace("{referer}", $url_encode ? urlencode($_SERVER["HTTP_REFERER"]) : $_SERVER["HTTP_REFERER"], $text);

        //logged in user info
        global $userdata;
        $user_array = self::object_to_array($userdata);
        preg_match_all("/\{user:(.*?)\}/", $text, $matches, PREG_SET_ORDER);
        foreach($matches as $match){
            $full_tag = $match[0];
            $property = $match[1];
            $text = str_replace($full_tag, $url_encode ? urlencode($user_array[$property]) : $user_array[$property], $text);
        }

        return $text;
    }

    public static function object_to_array($object)
    {
        $array=array();
        if(!empty($object)){
            foreach($object as $member=>$data)
                $array[$member]=$data;
        }
        return $array;
    }

    public static function get_submitted_fields($form, $lead, $display_empty = false){
        $field_data = '<table width="99%" border="0" cellpadding="1" cellpsacing="0" bgcolor="#EAEAEA"><tr><td>
                          <table width="100%" border="0" cellpadding="5" cellpsacing="0" bgcolor="#FFFFFF">';
        foreach($form["fields"] as $field){
            $field_label = esc_html(GFCommon::get_label($field));

            switch($field["type"]){
                case "captcha" :
                    break;

                case "section" :
                    $field_data .= sprintf('<tr>
                                                <td colspan="2" style="font-size:14px; font-weight:bold; background-color:#EEE; border-bottom:1px solid #DFDFDF; padding:7px 7px">%s</td>
                                           </tr>', $field_label);
                    break;

                default :
                    $value = RGFormsModel::get_lead_field_value($lead, $field);
                    $field_value = GFCommon::get_lead_field_display($field, $value);

                    if(!empty($field_value) || $display_empty)
                        $field_data .= sprintf('<tr bgcolor="#EAF2FA">
                                                    <td colspan="2">
                                                        <font style="font-family:verdana; font-size:12px;"><strong>%s</strong></font>
                                                    </td>
                                               </tr>
                                               <tr bgcolor="#FFFFFF">
                                                    <td width="20">&nbsp;</td>
                                                    <td>
                                                        <font style="font-family:verdana; font-size:12px;">%s</font>
                                                    </td>
                                               </tr>', $field_label, empty($field_value) ? "&nbsp;" : $field_value);
            }
        }
        $field_data .= "</table></td></tr></table>";
        return $field_data;
    }

    public static function has_post_field($fields){
        foreach($fields as $field){
            if(in_array($field["type"], array("post_title", "post_content", "post_excerpt", "post_category", "post_image", "post_tags", "post_custom_field")))
                return true;
        }
        return false;
    }

    public static function current_user_can_any($caps){

        if(!is_array($caps))
            return current_user_can($caps) || current_user_can("gform_full_access");

        foreach($caps as $cap){
            if(current_user_can($cap))
                return true;
        }

        return current_user_can("gform_full_access");
    }

    public static function current_user_can_which($caps){

        foreach($caps as $cap){
            if(current_user_can($cap))
                return $cap;
        }

        return "";
    }

    function all_caps(){
        return array(   'gravityforms_edit_forms',
                        'gravityforms_delete_forms',
                        'gravityforms_create_form',
                        'gravityforms_view_entries',
                        'gravityforms_edit_entries',
                        'gravityforms_delete_entries',
                        'gravityforms_view_settings',
                        'gravityforms_edit_settings',
                        'gravityforms_export_entries',
                        'gravityforms_uninstall',
                        'gravityforms_view_entry_notes',
                        'gravityforms_edit_entry_notes',
                        'gravityforms_view_updates'
                        );
    }


    public static function delete_directory($dir)
    {
        if(!file_exists($dir))
            return;

        if ($handle = opendir($dir)){
            $array = array();
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    if(is_dir($dir.$file)){
                        if(!@rmdir($dir.$file)) // Empty directory? Remove it
                            self::delete_directory($dir.$file.'/'); // Not empty? Delete the files inside it
                    }
                    else{
                       @unlink($dir.$file);
                    }
                }
            }
            closedir($handle);
            @rmdir($dir);
        }
    }

    public static function get_remote_message(){
        return stripslashes(get_option("rg_gforms_message"));
    }

    public static function get_key(){
        return get_option("rg_gforms_key");
    }

    public static function has_update($use_cache=true){
        $version_info = GFCommon::get_version_info($use_cache);
        return version_compare(GFCommon::$version, $version_info["version"], '<');
    }

    public static function get_version_info($cache=true){
	
	// brbr 18.11.10
	/*
        $raw_response = get_transient("gform_update_info");
        if(!$cache)
            $raw_response = null;

        if(!$raw_response){
            //Getting version number
            $options = array('method' => 'POST', 'timeout' => 3);
            $options['headers'] = array(
                'Content-Type' => 'application/x-www-form-urlencoded; charset=' . get_option('blog_charset'),
                'User-Agent' => 'WordPress/' . get_bloginfo("version"),
                'Referer' => get_bloginfo("url")
            );
            $request_url = GRAVITY_MANAGER_URL . "/version.php?" . self::get_remote_request_params();
            $raw_response = wp_remote_request($request_url, $options);

            //caching responses.
            set_transient("gform_update_info", $raw_response, 43200); //caching for 12 hours
        }

         if ( is_wp_error( $raw_response ) || 200 != $raw_response['response']['code'])
            return array("is_valid_key" => "1", "version" => "", "url" => "");
         else
         {
             $ary = explode("||", $raw_response['body']);
             return array("is_valid_key" => $ary[0], "version" => $ary[1], "url" => $ary[2]);
         }
		 */

    }

    public static function get_remote_request_params(){
        global $wpdb;

        return sprintf("of=GravityForms&key=%s&v=%s&wp=%s&php=%s&mysql=%s", urlencode(self::get_key()), urlencode(self::$version), urlencode(get_bloginfo("version")), urlencode(phpversion()), urlencode($wpdb->db_version()));
    }

    public static function ensure_wp_version(){
        if(!GF_SUPPORTED_WP_VERSION){
            echo "<div class='error' style='padding:10px;'>Gravity Forms require WordPress 2.8 or greater. You must upgrade WordPress in order to use Gravity Forms</div>";
            return false;
        }
        return true;
    }

    public static function check_update($option, $cache=true){

		// brbr 18.11.10
/*
        $version_info = self::get_version_info($cache);

        if (!$version_info)
            return $option;

        $plugin_path = "gravityforms/gravityforms.php";
        if(empty($option->response[$plugin_path]))
            $option->response[$plugin_path] = new stdClass();

        //Empty response means that the key is invalid. Do not queue for upgrade
        if(!$version_info["is_valid_key"] || version_compare(GFCommon::$version, $version_info["version"], '>=')){
            unset($option->response[$plugin_path]);
        }
        else{
            $option->response[$plugin_path]->url = "http://www.gravityforms.com";
            $option->response[$plugin_path]->slug = "gravityforms";
            $option->response[$plugin_path]->package = str_replace("{KEY}", GFCommon::get_key(), $version_info["url"]);
            $option->response[$plugin_path]->new_version = $version_info["version"];
            $option->response[$plugin_path]->id = "0";
        }

        return $option;
*/

		return false;

    }

    public static function cache_remote_message(){
		
		// brbr 18.11.10
		
		/*
        //Getting version number
        $key = GFCommon::get_key();
        $body = "key=$key";
        $options = array('method' => 'POST', 'timeout' => 3, 'body' => $body);
        $options['headers'] = array(
            'Content-Type' => 'application/x-www-form-urlencoded; charset=' . get_option('blog_charset'),
            'Content-Length' => strlen($body),
            'User-Agent' => 'WordPress/' . get_bloginfo("version"),
            'Referer' => get_bloginfo("url")
        );

        $request_url = GRAVITY_MANAGER_URL . "/message.php?" . GFCommon::get_remote_request_params();
        $raw_response = wp_remote_request($request_url, $options);

        if ( is_wp_error( $raw_response ) || 200 != $raw_response['response']['code'] )
            $message = "";
        else
            $message = $raw_response['body'];

        //validating that message is a valid Gravity Form message. If message is invalid, don't display anything
        if(substr($message, 0, 10) != "<!--GFM-->") */ 
            $message = "";

        update_option("rg_gforms_message", $message);
    }

    public static function get_local_timestamp($timestamp){
        return $timestamp + (get_option( 'gmt_offset' ) * 3600 );
    }

    public static function format_date($gmt_datetime, $is_human = true){
        if(empty($gmt_datetime))
            return "";

        //adjusting date to local configured Time Zone
        $lead_gmt_time = mysql2date("G", $gmt_datetime);
        $lead_local_time = self::get_local_timestamp($lead_gmt_time);

        if($is_human){
            $time_diff = time() - $lead_gmt_time;

            if ($time_diff > 0 && $time_diff < 24*60*60)
                $date_display = sprintf(__('%s ago', 'gravityforms'), human_time_diff($lead_gmt_time));
            else
                $date_display = sprintf(__('%1$s at %2$s', 'gravityforms'), date_i18n(get_option('date_format'), $lead_local_time, true), date_i18n(get_option('time_format'), $lead_local_time, true));
        }
        else{
            $date_display = sprintf(__('%1$s at %2$s', 'gravityforms'), date_i18n(get_option('date_format'), $lead_local_time, true), date_i18n(get_option('time_format'), $lead_local_time, true));
        }

        return $date_display;
    }

    public static function date_display($value, $format = "mdy"){
        $date = self::parse_date($value, $format);
        if(empty($date))
            return $value;

        return $format == "dmy" ? $date["day"] . "/" . $date["month"] . "/" . $date["year"] : $date["month"] . "/" . $date["day"] . "/" . $date["year"];
    }

    public static function parse_date($date, $format="mdy"){
        $date_info = array();

        if(is_array($date)){
            if(empty($date[0]))
                return array();

            //format mm-dd-yyyy or dd-mm-yyyy
            $date_info["year"] = $date[2];
            $date_info["month"] = $format == "mdy" ? $date[0] : $date[1];
            $date_info["day"] = $format == "mdy" ? $date[1] : $date[0];
            return $date_info;
        }

        $date = str_replace("/", "-", $date);
        if(preg_match('/^(\d{1,4})-(\d{1,2})-(\d{1,4})$/', $date, $matches)){

            if(strlen($matches[1]) == 4){
                //format yyyy-mm-dd
                $date_info["year"] = $matches[1];
                $date_info["month"] = $matches[2];
                $date_info["day"] = $matches[3];
            }
            else{
                //format mm-dd-yyyy or dd-mm-yyyy
                $date_info["year"] = $matches[3];
                $date_info["month"] = $format == "mdy" ? $matches[1] : $matches[2];
                $date_info["day"] = $format == "mdy" ? $matches[2] : $matches[1];
            }
        }

        return $date_info;
    }


    public static function truncate_url($url){
        $truncated_url = basename($url);
        if(empty($truncated_url))
            $truncated_url = dirname($url);

        $ary = explode("?", $truncated_url);

        return $ary[0];
    }

    public static function get_tabindex(){
        return GFCommon::$tab_index > 0 ? "tabindex='" . GFCommon::$tab_index++ . "'" : "";
    }

    public static function get_checkbox_choices($field, $value, $disabled_text){
        $choices = "";

        if(is_array($field["choices"])){
            $choice_number = 1;

            foreach($field["choices"] as $choice){
                if($choice_number % 10 == 0) //hack to skip numbers ending in 0. so that 5.1 doesn't conflict with 5.10
                    $choice_number++;

                $input_id = $field["id"] . '.' . $choice_number;
                $id = $field["id"] . '_' . $choice_number++;

                if(empty($value) && $choice["isSelected"])
                    $checked = "checked='checked'";
                else if($value == $choice["value"] || (is_array($value) && $value[$input_id] == $choice["value"]))
                    $checked = "checked='checked'";
                else
                    $checked = "";

                $logic_event = empty($field["conditionalLogicFields"]) || IS_ADMIN ? "" : "onclick='gf_apply_rules(" . $field["formId"] . "," . GFCommon::json_encode($field["conditionalLogicFields"]) . ");'";

                $tabindex = self::get_tabindex();
                $choices.= sprintf("<li class='gchoice_$id'><input name='input_%s' type='checkbox' $logic_event value='%s' %s id='choice_%s' $tabindex %s /><label for='choice_%s'>%s</label></li>", $input_id, esc_attr($choice["value"]), $checked, $id, $disabled_text, $id, esc_html($choice["text"]));
            }
        }

        return apply_filters("gform_field_choices_{$field["formId"]}", apply_filters("gform_field_choices", $choices, $field), $field);

    }

    public static function get_radio_choices($field, $value="", $disabled_text){
        $choices = "";

        if(is_array($field["choices"])){
            $choice_id = 0;

            $logic_event = empty($field["conditionalLogicFields"]) || IS_ADMIN ? "" : "onclick='gf_apply_rules(" . $field["formId"] . "," . GFCommon::json_encode($field["conditionalLogicFields"]) . ");'";
            foreach($field["choices"] as $choice){
                $id = $field["id"] . '_' . $choice_id++;

                $field_value = !empty($choice["value"]) || $field["enableChoiceValue"] ? $choice["value"] : $choice["text"];

                if(empty($value))
                    $checked = $choice["isSelected"] ? "checked='checked'" : "";
                else
                    $checked = ($value == $field_value || (is_array($value) && in_array($field_value, $value))) ? "checked='checked'" : "";

                $tabindex = self::get_tabindex();
                $choices.= sprintf("<li class='gchoice_$id'><input name='input_%d' type='radio' value='%s' %s id='choice_%s' $tabindex %s $logic_event /><label for='choice_%s'>%s</label></li>", $field["id"], esc_attr($field_value), $checked, $id, $disabled_text, $id, esc_html($choice["text"]));
            }
        }

        return apply_filters("gform_field_choices_{$field["formId"]}", apply_filters("gform_field_choices", $choices, $field), $field);
    }

    public static function get_select_choices($field, $value=""){
        $choices = "";

        if(RG_CURRENT_VIEW == "entry" && empty($value))
            $choices .= "<option value=''></option>";

        if(is_array($field["choices"])){
            foreach($field["choices"] as $choice){

                //needed for users upgrading from 1.0
                $field_value = !empty($choice["value"]) || $field["enableChoiceValue"] ? $choice["value"] : $choice["text"];

                if(empty($value) && RG_CURRENT_VIEW != "entry")
                    $selected = $choice["isSelected"] ? "selected='selected'" : "";
                else
                    $selected = ($value == $field_value) ? "selected='selected'" : "";

                $choices.= sprintf("<option value='%s' %s>%s</option>", esc_attr($field_value), $selected,  esc_html($choice["text"]));
            }
        }
        return $choices;
    }

    public static function get_fields_by_type($form, $types){
        $fields = array();
        foreach($form["fields"] as $field){
            if(in_array($field["type"], $types))
                $fields[] = $field;
        }
        return $fields;
    }

    public static function get_section_fields($form, $section_field_id){
        $fields = array();
        $in_section = false;
        foreach($form["fields"] as $field){
            if($field["type"] == "section" && $in_section)
                return $fields;

            if($field["id"] == $section_field_id)
                $in_section = true;

            if($in_section)
                $fields[] = $field;
        }

        return $fields;
    }


    public static function get_countries(){
        return array(
        __('Afghanistan', 'gravityforms'),__('Albania', 'gravityforms'),__('Algeria', 'gravityforms'), __('American Samoa', 'gravityforms'), __('Andorra', 'gravityforms'),__('Angola', 'gravityforms'),__('Antigua and Barbuda', 'gravityforms'),__('Argentina', 'gravityforms'),__('Armenia', 'gravityforms'),__('Australia', 'gravityforms'),__('Austria', 'gravityforms'),__('Azerbaijan', 'gravityforms'),__('Bahamas', 'gravityforms'),__('Bahrain', 'gravityforms'),__('Bangladesh', 'gravityforms'),__('Barbados', 'gravityforms'),__('Belarus', 'gravityforms'),__('Belgium', 'gravityforms'),__('Belize', 'gravityforms'),__('Benin', 'gravityforms'),__('Bermuda', 'gravityforms'),__('Bhutan', 'gravityforms'),__('Bolivia', 'gravityforms'),__('Bosnia and Herzegovina', 'gravityforms'),__('Botswana', 'gravityforms'),__('Brazil', 'gravityforms'),__('Brunei', 'gravityforms'),__('Bulgaria', 'gravityforms'),__('Burkina Faso', 'gravityforms'),__('Burundi', 'gravityforms'),__('Cambodia', 'gravityforms'),__('Cameroon', 'gravityforms'),__('Canada', 'gravityforms'),__('Cape Verde', 'gravityforms'),__('Central African Republic', 'gravityforms'),__('Chad', 'gravityforms'),__('Chile', 'gravityforms'),__('China', 'gravityforms'),__('Colombia', 'gravityforms'),__('Comoros', 'gravityforms'),__('Congo', 'gravityforms'),__('Costa Rica', 'gravityforms'),__('C&ocirc;te d\'Ivoire', 'gravityforms'),__('Croatia', 'gravityforms'),__('Cuba', 'gravityforms'),__('Cyprus', 'gravityforms'),__('Czech Republic', 'gravityforms'),__('Denmark', 'gravityforms'),__('Djibouti', 'gravityforms'),__('Dominica', 'gravityforms'),__('Dominican Republic', 'gravityforms'),__('East Timor', 'gravityforms'),__('Ecuador', 'gravityforms'),__('Egypt', 'gravityforms'),__('El Salvador', 'gravityforms'),__('Equatorial Guinea', 'gravityforms'),__('Eritrea', 'gravityforms'),__('Estonia', 'gravityforms'),__('Ethiopia', 'gravityforms'),__('Fiji', 'gravityforms'),__('Finland', 'gravityforms'),__('France', 'gravityforms'),__('Gabon', 'gravityforms'),
        __('Gambia', 'gravityforms'),__('Georgia', 'gravityforms'),__('Germany', 'gravityforms'),__('Ghana', 'gravityforms'),__('Greece', 'gravityforms'),__('Grenada', 'gravityforms'),__('Guam', 'gravityforms'),__('Guatemala', 'gravityforms'),__('Guinea', 'gravityforms'),__('Guinea-Bissau', 'gravityforms'),__('Guyana', 'gravityforms'),__('Haiti', 'gravityforms'),__('Honduras', 'gravityforms'),__('Hong Kong', 'gravityforms'),__('Hungary', 'gravityforms'),__('Iceland', 'gravityforms'),__('India', 'gravityforms'),__('Indonesia', 'gravityforms'),__('Iran', 'gravityforms'),__('Iraq', 'gravityforms'),__('Ireland', 'gravityforms'),__('Israel', 'gravityforms'),__('Italy', 'gravityforms'),__('Jamaica', 'gravityforms'),__('Japan', 'gravityforms'),__('Jordan', 'gravityforms'),__('Kazakhstan', 'gravityforms'),__('Kenya', 'gravityforms'),__('Kiribati', 'gravityforms'),__('North Korea', 'gravityforms'),__('South Korea', 'gravityforms'),__('Kuwait', 'gravityforms'),__('Kyrgyzstan', 'gravityforms'),__('Laos', 'gravityforms'),__('Latvia', 'gravityforms'),__('Lebanon', 'gravityforms'),__('Lesotho', 'gravityforms'),__('Liberia', 'gravityforms'),__('Libya', 'gravityforms'),__('Liechtenstein', 'gravityforms'),__('Lithuania', 'gravityforms'),__('Luxembourg', 'gravityforms'),__('Macedonia', 'gravityforms'),__('Madagascar', 'gravityforms'),__('Malawi', 'gravityforms'),__('Malaysia', 'gravityforms'),__('Maldives', 'gravityforms'),__('Mali', 'gravityforms'),__('Malta', 'gravityforms'),__('Marshall Islands', 'gravityforms'),__('Mauritania', 'gravityforms'),__('Mauritius', 'gravityforms'),__('Mexico', 'gravityforms'),__('Micronesia', 'gravityforms'),__('Moldova', 'gravityforms'),__('Monaco', 'gravityforms'),__('Mongolia', 'gravityforms'),__('Montenegro', 'gravityforms'),__('Morocco', 'gravityforms'),__('Mozambique', 'gravityforms'),__('Myanmar', 'gravityforms'),__('Namibia', 'gravityforms'),__('Nauru', 'gravityforms'),__('Nepal', 'gravityforms'),__('Netherlands', 'gravityforms'),__('New Zealand', 'gravityforms'),
        __('Nicaragua', 'gravityforms'),__('Niger', 'gravityforms'),__('Nigeria', 'gravityforms'),__('Norway', 'gravityforms'), __('Northern Mariana Islands', 'gravityforms'), __('Oman', 'gravityforms'),__('Pakistan', 'gravityforms'),__('Palau', 'gravityforms'),__('Palestine', 'gravityforms'),__('Panama', 'gravityforms'),__('Papua New Guinea', 'gravityforms'),__('Paraguay', 'gravityforms'),__('Peru', 'gravityforms'),__('Philippines', 'gravityforms'),__('Poland', 'gravityforms'),__('Portugal', 'gravityforms'),__('Puerto Rico', 'gravityforms'),__('Qatar', 'gravityforms'),__('Romania', 'gravityforms'),__('Russia', 'gravityforms'),__('Rwanda', 'gravityforms'),__('Saint Kitts and Nevis', 'gravityforms'),__('Saint Lucia', 'gravityforms'),__('Saint Vincent and the Grenadines', 'gravityforms'),__('Samoa', 'gravityforms'),__('San Marino', 'gravityforms'),__('Sao Tome and Principe', 'gravityforms'),__('Saudi Arabia', 'gravityforms'),__('Senegal', 'gravityforms'),__('Serbia and Montenegro', 'gravityforms'),__('Seychelles', 'gravityforms'),__('Sierra Leone', 'gravityforms'),__('Singapore', 'gravityforms'),__('Slovakia', 'gravityforms'),__('Slovenia', 'gravityforms'),__('Solomon Islands', 'gravityforms'),__('Somalia', 'gravityforms'),__('South Africa', 'gravityforms'),__('Spain', 'gravityforms'),__('Sri Lanka', 'gravityforms'),__('Sudan', 'gravityforms'),__('Suriname', 'gravityforms'),__('Swaziland', 'gravityforms'),__('Sweden', 'gravityforms'),__('Switzerland', 'gravityforms'),__('Syria', 'gravityforms'),__('Taiwan', 'gravityforms'),__('Tajikistan', 'gravityforms'),__('Tanzania', 'gravityforms'),__('Thailand', 'gravityforms'),__('Togo', 'gravityforms'),__('Tonga', 'gravityforms'),__('Trinidad and Tobago', 'gravityforms'),__('Tunisia', 'gravityforms'),__('Turkey', 'gravityforms'),__('Turkmenistan', 'gravityforms'),__('Tuvalu', 'gravityforms'),__('Uganda', 'gravityforms'),__('Ukraine', 'gravityforms'),__('United Arab Emirates', 'gravityforms'),__('United Kingdom', 'gravityforms'),
        __('United States', 'gravityforms'),__('Uruguay', 'gravityforms'),__('Uzbekistan', 'gravityforms'),__('Vanuatu', 'gravityforms'),__('Vatican City', 'gravityforms'),__('Venezuela', 'gravityforms'),__('Vietnam', 'gravityforms'), __('Virgin Islands, British', 'gravityforms'), __('Virgin Islands, U.S.', 'gravityforms'),__('Yemen', 'gravityforms'),__('Zambia', 'gravityforms'),__('Zimbabwe', 'gravityforms'));
    }

    public static function get_country_code($country_name) {
        $codes = array(

            __('AFGHANISTAN', 'gravityforms') => "AF" ,
            __('ALBANIA', 'gravityforms') => "AL" ,
            __('ALGERIA', 'gravityforms') => "DZ" ,
            __('AMERICAN SAMOA', 'gravityforms') => "AS" ,
            __('ANDORRA', 'gravityforms') => "AD" ,
            __('ANGOLA', 'gravityforms') => "AO" ,
            __('ANTIGUA AND BARBUDA', 'gravityforms') => "AG" ,
            __('ARGENTINA', 'gravityforms') => "AR" ,
            __('ARMENIA', 'gravityforms') => "AM" ,
            __('AUSTRALIA', 'gravityforms') => "AU" ,
            __('AUSTRIA', 'gravityforms') => "AT" ,
            __('AZERBAIJAN', 'gravityforms') => "AZ" ,
            __('BAHAMAS', 'gravityforms') => "BS" ,
            __('BAHRAIN', 'gravityforms') => "BH" ,
            __('BANGLADESH', 'gravityforms') => "BD" ,
            __('BARBADOS', 'gravityforms') => "BB" ,
            __('BELARUS', 'gravityforms') => "BY" ,
            __('BELGIUM', 'gravityforms') => "BE" ,
            __('BELIZE', 'gravityforms') => "BZ" ,
            __('BENIN', 'gravityforms') => "BJ" ,
            __('BERMUDA', 'gravityforms') => "BM" ,
            __('BHUTAN', 'gravityforms') => "BT" ,
            __('BOLIVIA', 'gravityforms') => "BO" ,
            __('BOSNIA AND HERZEGOVINA', 'gravityforms') => "BA" ,
            __('BOTSWANA', 'gravityforms') => "BW" ,
            __('BRAZIL', 'gravityforms') => "BR" ,
            __('BRUNEI', 'gravityforms') => "BN" ,
            __('BULGARIA', 'gravityforms') => "BG" ,
            __('BURKINA FASO', 'gravityforms') => "BF" ,
            __('BURUNDI', 'gravityforms') => "BI" ,
            __('CAMBODIA', 'gravityforms') => "KH" ,
            __('CAMEROON', 'gravityforms') => "CM" ,
            __('CANADA', 'gravityforms') => "CA" ,
            __('CAPE VERDE', 'gravityforms') => "CV" ,
            __('CENTRAL AFRICAN REPUBLIC', 'gravityforms') => "CF" ,
            __('CHAD', 'gravityforms') => "TD" ,
            __('CHILE', 'gravityforms') => "CL" ,
            __('CHINA', 'gravityforms') => "CN" ,
            __('COLOMBIA', 'gravityforms') => "CO" ,
            __('COMOROS', 'gravityforms') => "KM" ,
            __('CONGO', 'gravityforms') => "CG" ,
            __('COSTA RICA', 'gravityforms') => "CR" ,
            __('C&OCIRC;TE D\'IVOIRE', 'gravityforms') => "CI" ,
            __('CROATIA', 'gravityforms') => "HR" ,
            __('CUBA', 'gravityforms') => "CU" ,
            __('CYPRUS', 'gravityforms') => "CY" ,
            __('CZECH REPUBLIC', 'gravityforms') => "CZ" ,
            __('DENMARK', 'gravityforms') => "DK" ,
            __('DJIBOUTI', 'gravityforms') => "DJ" ,
            __('DOMINICA', 'gravityforms') => "DM" ,
            __('DOMINICAN REPUBLIC', 'gravityforms') => "DO" ,
            __('EAST TIMOR', 'gravityforms') => "TL" ,
            __('ECUADOR', 'gravityforms') => "EC" ,
            __('EGYPT', 'gravityforms') => "EG" ,
            __('EL SALVADOR', 'gravityforms') => "SV" ,
            __('EQUATORIAL GUINEA', 'gravityforms') => "GQ" ,
            __('ERITREA', 'gravityforms') => "ER" ,
            __('ESTONIA', 'gravityforms') => "EE" ,
            __('ETHIOPIA', 'gravityforms') => "ET" ,
            __('FIJI', 'gravityforms') => "FJ" ,
            __('FINLAND', 'gravityforms') => "FI" ,
            __('FRANCE', 'gravityforms') => "FR" ,
            __('GABON', 'gravityforms') => "GA" ,
            __('GAMBIA', 'gravityforms') => "GM" ,
            __('GEORGIA', 'gravityforms') => "GE" ,
            __('GERMANY', 'gravityforms') => "DE" ,
            __('GHANA', 'gravityforms') => "GH" ,
            __('GREECE', 'gravityforms') => "GR" ,
            __('GRENADA', 'gravityforms') => "GD" ,
            __('GUAM', 'gravityforms') => "GU" ,
            __('GUATEMALA', 'gravityforms') => "GT" ,
            __('GUINEA', 'gravityforms') => "GN" ,
            __('GUINEA-BISSAU', 'gravityforms') => "GW" ,
            __('GUYANA', 'gravityforms') => "GY" ,
            __('HAITI', 'gravityforms') => "HT" ,
            __('HONDURAS', 'gravityforms') => "HN" ,
            __('HONG KONG', 'gravityforms') => "HK" ,
            __('HUNGARY', 'gravityforms') => "HU" ,
            __('ICELAND', 'gravityforms') => "IS" ,
            __('INDIA', 'gravityforms') => "IN" ,
            __('INDONESIA', 'gravityforms') => "ID" ,
            __('IRAN', 'gravityforms') => "IR" ,
            __('IRAQ', 'gravityforms') => "IQ" ,
            __('IRELAND', 'gravityforms') => "IE" ,
            __('ISRAEL', 'gravityforms') => "IL" ,
            __('ITALY', 'gravityforms') => "IT" ,
            __('JAMAICA', 'gravityforms') => "JM" ,
            __('JAPAN', 'gravityforms') => "JP" ,
            __('JORDAN', 'gravityforms') => "JO" ,
            __('KAZAKHSTAN', 'gravityforms') => "KZ" ,
            __('KENYA', 'gravityforms') => "KE" ,
            __('KIRIBATI', 'gravityforms') => "KI" ,
            __('NORTH KOREA', 'gravityforms') => "KP" ,
            __('SOUTH KOREA', 'gravityforms') => "KR" ,
            __('KUWAIT', 'gravityforms') => "KW" ,
            __('KYRGYZSTAN', 'gravityforms') => "KG" ,
            __('LAOS', 'gravityforms') => "LA" ,
            __('LATVIA', 'gravityforms') => "LV" ,
            __('LEBANON', 'gravityforms') => "LB" ,
            __('LESOTHO', 'gravityforms') => "LS" ,
            __('LIBERIA', 'gravityforms') => "LR" ,
            __('LIBYA', 'gravityforms') => "LY" ,
            __('LIECHTENSTEIN', 'gravityforms') => "LI" ,
            __('LITHUANIA', 'gravityforms') => "LT" ,
            __('LUXEMBOURG', 'gravityforms') => "LU" ,
            __('MACEDONIA', 'gravityforms') => "MK" ,
            __('MADAGASCAR', 'gravityforms') => "MG" ,
            __('MALAWI', 'gravityforms') => "MW" ,
            __('MALAYSIA', 'gravityforms') => "MY" ,
            __('MALDIVES', 'gravityforms') => "MV" ,
            __('MALI', 'gravityforms') => "ML" ,
            __('MALTA', 'gravityforms') => "MT" ,
            __('MARSHALL ISLANDS', 'gravityforms') => "MH" ,
            __('MAURITANIA', 'gravityforms') => "MR" ,
            __('MAURITIUS', 'gravityforms') => "MU" ,
            __('MEXICO', 'gravityforms') => "MX" ,
            __('MICRONESIA', 'gravityforms') => "FM" ,
            __('MOLDOVA', 'gravityforms') => "MD" ,
            __('MONACO', 'gravityforms') => "MC" ,
            __('MONGOLIA', 'gravityforms') => "MN" ,
            __('MONTENEGRO', 'gravityforms') => "ME" ,
            __('MOROCCO', 'gravityforms') => "MA" ,
            __('MOZAMBIQUE', 'gravityforms') => "MZ" ,
            __('MYANMAR', 'gravityforms') => "MM" ,
            __('NAMIBIA', 'gravityforms') => "NA" ,
            __('NAURU', 'gravityforms') => "NR" ,
            __('NEPAL', 'gravityforms') => "NP" ,
            __('NETHERLANDS', 'gravityforms') => "NL" ,
            __('NEW ZEALAND', 'gravityforms') => "NZ" ,
            __('NICARAGUA', 'gravityforms') => "NI" ,
            __('NIGER', 'gravityforms') => "NE" ,
            __('NIGERIA', 'gravityforms') => "NG" ,
            __('NORTHERN MARIANA ISLANDS', 'gravityforms') => "MP" ,
            __('NORWAY', 'gravityforms') => "NO" ,
            __('OMAN', 'gravityforms') => "OM" ,
            __('PAKISTAN', 'gravityforms') => "PK" ,
            __('PALAU', 'gravityforms') => "PW" ,
            __('PALESTINE', 'gravityforms') => "PS" ,
            __('PANAMA', 'gravityforms') => "PA" ,
            __('PAPUA NEW GUINEA', 'gravityforms') => "PG" ,
            __('PARAGUAY', 'gravityforms') => "PY" ,
            __('PERU', 'gravityforms') => "PE" ,
            __('PHILIPPINES', 'gravityforms') => "PH" ,
            __('POLAND', 'gravityforms') => "PL" ,
            __('PORTUGAL', 'gravityforms') => "PT" ,
            __('PUERTO RICO', 'gravityforms') => "PR" ,
            __('QATAR', 'gravityforms') => "QA" ,
            __('ROMANIA', 'gravityforms') => "RO" ,
            __('RUSSIA', 'gravityforms') => "RU" ,
            __('RWANDA', 'gravityforms') => "RW" ,
            __('SAINT KITTS AND NEVIS', 'gravityforms') => "KN" ,
            __('SAINT LUCIA', 'gravityforms') => "LC" ,
            __('SAINT VINCENT AND THE GRENADINES', 'gravityforms') => "VC" ,
            __('SAMOA', 'gravityforms') => "WS" ,
            __('SAN MARINO', 'gravityforms') => "SM" ,
            __('SAO TOME AND PRINCIPE', 'gravityforms') => "ST" ,
            __('SAUDI ARABIA', 'gravityforms') => "SA" ,
            __('SENEGAL', 'gravityforms') => "SN" ,
            __('SERBIA AND MONTENEGRO', 'gravityforms') => "RS" ,
            __('SEYCHELLES', 'gravityforms') => "SC" ,
            __('SIERRA LEONE', 'gravityforms') => "SL" ,
            __('SINGAPORE', 'gravityforms') => "SG" ,
            __('SLOVAKIA', 'gravityforms') => "SK" ,
            __('SLOVENIA', 'gravityforms') => "SI" ,
            __('SOLOMON ISLANDS', 'gravityforms') => "SB" ,
            __('SOMALIA', 'gravityforms') => "SO" ,
            __('SOUTH AFRICA', 'gravityforms') => "ZA" ,
            __('SPAIN', 'gravityforms') => "ES" ,
            __('SRI LANKA', 'gravityforms') => "LK" ,
            __('SUDAN', 'gravityforms') => "SD" ,
            __('SURINAME', 'gravityforms') => "SR" ,
            __('SWAZILAND', 'gravityforms') => "SZ" ,
            __('SWEDEN', 'gravityforms') => "SE" ,
            __('SWITZERLAND', 'gravityforms') => "CH" ,
            __('SYRIA', 'gravityforms') => "SY" ,
            __('TAIWAN', 'gravityforms') => "TW" ,
            __('TAJIKISTAN', 'gravityforms') => "TJ" ,
            __('TANZANIA', 'gravityforms') => "TZ" ,
            __('THAILAND', 'gravityforms') => "TH" ,
            __('TOGO', 'gravityforms') => "TG" ,
            __('TONGA', 'gravityforms') => "TO" ,
            __('TRINIDAD AND TOBAGO', 'gravityforms') => "TT" ,
            __('TUNISIA', 'gravityforms') => "TN" ,
            __('TURKEY', 'gravityforms') => "TR" ,
            __('TURKMENISTAN', 'gravityforms') => "TM" ,
            __('TUVALU', 'gravityforms') => "TV" ,
            __('UGANDA', 'gravityforms') => "UG" ,
            __('UKRAINE', 'gravityforms') => "UA" ,
            __('UNITED ARAB EMIRATES', 'gravityforms') => "AE" ,
            __('UNITED KINGDOM', 'gravityforms') => "GB" ,
            __('UNITED STATES', 'gravityforms') => "US" ,
            __('URUGUAY', 'gravityforms') => "UY" ,
            __('UZBEKISTAN', 'gravityforms') => "UZ" ,
            __('VANUATU', 'gravityforms') => "VU" ,
            __('VATICAN CITY', 'gravityforms') => "" ,
            __('VENEZUELA', 'gravityforms') => "VE" ,
            __('VIRGIN ISLANDS, BRITISH', 'gravityforms') => "VG" ,
            __('VIRGIN ISLANDS, U.S.', 'gravityforms') => "VI" ,
            __('VIETNAM', 'gravityforms') => "VN" ,
            __('YEMEN', 'gravityforms') => "YE" ,
            __('ZAMBIA', 'gravityforms') => "ZM" ,
            __('ZIMBABWE', 'gravityforms') => "ZW" );

            return $codes[strtoupper($country_name)];
    }

    public static function get_us_states(){
        return array(__("Alabama","gravityforms"),__("Alaska","gravityforms"),__("Arizona","gravityforms"),__("Arkansas","gravityforms"),__("California","gravityforms"),__("Colorado","gravityforms"),__("Connecticut","gravityforms"),__("Delaware","gravityforms"),__("District of Columbia", "gravityforms"), __("Florida","gravityforms"),__("Georgia","gravityforms"),__("Hawaii","gravityforms"),__("Idaho","gravityforms"),__("Illinois","gravityforms"),__("Indiana","gravityforms"),__("Iowa","gravityforms"),__("Kansas","gravityforms"),__("Kentucky","gravityforms"),__("Louisiana","gravityforms"),__("Maine","gravityforms"),__("Maryland","gravityforms"),__("Massachusetts","gravityforms"),__("Michigan","gravityforms"),__("Minnesota","gravityforms"),__("Mississippi","gravityforms"),__("Missouri","gravityforms"),__("Montana","gravityforms"),__("Nebraska","gravityforms"),__("Nevada","gravityforms"),__("New Hampshire","gravityforms"),__("New Jersey","gravityforms"),__("New Mexico","gravityforms"),__("New York","gravityforms"),__("North Carolina","gravityforms"),__("North Dakota","gravityforms"),__("Ohio","gravityforms"),__("Oklahoma","gravityforms"),__("Oregon","gravityforms"),__("Pennsylvania","gravityforms"),__("Rhode Island","gravityforms"),__("South Carolina","gravityforms"),__("South Dakota","gravityforms"),__("Tennessee","gravityforms"),__("Texas","gravityforms"),__("Utah","gravityforms"),__("Vermont","gravityforms"),__("Virginia","gravityforms"),__("Washington","gravityforms"),__("West Virginia","gravityforms"),__("Wisconsin","gravityforms"),__("Wyoming","gravityforms"));
    }

    public static function get_canadian_provinces(){
        return array(__("Alberta","gravityforms"),__("British Columbia","gravityforms"),__("Manitoba","gravityforms"),__("New Brunswick","gravityforms"),__("Newfoundland & Labrador","gravityforms"),__("Northwest Territories","gravityforms"),__("Nova Scotia","gravityforms"),__("Nunavut","gravityforms"),__("Ontario","gravityforms"),__("Prince Edward Island","gravityforms"),__("Quebec","gravityforms"),__("Saskatchewan","gravityforms"),__("Yukon","gravityforms"));
    }

    public static function get_state_dropdown($states, $selected_state=""){
        foreach($states as $state){
            $selected = $state == $selected_state ? "selected='selected'" : "";
            $str .= "<option value='" . esc_attr($state) . "' $selected>" . $state . "</option>";
        }
        return $str;
    }

    public static function get_us_state_dropdown($selected_state = ""){
        $states = array_merge(array(''), self::get_us_states());
        foreach($states as $state){
            $selected = $state == $selected_state ? "selected='selected'" : "";
            $str .= "<option value='" . esc_attr($state) . "' $selected>" . $state . "</option>";
        }
        return $str;
    }

    public static function get_canadian_provinces_dropdown($selected_province = ""){
        $states = array_merge(array(''), self::get_canadian_provinces());
        foreach($states as $state){
            $selected = $state == $selected_province ? "selected='selected'" : "";
            $str .= "<option value='" . esc_attr($state) . "' $selected>" . $state . "</option>";
        }
        return $str;
    }

    public static function get_country_dropdown($selected_country = ""){
        $countries = array_merge(array(''), self::get_countries());
        foreach($countries as $country){
            $selected = $country == $selected_country ? "selected='selected'" : "";
            $str .= "<option value='" . esc_attr($country) . "' $selected>" . $country . "</option>";
        }
        return $str;
    }

    private static function is_post_field($field){
        return in_array($field["type"], array("post_title", "post_tags", "post_category", "post_custom_field", "post_content", "post_excerpt", "post_image"));
    }

    public static function get_range_message($field){
        $min = $field["rangeMin"];
        $max = $field["rangeMax"];

        if(is_numeric($min) && is_numeric($max))
            $message =  __(sprintf("Please enter a value between %s and %s.", "<strong>$min</strong>", "<strong>$max</strong>"), "gravityforms") ;
        else if(is_numeric($min))
            $message = __(sprintf("Please enter a value greater than or equal to %s.", "<strong>$min</strong>"), "gravityforms");
        else if(is_numeric($max))
            $message = __(sprintf("Please enter a value less than or equal to %s.", "<strong>$max</strong>"), "gravityforms");
        else
            $message = "";

        return $message;
    }

    public static function get_field_input($field, $value="", $lead_id=0, $form_id=0){

        $id = $field["id"];
        $field_id = IS_ADMIN || $form_id == 0 ? "input_$id" : "input_" . $form_id . "_$id";
        $form_id = IS_ADMIN && empty($form_id) ? $_GET["id"] : $form_id;

        $size = $field["size"];
        $disabled_text = (IS_ADMIN && RG_CURRENT_VIEW != "entry") ? "disabled='disabled'" : "";
        $class_suffix = RG_CURRENT_VIEW == "entry" ? "_admin" : "";
        $class = $size . $class_suffix;

        if(RG_CURRENT_VIEW == "entry"){
            $lead = RGFormsModel::get_lead($lead_id);
            $post_id = $lead["post_id"];
            $post_link = "";
            if(is_numeric($post_id) && self::is_post_field($field)){
                $post_link = "You can <a href='post.php?action=edit&post=$post_id'>edit this post</a> from the post page.";
            }
        }

        $html5_attributes = "";
        switch(RGFormsModel::get_input_type($field)){

            case "website":
                $is_html5 = RGFormsModel::is_html5_enabled();
                $value = empty($value) && !$is_html5 ? "http://" : $value;
                $html_input_type = $is_html5 ? "url" : "text";
                $html5_attributes = $is_html5 ? "placeholder='http://'" : "";
            case "text":
                if(empty($html_input_type))
                    $html_input_type = "text";

                if($field["enablePasswordInput"] && RG_CURRENT_VIEW != "entry")
                    $html_input_type = "password";

                if(is_numeric($field["maxLength"]))
                    $max_length = "maxlength='{$field["maxLength"]}'";

            case "email":
                if(!empty($post_link))
                    return $post_link;

                if(empty($html_input_type))
                    $html_input_type = RGFormsModel::is_html5_enabled() ? "email" : "text";

                $tabindex = self::get_tabindex();
                return sprintf("<div class='ginput_container'><input name='input_%d' id='%s' type='%s' value='%s' class='%s' $max_length $tabindex $html5_attributes %s/></div>", $id, $field_id, $html_input_type, esc_attr($value), esc_attr($class), $disabled_text);
            break;
            case "honeypot":
                return "<div class='ginput_container'><input name='input_{$id}' type='text' value=''/></div>";
            break;

            case "hidden" :
                if(!empty($post_link))
                    return $post_link;

                $field_type = IS_ADMIN ? "text" : "hidden";
                $class_attribute = IS_ADMIN ? "" : "class='gform_hidden'";

                return sprintf("<input name='input_%d' id='%s' type='$field_type' $class_attribute value='%s' %s/>", $id, $field_id, esc_attr($value), $disabled_text);
            break;

            case "html" :
                $content = IS_ADMIN ? "<img class='gfield_html_block' src='" . self::get_base_url() . "/images/html_placeholder.png' alt='HTML Block'/>" : $field["content"];
                return $content;
            break;

            case "adminonly_hidden" :
                if(!is_array($field["inputs"]))
                    return sprintf("<input name='input_%d' id='%s' class='gform_hidden' type='hidden' value='%s'/>", $id, $field_id, esc_attr($value));

                $fields = "";
                foreach($field["inputs"] as $input){
                    $fields .= sprintf("<input name='input_%s' class='gform_hidden' type='hidden' value='%s'/>", $input["id"], esc_attr($value[$input["id"]]));
                }
                return $fields;
            break;

            case "number" :
                if(!empty($post_link))
                    return $post_link;

                if(!IS_ADMIN){
                    $min = $field["rangeMin"];
                    $max = $field["rangeMax"];
                    $validation_class = $field["failed_validation"] ? "validation_message" : "";
                    $message = self::get_range_message($field);

                    if(!$field["failed_validation"] && !empty($message) && empty($field["errorMessage"]))
                        $instruction = "<div class='instruction $validation_class'>" . $message . "</div>";

                }
                $html_input_type = RGFormsModel::is_html5_enabled() ? "number" : "text";

                $tabindex = self::get_tabindex();
                return sprintf("<div class='ginput_container'><input name='input_%d' id='%s' type='{$html_input_type}' value='%s' class='%s' $tabindex %s/>%s</div>", $id, $field_id, esc_attr($value), esc_attr($class),  $disabled_text, $instruction);

            case "phone" :
                if(!empty($post_link))
                    return $post_link;

                $instruction = $field["phoneFormat"] == "standard" ? __("Phone format:", "gravityforms") . " (###)###-####" : "";
                $instruction_div = $field["failed_validation"] ? "<div class='instruction validation_message'>$instruction</div>" : "";
                $html_input_type = RGFormsModel::is_html5_enabled() ? "tel" : "text";

                $tabindex = self::get_tabindex();
                return sprintf("<div class='ginput_container'><input name='input_%d' id='%s' type='{$html_input_type}' value='%s' class='%s' $tabindex %s/>$instruction_div</div>", $id, $field_id, esc_attr($value), esc_attr($class), $disabled_text);

            case "textarea":
                $tabindex = self::get_tabindex();
                return sprintf("<div class='ginput_container'><textarea name='input_%d' id='%s' class='textarea %s' $tabindex %s rows='10' cols='50'>%s</textarea></div>", $id, $field_id, esc_attr($class), $disabled_text, esc_html($value));

            case "post_title":
            case "post_tags":
            case "post_custom_field":
                $tabindex = self::get_tabindex();
                return !empty($post_link) ? $post_link : sprintf("<div class='ginput_container'><input name='input_%d' id='%s' type='text' value='%s' class='%s' $tabindex %s/></div>", $id, $field_id, esc_attr($value), esc_attr($class), $disabled_text);
            break;

            case "post_content":
            case "post_excerpt":
                $tabindex = self::get_tabindex();
                return !empty($post_link) ? $post_link : sprintf("<div class='ginput_container'><textarea name='input_%d' id='%s' class='textarea %s' $tabindex %s rows='10' cols='50'>%s</textarea></div>", $id, $field_id, esc_attr($class), $disabled_text, esc_html($value));
            break;

            case "post_category" :
                if(!empty($post_link))
                    return $post_link;

                if($field["displayAllCategories"] && !IS_ADMIN){
                    $selected = empty($value) ? get_option('default_category') : $value;
                    $args = array('echo' => 0, 'selected' => $selected, "class" => esc_attr($class) . " gfield_select",  'hide_empty' => 0, 'name' => "input_$id", 'orderby' => 'name', 'hierarchical' => true );
                    if(self::$tab_index > 0)
                        $args["tab_index"] = self::$tab_index++;

                    return "<div class='ginput_container'>" . wp_dropdown_categories($args) . "</div>";
                }
                else{
                    $tabindex = self::get_tabindex();
                    return sprintf("<div class='ginput_container'><select name='input_%d' id='%s' class='%s gfield_select' %s>%s</select></div>", $id, $field_id, esc_attr($class), $disabled_text, self::get_select_choices($field, $value));
                }
            break;

            case "post_image" :
                if(!empty($post_link))
                    return $post_link;

                $title = esc_attr($value[$field["id"] . ".1"]);
                $caption = esc_attr($value[$field["id"] . ".4"]);
                $description = esc_attr($value[$field["id"] . ".7"]);

                //hidding meta fields for admin
                $hidden_style = "style='display:none;'";
                $title_style = !$field["displayTitle"] && IS_ADMIN ? $hidden_style : "";
                $caption_style = !$field["displayCaption"] && IS_ADMIN ? $hidden_style : "";
                $description_style = !$field["displayDescription"] && IS_ADMIN ? $hidden_style : "";
                $file_label_style = IS_ADMIN && !($field["displayTitle"] || $field["displayCaption"] || $field["displayDescription"]) ? $hidden_style : "";

                //in admin, render all meta fields to allow for immediate feedback, but hide the ones not selected
                $file_label = (IS_ADMIN || $field["displayTitle"] || $field["displayCaption"] || $field["displayDescription"]) ? "<label for='$field_id' class='ginput_post_image_file' $file_label_style>" . apply_filters("gform_postimage_file_{$form_id}",apply_filters("gform_postimage_file",__("File", "gravityforms"), $form_id), $form_id) . "</label>" : "";

                $tabindex = self::get_tabindex();
                $upload = sprintf("<span class='ginput_full$class_suffix'><input name='input_%d' id='%s' type='file' value='%s' class='%s' $tabindex %s/>$file_label</span>", $id, $field_id, esc_attr($value), esc_attr($class), $disabled_text);

                $tabindex = self::get_tabindex();
                $title_field = $field["displayTitle"] || IS_ADMIN ? sprintf("<span class='ginput_full$class_suffix ginput_post_image_title' $title_style><input type='text' name='input_%d.1' id='%s.1' value='%s' $tabindex %s/><label for='%s.1'>" . apply_filters("gform_postimage_title_{$form_id}",apply_filters("gform_postimage_title",__("Title", "gravityforms"), $form_id), $form_id) . "</label></span>", $id, $field_id, $title, $disabled_text, $field_id) : "";

                $tabindex = self::get_tabindex();
                $caption_field = $field["displayCaption"] || IS_ADMIN ? sprintf("<span class='ginput_full$class_suffix ginput_post_image_caption' $caption_style><input type='text' name='input_%d.4' id='%s.4' value='%s' $tabindex %s/><label for='%s.4'>" . apply_filters("gform_postimage_caption_{$form_id}",apply_filters("gform_postimage_caption",__("Caption", "gravityforms"), $form_id), $form_id) . "</label></span>", $id, $field_id, $caption, $disabled_text, $field_id) : "";

                $tabindex = self::get_tabindex();
                $description_field = $field["displayDescription"] || IS_ADMIN? sprintf("<span class='ginput_full$class_suffix ginput_post_image_description' $description_style><input type='text' name='input_%d.7' id='%s.7' value='%s' $tabindex %s/><label for='%s.7'>" . apply_filters("gform_postimage_description_{$form_id}",apply_filters("gform_postimage_description",__("Description", "gravityforms"), $form_id), $form_id) . "</label></span>", $id, $field_id, $description, $disabled_text, $field_id) : "";

                return "<div class='ginput_complex$class_suffix ginput_container'>" . $upload . $title_field . $caption_field . $description_field . "</div>";

            break;
            case "select" :
                if(!empty($post_link))
                    return $post_link;

                $logic_event = empty($field["conditionalLogicFields"]) || IS_ADMIN ? "" : "onchange='gf_apply_rules(" . $field["formId"] . "," . GFCommon::json_encode($field["conditionalLogicFields"]) . ");'";
                $css_class = trim(esc_attr($class) . " gfield_select");
                $tabindex = self::get_tabindex();
                return sprintf("<div class='ginput_container'><select name='input_%d' id='%s' $logic_event class='%s' $tabindex %s>%s</select></div>", $id, $field_id, $css_class, $disabled_text, self::get_select_choices($field, $value));

            case "checkbox" :
                return sprintf("<div class='ginput_container'><ul class='gfield_checkbox' id='%s'>%s</ul></div>", $field_id, self::get_checkbox_choices($field, $value, $disabled_text));

            case "radio" :
                if(!empty($post_link))
                    return $post_link;

                return sprintf("<div class='ginput_container'><ul class='gfield_radio' id='%s'>%s</ul></div>", $field_id, self::get_radio_choices($field, $value, $disabled_text));

            case "name" :
                if(is_array($value)){
                    $prefix = esc_attr($value[$field["id"] . ".2"]);
                    $first = esc_attr($value[$field["id"] . ".3"]);
                    $last = esc_attr($value[$field["id"] . ".6"]);
                    $suffix = esc_attr($value[$field["id"] . ".8"]);
                }
                switch($field["nameFormat"]){

                    case "extended" :
                        $prefix_tabindex = self::get_tabindex();
                        $first_tabindex = self::get_tabindex();
                        $last_tabindex = self::get_tabindex();
                        $suffix_tabindex = self::get_tabindex();
                        return sprintf("<div class='ginput_complex$class_suffix ginput_container' id='$field_id'><span id='" . $field_id . "_2_container' class='name_prefix'><input type='text' name='input_%d.2' id='%s.2' value='%s' $prefix_tabindex %s/><label for='%s.2'>" . apply_filters("gform_name_prefix_{$form_id}",apply_filters("gform_name_prefix",__("Prefix", "gravityforms"), $form_id), $form_id) . "</label></span><span id='" . $field_id . "_3_container' class='name_first'><input type='text' name='input_%d.3' id='%s.3' value='%s' $first_tabindex %s/><label for='%s.3'>" . apply_filters("gform_name_first_{$form_id}",apply_filters("gform_name_first",__("First", "gravityforms"), $form_id), $form_id) . "</label></span><span id='" . $field_id . "_6_container' class='name_last'><input type='text' name='input_%d.6' id='%s.6' value='%s' $last_tabindex %s/><label for='%s.6'>" . apply_filters("gform_name_last_{$form_id}", apply_filters("gform_name_last", __("Last", "gravityforms"), $form_id), $form_id) . "</label></span><span id='" . $field_id . "_8_container' class='name_suffix'><input type='text' name='input_%d.8' id='%s.8' value='%s' $suffix_tabindex %s/><label for='%s.8'>" . apply_filters("gform_name_suffix_{$form_id}", apply_filters("gform_name_suffix", __("Suffix", "gravityforms"), $form_id), $form_id) . "</label></span></div>", $id, $field_id, $prefix, $disabled_text, $field_id, $id, $field_id, $first, $disabled_text, $field_id, $id, $field_id, $last, $disabled_text, $field_id, $id, $field_id, $suffix, $disabled_text, $field_id);

                    case "simple" :
                        $tabindex = self::get_tabindex();
                        return sprintf("<div class='ginput_container'><input name='input_%d' id='%s' type='text' value='%s' class='%s' $tabindex %s/></div>", $id, $field_id, esc_attr($value), esc_attr($class), $disabled_text);

                    default :
                        $first_tabindex = self::get_tabindex();
                        $last_tabindex = self::get_tabindex();
                        return sprintf("<div class='ginput_complex$class_suffix ginput_container' id='$field_id'><span id='" . $field_id . "_3_container' class='ginput_left'><input type='text' name='input_%d.3' id='%s.3' value='%s' $first_tabindex %s/><label for='%s.3'>" . apply_filters("gform_name_first_{$form_id}", apply_filters("gform_name_first",__("First", "gravityforms"), $form_id), $form_id) . "</label></span><span id='" . $field_id . "_6_container' class='ginput_right'><input type='text' name='input_%d.6' id='%s.6' value='%s' $last_tabindex %s/><label for='%s.6'>" . apply_filters("gform_name_last_{$form_id}", apply_filters("gform_name_last",__("Last", "gravityforms"), $form_id), $form_id) . "</label></span></div>", $id, $field_id, $first, $disabled_text, $field_id, $id, $field_id, $last, $disabled_text, $field_id);
                }

            case "address" :
                if(is_array($value)){
                    $street_value = esc_attr($value[$field["id"] . ".1"]);
                    $street2_value = esc_attr($value[$field["id"] . ".2"]);
                    $city_value = esc_attr($value[$field["id"] . ".3"]);
                    $state_value = esc_attr($value[$field["id"] . ".4"]);
                    $zip_value = esc_attr($value[$field["id"] . ".5"]);
                    $country_value = esc_attr($value[$field["id"] . ".6"]);
                }

                $address_types = self::get_address_types($form_id);
                $addr_type = empty($field["addressType"]) ? "international" : $field["addressType"];
                $address_type = $address_types[$addr_type];

                $state_label = empty($address_type["state_label"]) ? __("State", "gravityforms") : $address_type["state_label"];
                $zip_label = empty($address_type["zip_label"]) ? __("Zip Code", "gravityforms") : $address_type["zip_label"];
                $hide_country = !empty($address_type["country"]) || $field["hideCountry"];

                if(empty($country_value))
                    $country_value = $field["defaultCountry"];

                if(empty($state_value))
                    $state_value = $field["defaultState"];

                $country_list = self::get_country_dropdown($country_value);

                //address field
                $tabindex = self::get_tabindex();
                $street_address = sprintf("<span class='ginput_full$class_suffix' id='" . $field_id . "_1_container'><input type='text' name='input_%d.1' id='%s_1' value='%s' $tabindex %s/><label for='%s_1' id='" . $field_id . "_1_label'>" . apply_filters("gform_address_street_{$form_id}", apply_filters("gform_address_street",__("Street Address", "gravityforms"), $form_id), $form_id) . "</label></span>", $id, $field_id, $street_value, $disabled_text, $field_id);

                //address line 2 field
                $style = (IS_ADMIN && $field["hideAddress2"]) ? "style='display:none;'" : "";
                if(IS_ADMIN || !$field["hideAddress2"]){
                    $tabindex = self::get_tabindex();
                    $street_address2 = sprintf("<span class='ginput_full$class_suffix' id='" . $field_id . "_2_container' $style><input type='text' name='input_%d.2' id='%s_2' value='%s' $tabindex %s/><label for='%s_2' id='" . $field_id . "_2_label'>" . apply_filters("gform_address_street2_{$form_id}",apply_filters("gform_address_street2",__("Address Line 2", "gravityforms"), $form_id), $form_id) . "</label></span>", $id, $field_id, $street2_value, $disabled_text, $field_id);
                }

                //city field
                $tabindex = self::get_tabindex();
                $city = sprintf("<span class='ginput_left$class_suffix' id='" . $field_id . "_3_container'><input type='text' name='input_%d.3' id='%s_3' value='%s' $tabindex %s/><label for='%s_3' id='$field_id.3_label'>" . apply_filters("gform_address_city_{$form_id}", apply_filters("gform_address_city",__("City", "gravityforms"), $form_id), $form_id) . "</label></span>", $id, $field_id, $city_value, $disabled_text, $field_id);

                //state field
                $style = (IS_ADMIN && $field["hideState"]) ? "style='display:none;'" : "";
                if(IS_ADMIN || !$field["hideState"]){
                    $state_field = self::get_state_field($field, $id, $field_id, $state_value, $disabled_text, $form_id);
                    $state = sprintf("<span class='ginput_right$class_suffix' id='" . $field_id . "_4_container' $style>$state_field<label for='%s.4' id='" . $field_id . "_4_label'>" . apply_filters("gform_address_state_{$form_id}", apply_filters("gform_address_state", $state_label, $form_id), $form_id) . "</label></span>", $field_id);
                }
                else{
                    $state = sprintf("<input type='hidden' class='gform_hidden' name='input_%d.4' id='%s_4' value='%s'/>", $id, $field_id, $state_value);
                }

                //zip field
                $tabindex = self::get_tabindex();
                $zip = sprintf("<span class='ginput_left$class_suffix' id='" . $field_id . "_5_container'><input type='text' name='input_%d.5' id='%s_5' value='%s' $tabindex %s/><label for='%s_5' id='" . $field_id . "_5_label'>" . apply_filters("gform_address_zip_{$form_id}", apply_filters("gform_address_zip", $zip_label, $form_id), $form_id) . "</label></span>", $id, $field_id, $zip_value, $disabled_text, $field_id);

                if(IS_ADMIN || !$hide_country){
                    $style = $hide_country ? "style='display:none;'" : "";
                    $tabindex = self::get_tabindex();
                    $country = sprintf("<span class='ginput_right$class_suffix' id='" . $field_id . "_6_container' $style><select name='input_%d.6' id='%s_6' $tabindex %s>%s</select><label for='%s_6' id='" . $field_id . "_6_label'>" . apply_filters("gform_address_country_{$form_id}", apply_filters("gform_address_country",__("Country", "gravityforms"), $form_id), $form_id) . "</label></span>", $id, $field_id, $disabled_text, $country_list, $field_id);
                }
                else{
                    $country = sprintf("<input type='hidden' class='gform_hidden' name='input_%d.6' id='%s_6' value='%s'/>", $id, $field_id, $country_value);
                }

                return "<div class='ginput_complex$class_suffix ginput_container' id='$field_id'>" . $street_address . $street_address2 . $city . $state . $zip . $country . "</div>";

            case "date" :
                if(!empty($post_link))
                    return $post_link;

                $format = empty($field["dateFormat"]) ? "mdy" : esc_attr($field["dateFormat"]);

                if(IS_ADMIN && RG_CURRENT_VIEW != "entry"){
                    $datepicker_display = $field["dateType"] == "datefield" ? "none" : "inline";
                    $dropdown_display = $field["dateType"] == "datefield" ? "inline" : "none";
                    $icon_display = $field["calendarIconType"] == "calendar" ? "inline" : "none";

                    $month_field = "<div class='gfield_date_month ginput_date' id='gfield_input_date_month' style='display:$dropdown_display'><input name='ginput_month' type='text' disabled='disabled'/><label>" . __("MM", "gravityforms") . "</label></div>";
                    $day_field = "<div class='gfield_date_day ginput_date' id='gfield_input_date_day' style='display:$dropdown_display'><input name='ginput_day' type='text' disabled='disabled'/><label>" . __("DD", "gravityforms") . "</label></div>";
                    $year_field = "<div class='gfield_date_year ginput_date' id='gfield_input_date_year' style='display:$dropdown_display'><input type='text' name='ginput_year' disabled='disabled'/><label>" . __("YYYY", "gravityforms") . "</label></div>";

                    $field_string ="<div class='ginput_container' id='gfield_input_datepicker' style='display:$datepicker_display'><input name='ginput_datepicker' type='text' /><img src='" . GFCommon::get_base_url() . "/images/calendar.png' id='gfield_input_datepicker_icon' style='display:$icon_display'/></div>";
                    $field_string .= $field["dateFormat"] == "dmy" ? $day_field . $month_field . $year_field : $month_field . $day_field . $year_field;

                    return $field_string;
                }
                else{
                    $date_info = GFCommon::parse_date($value, $format);

                    if($field["dateType"] == "datefield")
                    {
                        if($format == "mdy"){
                            $tabindex = self::get_tabindex();
                            $field_str = sprintf("<div class='clear-multi'><div class='gfield_date_month ginput_container' id='%s'><input type='text' maxlength='2' name='input_%d[]' id='%s.1' value='%s' $tabindex %s/><label for='%s.1'>" . __("MM", "gravityforms") . "</label></div>", $field_id, $id, $field_id, $date_info["month"], $disabled_text, $field_id);

                            $tabindex = self::get_tabindex();
                            $field_str .= sprintf("<div class='gfield_date_day ginput_container' id='%s'><input type='text' maxlength='2' name='input_%d[]' id='%s.2' value='%s' $tabindex %s/><label for='%s.2'>" . __("DD", "gravityforms") . "</label></div>", $field_id, $id, $field_id, $date_info["day"], $disabled_text, $field_id);
                        }
                        else{
                            $tabindex = self::get_tabindex();
                            $field_str = sprintf("<div class='clear-multi'><div class='gfield_date_day ginput_container' id='%s'><input type='text' maxlength='2' name='input_%d[]' id='%s.2' value='%s' $tabindex %s/><label for='%s.2'>" . __("DD", "gravityforms") . "</label></div>", $field_id, $id, $field_id, $date_info["day"], $disabled_text, $field_id);

                            $tabindex = self::get_tabindex();
                            $field_str .= sprintf("<div class='gfield_date_month ginput_container' id='%s'><input type='text' maxlength='2' name='input_%d[]' id='%s.1' value='%s' $tabindex %s/><label for='%s.1'>" . __("MM", "gravityforms") . "</label></div>", $field_id, $id, $field_id, $date_info["month"], $disabled_text, $field_id);
                        }

                        $tabindex = self::get_tabindex();
                        $field_str .= sprintf("<div class='gfield_date_year ginput_container' id='%s'><input type='text' maxlength='4' name='input_%d[]' id='%s.3' value='%s' $tabindex %s/><label for='%s.3'>" . __("YYYY", "gravityforms") . "</label></div></div>", $field_id, $id, $field_id, $date_info["year"], $disabled_text, $field_id);

                        return $field_str;
                    }
                    else
                    {
                        $value = GFCommon::date_display($value, $format);
                        $icon_class = $field["calendarIconType"] == "none" ? "datepicker_no_icon" : "datepicker_with_icon";
                        $icon_url = empty($field["calendarIconUrl"]) ? GFCommon::get_base_url() . "/images/calendar.png" : $field["calendarIconUrl"];
                        $tabindex = self::get_tabindex();
                        return sprintf("<div class='ginput_container'><input name='input_%d' id='%s' type='text' value='%s' class='datepicker %s %s %s' $tabindex %s/>%s</div><input type='hidden' id='gforms_calendar_icon_$field_id' class='gform_hidden' value='$icon_url'/>", $id, $field_id, esc_attr($value), esc_attr($class), $format, $icon_class, $disabled_text, $datepicker);
                    }
                }

            case "time" :
                if(!empty($post_link))
                    return $post_link;

                if(!is_array($value) && !empty($value)){
                    preg_match('/^(\d*):(\d*) (.*)$/', $value, $matches);
                    $hour = esc_attr($matches[1]);
                    $minute = esc_attr($matches[2]);
                    $am_selected = $matches[3] == "am" ? "selected='selected'" : "";
                    $pm_selected = $matches[3] == "pm" ? "selected='selected'" : "";
                }
                else{
                    $hour = esc_attr($value[0]);
                    $minute = esc_attr($value[1]);
                    $am_selected = $value[2] == "am" ? "selected='selected'" : "";
                    $pm_selected = $value[2] == "pm" ? "selected='selected'" : "";
                }
                $hour_tabindex = self::get_tabindex();
                $minute_tabindex = self::get_tabindex();
                $ampm_tabindex = self::get_tabindex();
                return sprintf("<div class='clear-multi'><div class='gfield_time_hour ginput_container' id='%s'><input type='text' maxlength='2' name='input_%d[]' id='%s.1' value='%s' $hour_tabindex %s/> : <label for='%s.1'>" . __("HH", "gravityforms") . "</label></div><div class='gfield_time_minute ginput_container'><input type='text' maxlength='2' name='input_%d[]' id='%s.2' value='%s' $minute_tabindex %s/><label for='%s.2'>" . __("MM", "gravityforms") . "</label></div><div class='gfield_time_ampm ginput_container'><select name='input_%d[]' id='%s.3' $ampm_tabindex %s><option value='am' %s>" . __("AM", "gravityforms") . "</option><option value='pm' %s>" . __("PM", "gravityforms") . "</option></select></div></div>", $field_id, $id, $field_id, $hour, $disabled_text, $field_id, $id, $field_id, $minute, $disabled_text, $field_id, $id, $field_id, $disabled_text, $am_selected, $pm_selected);

            case "fileupload" :
                $tabindex = self::get_tabindex();
                $upload = sprintf("<input name='input_%d' id='%s' type='file' value='%s' size='20' class='%s' $tabindex %s/>", $id, $field_id, esc_attr($value), esc_attr($class), $disabled_text);

                if(IS_ADMIN && !empty($value)){
                    $value = esc_attr($value);
                    $preview = sprintf("<div id='preview_%d'><a href='%s' target='_blank' alt='%s' title='%s'>%s</a><a href='%s' target='_blank' alt='" . __("Download file", "gravityforms") . "' title='" . __("Download file", "gravityforms") . "'><img src='%s' style='margin-left:10px;'/></a><a href='javascript:void(0);' alt='" . __("Delete file", "gravityforms") . "' title='" . __("Delete file", "gravityforms") . "' onclick='DeleteFile(%d,%d);' ><img src='%s' style='margin-left:10px;'/></a></div>", $id, $value, $value, $value, GFCommon::truncate_url($value), $value, GFCommon::get_base_url() . "/images/download.png", $lead_id, $id, GFCommon::get_base_url() . "/images/delete.png");
                    return $preview . "<div id='upload_$id' style='display:none;'>$upload</div>";
                }
                else{
                    return "<div class='ginput_container'>$upload</div>";
                }


            case "captcha" :

                switch($field["captchaType"]){
                    case "simple_captcha" :
                        $size = empty($field["simpleCaptchaSize"]) ? "medium" : $field["simpleCaptchaSize"];
                        $captcha = self::get_captcha($field);

                        $tagindex = self::get_tabindex();

                        $dimensions = IS_ADMIN ? "" : "width='{$captcha["width"]}' height='{$captcha["height"]}'";
                        return "<div class='gfield_captcha_container'><img class='gfield_captcha' src='{$captcha["url"]}' alt='' {$dimensions} /><div class='gfield_captcha_input_container simple_captcha_{$size}'><input type='text' name='input_{$id}' id='input_{$field_id}' /><input type='hidden' name='input_captcha_prefix_{$id}' value='{$captcha["prefix"]}' /></div></div>";
                    break;

                    case "math" :
                        $size = empty($field["simpleCaptchaSize"]) ? "medium" : $field["simpleCaptchaSize"];
                        $captcha_1 = self::get_math_captcha($field, 1);
                        $captcha_2 = self::get_math_captcha($field, 2);
                        $captcha_3 = self::get_math_captcha($field, 3);

                        $tagindex = self::get_tabindex();

                        $dimensions = IS_ADMIN ? "" : "width='{$captcha_1["width"]}' height='{$captcha_1["height"]}'";
                        return "<div class='gfield_captcha_container'><img class='gfield_captcha' src='{$captcha_1["url"]}' alt='' {$dimensions} /><img class='gfield_captcha' src='{$captcha_2["url"]}' alt='' {$dimensions} /><img class='gfield_captcha' src='{$captcha_3["url"]}' alt='' {$dimensions} /><div class='gfield_captcha_input_container math_{$size}'><input type='text' name='input_{$id}' id='input_{$field_id}' /><input type='hidden' name='input_captcha_prefix_{$id}' value='{$captcha_1["prefix"]},{$captcha_2["prefix"]},{$captcha_3["prefix"]}' /></div></div>";
                    break;

                    default:

                        if(!function_exists("recaptcha_get_html")){
                            require_once(GFCommon::get_base_path() . '/recaptchalib.php');
                        }

                        $theme = empty($field["captchaTheme"]) ? "red" : esc_attr($field["captchaTheme"]);
                        $publickey = get_option("rg_gforms_captcha_public_key");
                        $privatekey = get_option("rg_gforms_captcha_private_key");
                        if(IS_ADMIN){
                            if(empty($publickey) || empty($privatekey)){
                                return "<div class='captcha_message'>" . __("To use the reCaptcha field you must first do the following:", "gravityforms") . "</div><div class='captcha_message'>1 - <a href='https://admin.recaptcha.net/recaptcha/createsite/?app=php' target='_blank'>" . __(sprintf("Sign up%s for a free reCAPTCHA account", "</a>"), "gravityforms") . "</div><div class='captcha_message'>2 - " . __(sprintf("Enter your reCAPTCHA keys in the %ssettings page%s", "<a href='?page=gf_settings'>", "</a>"), "gravityforms") . "</div>";
                            }
                            else{
                                return "<div class='ginput_container'><img class='gfield_captcha' src='" . GFCommon::get_base_url() . "/images/captcha_$theme.jpg' alt='reCAPTCHA' title='reCAPTCHA'/></div>";
                            }
                        }
                        else{
                            $language = empty($field["captchaLanguage"]) ? "en" : esc_attr($field["captchaLanguage"]);

                            $options = "<script type='text/javascript'>var RecaptchaOptions = {theme : '$theme', lang : '$language'}; if(parseInt('" . self::$tab_index . "') > 0) {RecaptchaOptions.tabindex = " . self::$tab_index++ . ";}</script>";

                            $is_ssl = !empty($_SERVER['HTTPS']);
                            return $options . "<div class='ginput_container' id='$field_id'>" . recaptcha_get_html($publickey, null, $is_ssl) . "</div>";
                        }
                }
            break;

        }
    }

    public static function get_simple_captcha(){
        $captcha = new ReallySimpleCaptcha();
        $captcha->tmp_dir = RGFormsModel::get_upload_path("captcha") . "/";
        return $captcha;
    }

    public static function get_captcha($field){
        if(!class_exists("ReallySimpleCaptcha"))
            return array();

        $captcha = self::get_simple_captcha();

        //If captcha folder does not exist and can't be created, return an empty captcha
        if(!wp_mkdir_p($captcha->tmp_dir))
            return array();

        $captcha->char_length = 5;
        switch($field["simpleCaptchaSize"]){
            case "small" :
                $captcha->img_size = array( 100, 28 );
                $captcha->font_size = 18;
                $captcha->base = array( 8, 20 );
                $captcha->font_char_width = 17;

            break;

            case "large" :
                $captcha->img_size = array( 200, 56 );
                $captcha->font_size = 32;
                $captcha->base = array( 18, 42 );
                $captcha->font_char_width = 35;
            break;

            default :
                $captcha->img_size = array( 150, 42 );
                $captcha->font_size = 26;
                $captcha->base = array( 15, 32 );
                $captcha->font_char_width = 25;
            break;
        }

        if(!empty($field["simpleCaptchaFontColor"])){
            $captcha->fg = self::hex2rgb($field["simpleCaptchaFontColor"]);
        }
        if(!empty($field["simpleCaptchaBackgroundColor"])){
            $captcha->bg = self::hex2rgb($field["simpleCaptchaBackgroundColor"]);
        }

        $word = $captcha->generate_random_word();
        $prefix = mt_rand();
        $filename = $captcha->generate_image($prefix, $word);
        $url = RGFormsModel::get_upload_url("captcha") . "/" . $filename;
        $path = $captcha->tmp_dir . $filename;

        return array("path"=>$path, "url"=> $url, "height" => $captcha->img_size[1], "width" => $captcha->img_size[0], "prefix" => $prefix);
    }

    public static function get_math_captcha($field, $pos){
        if(!class_exists("ReallySimpleCaptcha"))
            return array();

        $captcha = self::get_simple_captcha();

        //If captcha folder does not exist and can't be created, return an empty captcha
        if(!wp_mkdir_p($captcha->tmp_dir))
            return array();

        $captcha->char_length = 1;
        if($pos == 1 || $pos == 3)
            $captcha->chars = '0123456789';
        else
            $captcha->chars = '+';

        switch($field["simpleCaptchaSize"]){
            case "small" :
                $captcha->img_size = array( 23, 28 );
                $captcha->font_size = 18;
                $captcha->base = array( 6, 20 );
                $captcha->font_char_width = 17;

            break;

            case "large" :
                $captcha->img_size = array( 36, 56 );
                $captcha->font_size = 32;
                $captcha->base = array( 10, 42 );
                $captcha->font_char_width = 35;
            break;

            default :
                $captcha->img_size = array( 30, 42 );
                $captcha->font_size = 26;
                $captcha->base = array( 9, 32 );
                $captcha->font_char_width = 25;
            break;
        }

        if(!empty($field["simpleCaptchaFontColor"])){
            $captcha->fg = self::hex2rgb($field["simpleCaptchaFontColor"]);
        }
        if(!empty($field["simpleCaptchaBackgroundColor"])){
            $captcha->bg = self::hex2rgb($field["simpleCaptchaBackgroundColor"]);
        }

        $word = $captcha->generate_random_word();
        $prefix = mt_rand();
        $filename = $captcha->generate_image($prefix, $word);
        $url = RGFormsModel::get_upload_url("captcha") . "/" . $filename;
        $path = $captcha->tmp_dir . $filename;

        return array("path"=>$path, "url"=> $url, "height" => $captcha->img_size[1], "width" => $captcha->img_size[0], "prefix" => $prefix);
    }

    private static function hex2rgb($color)
    {
        if ($color[0] == '#')
            $color = substr($color, 1);

        if (strlen($color) == 6)
            list($r, $g, $b) = array($color[0].$color[1],
                                     $color[2].$color[3],
                                     $color[4].$color[5]);
        elseif (strlen($color) == 3)
            list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
        else
            return false;

        $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

        return array($r, $g, $b);
    }

    public static function get_address_types($form_id){

        $addressTypes = array(
            "international" =>  array("label" => __("International", "gravityforms"),"zip_label" => __("Zip / Postal Code", "gravityforms"),"state_label" => __("State / Province / Region", "gravityforms")),
            "us" =>             array("label" => __("United States", "gravityforms"),"zip_label" => __("Zip Code", "gravityforms"),         "state_label" => __("State", "gravityforms"),   "country" => "United States", "states" => array_merge(array(''), GFCommon::get_us_states())),
            "canadian" =>       array("label" => __("Canadian", "gravityforms"),     "zip_label" => __("Postal Code", "gravityforms"),      "state_label" => __("Province", "gravityforms"),"country" => "Canada",        "states" => array_merge(array(''), GFCommon::get_canadian_provinces()))
            );

        return apply_filters("gform_address_types_{$form_id}", apply_filters("gform_address_types", $addressTypes, $form_id), $form_id);
    }

    private static function get_state_field($field, $id, $field_id, $state_value, $disabled_text, $form_id){

        if(empty($state_value)){
            $state_value = $field["defaultState"];

            //for backwards compatibility (canadian address type used to store the default state into the defaultProvince property)
            if ($field["addressType"] == "canadian" && !empty($field["defaultProvince"]))
                $state_value = $field["defaultProvince"];
        }

        $address_type = !isset($field["addressType"]) ? "international" : $field["addressType"];
        $address_types = self::get_address_types($form_id);
        $has_state_drop_down = is_array($address_types[$address_type]["states"]);

        if(IS_ADMIN && RG_CURRENT_VIEW != "entry"){
            $state_dropdown_class = "class='state_dropdown'";
            $state_text_class = "class='state_text'";
            $state_style = !$has_state_drop_down ? "style='display:none;'" : "";
            $text_style = $has_state_drop_down  ? "style='display:none;'" : "";
        }
        else{
            //id only displayed on front end
            $state_field_id = "id='" . $field_id . ".4'";
        }

        $tabindex = self::get_tabindex();
        $states = empty($address_types[$address_type]["states"]) ? array() : $address_types[$address_type]["states"];
        $state_dropdown = sprintf("<select name='input_%d.4' %s $tabindex %s $state_dropdown_class $state_style>%s</select>", $id, $state_field_id, $disabled_text, GFCommon::get_state_dropdown($states, $state_value));

        $tabindex = self::get_tabindex();
        $state_text = sprintf("<input type='text' name='input_%d.4' %s value='%s' $tabindex %s $state_text_class $text_style/>", $id, $state_field_id, $state_value, $disabled_text);

        if(IS_ADMIN && RG_CURRENT_VIEW != "entry")
            return $state_dropdown . $state_text;
        else if($has_state_drop_down)
            return $state_dropdown;
        else
            return $state_text;
    }

    public static function get_lead_field_display($field, $value){

        switch(RGFormsModel::get_input_type($field)){
            case "name" :
                if(is_array($value)){
                    $prefix = trim($value[$field["id"] . ".2"]);
                    $first = trim($value[$field["id"] . ".3"]);
                    $last = trim($value[$field["id"] . ".6"]);
                    $suffix = trim($value[$field["id"] . ".8"]);

                    $name = $prefix;
                    $name .= !empty($name) && !empty($first) ? " $first" : $first;
                    $name .= !empty($name) && !empty($last) ? " $last" : $last;
                    $name .= !empty($name) && !empty($suffix) ? " $suffix" : $suffix;

                    return $name;
                }
                else{
                    return $value;
                }

            break;

            case "address" :
                if(is_array($value)){
                    $street_value = trim($value[$field["id"] . ".1"]);
                    $street2_value = trim($value[$field["id"] . ".2"]);
                    $city_value = trim($value[$field["id"] . ".3"]);
                    $state_value = trim($value[$field["id"] . ".4"]);
                    $zip_value = trim($value[$field["id"] . ".5"]);
                    $country_value = trim($value[$field["id"] . ".6"]);

                    $address = $street_value;
                    $address .= !empty($address) && !empty($street2_value) ? "<br />$street2_value" : $street2_value;
                    $address .= !empty($address) && (!empty($city_value) || !empty($state_value)) ? "<br />$city_value" : $city_value;
                    $address .= !empty($address) && !empty($city_value) && !empty($state_value) ? ", $state_value" : $state_value;
                    $address .= !empty($address) && !empty($zip_value) ? " $zip_value" : $zip_value;
                    $address .= !empty($address) && !empty($country_value) ? "<br />$country_value" : $country_value;

                    //adding map link
                    if(!empty($address)){
                        $address_qs = str_replace("<br />", " ", $address); //replacing <br/> with spaces
                        $address_qs = urlencode($address_qs);
                        $address .= "<br/><a href='http://maps.google.com/maps?q=$address_qs' target='_blank' class='map-it-link'>Map It</a>";
                    }

                    return $address;
                }
                else{
                    return "";
                }
            break;

            case "email" :
                return GFCommon::is_valid_email($value) ? "<a href='mailto:$value'>$value</a>" : $value;
            break;

            case "website" :
                return GFCommon::is_valid_url($value) ? "<a href='$value' target='_blank'>$value</a>" : $value;
            break;

            case "checkbox" :
                if(is_array($value)){

                    foreach($value as $key => $item){
                        if(!empty($item)){
                            $items .= "<li>$item</li>";
                        }
                    }
                    return empty($items) ? "" : "<ul class='bulleted'>$items</ul>";
                }
                else{
                    return $value;
                }
            break;

            case "post_image" :
                list($url, $title, $caption, $description) = explode("|:|", $value);
                if(!empty($url)){
                    $url = str_replace(" ", "%20", $url);
                    $value = "<a href='$url' target='_blank' title='" . __("Click to view", "gravityforms") . "'><img src='$url' width='100' /></a>";
                    $value .= !empty($title) ? "<div>Title: $title</div>" : "";
                    $value .= !empty($caption) ? "<div>Caption: $caption</div>" : "";
                    $value .= !empty($description) ? "<div>Description: $description</div>": "";
                }
                return $value;
            case "fileupload" :
                $file_path = $value;
                if(!empty($file_path)){
                    $info = pathinfo($file_path);
                    $file_path = esc_attr(str_replace(" ", "%20", $file_path));
                    $value = "<a href='$file_path' target='_blank' title='" . __("Click to view", "gravityforms") . "'>" . $info["basename"] . "</a>";
                }
                return $value;
            break;

            case "date" :
                return GFCommon::date_display($value, $field["dateFormat"]);
            break;

            default :
                return nl2br($value);
            break;
        }
    }

}
?>
