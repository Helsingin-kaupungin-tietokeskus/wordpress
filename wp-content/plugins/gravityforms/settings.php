<?php
class GFSettings{

    public static $addon_pages = array();

    public static function add_settings_page($name, $handler, $icon_path){
        add_action("gform_settings_" . str_replace(" " , "_", $name), $handler);
        self::$addon_pages[$name] = array("name" => $name, "icon" => $icon_path);
    }

    public static function settings_page(){
        $addon_name = RGForms::get("addon");
        $icon_path = empty($addon_name) ? "" : self::$addon_pages[$addon_name]["icon"];
        $page_title = empty($addon_name) ? __("Gravity Forms Settings", "gravityforms") : $addon_name . " " . __("Settings", "gravityforms");
        $icon_path = empty($icon_path) ? GFCommon::get_base_url() . "/images/title32.png" : $icon_path;
        echo GFCommon::get_remote_message();
        ?>
        <link rel="stylesheet" href="<?php echo GFCommon::get_base_url()?>/css/admin.css" />
        <div class="wrap">
            <img alt="<?php $page_title ?>" src="<?php echo $icon_path?>" style="float:left; margin:15px 7px 0 0;"/>
            <h2><?php echo $page_title ?></h2>

        <?php
        if(!empty(self::$addon_pages)){
            ?>
            <ul class="subsubsub">
                <li><a href="?page=gf_settings">Gravity Forms</a> |</li>
            <?php


            $count = sizeof(self::$addon_pages);
            for($i = 0; $i<$count; $i++){
                $addon_keys = array_keys(self::$addon_pages);
                $addon = $addon_keys[$i];
                ?>
                <li><a href="?page=gf_settings&addon=<?php echo urlencode($addon) ?>"><?php echo esc_html($addon) ?></a> <?php echo $i < $count-1 ? "|" : ""?></li>
                <?php
            }
            ?>
            </ul>
            <br style="clear:both;"/>
            <?php
        }

        if(empty($addon_name)){
            self::gravityforms_settings_page();
        }
        else{
            do_action("gform_settings_" . str_replace(" ", "_", $addon_name));
        }
    }

    public static function gravityforms_settings_page(){
        global $wpdb;

        if(!GFCommon::ensure_wp_version())
            return;

        if(isset($_POST["submit"])){
            check_admin_referer('gforms_update_settings', 'gforms_update_settings');

            if(!GFCommon::current_user_can_any("gravityforms_edit_settings"))
                die(__("You don't have adequate permission to edit settings.", "gravityforms"));

            RGFormsModel::save_key($_POST["gforms_key"]);
            update_option("rg_gforms_disable_css", $_POST["gforms_disable_css"]);
            update_option("rg_gforms_enable_html5", $_POST["gforms_enable_html5"]);
            update_option("rg_gforms_captcha_public_key", $_POST["gforms_captcha_public_key"]);
            update_option("rg_gforms_captcha_private_key", $_POST["gforms_captcha_private_key"]);

            //Updating message because key could have been changed
            GFCommon::cache_remote_message();

            //Re-caching version info
            $version_info = GFCommon::get_version_info(false);
            ?>
            <div class="updated fade" style="padding:6px;">
                <?php _e("Settings Updated", "gravityforms"); ?>.
             </div>
             <?php
        }
        else if(isset($_POST["uninstall"])){

            if(!GFCommon::current_user_can_any("gravityforms_uninstall") || (function_exists("is_multisite") && is_multisite() && !is_super_admin()))
                die(__("You don't have adequate permission to uninstall Gravity Forms.", "gravityforms"));

            //droping all tables
            RGFormsModel::drop_tables();

            //removing options
            delete_option("rg_form_version");
            delete_option("rg_gforms_key");
            delete_option("rg_gforms_disable_css");
            delete_option("rg_gforms_enable_html5");
            delete_option("rg_gforms_captcha_public_key");
            delete_option("rg_gforms_captcha_private_key");
            delete_option("rg_gforms_message");
            delete_option("gf_dismissed_upgrades");

            //removing gravity forms upload folder
            GFCommon::delete_directory(RGFormsModel::get_upload_root());

            //Deactivating plugin
            $plugin = "gravityforms/gravityforms.php";
            deactivate_plugins($plugin);
            update_option('recently_activated', array($plugin => time()) + (array)get_option('recently_activated'));

            ?>
            <div class="updated fade" style="padding:20px;"><?php _e(sprintf("Gravity Forms have been successfully uninstalled. It can be re-activated from the %splugins page%s.", "<a href='plugins.php'>","</a>"), "gravityforms")?></div>
            <?php
            return;
        }

        if(!isset($version_info))
            $version_info = GFCommon::get_version_info();
        ?>
        <form method="post">
            <?php wp_nonce_field('gforms_update_settings', 'gforms_update_settings') ?>
            <table class="form-table">
              <?php //brbr 18.11.10
			  /*
			  <tr valign="top">
                   <th scope="row"><label for="gforms_key"><?php _e("Support License Key", "gravityforms"); ?></label>  <?php gform_tooltip("settings_license_key") ?></th>
                    <td>
                        <?php
                        $key = GFCommon::get_key();
                        $key_field = '<input type="password" name="gforms_key" id="gforms_key" style="width:350px;" value="' . $key . '" />';
                        if($version_info["is_valid_key"])
                            $key_field .= "&nbsp;<img src='" . GFCommon::get_base_url() ."/images/tick.png'/>";
                        else if (!empty($key))
                            $key_field .= "&nbsp;<img src='" . GFCommon::get_base_url() ."/images/stop.png'/>";

                        echo apply_filters('gform_settings_key_field', $key_field);
                        ?>
                        <br />
                        <?php _e("The license key is used for access to automatic upgrades and support.", "gravityforms"); ?>
                    </td>
                </tr>*/ ?>
               <tr valign="top">
                     <th scope="row"><label for="gforms_disable_css"><?php _e("Output CSS", "gravityforms"); ?></label>  <?php gform_tooltip("settings_output_css") ?></th>
                    <td>
                        <input type="radio" name="gforms_disable_css" value="0" id="gforms_css_output_enabled" <?php echo get_option('rg_gforms_disable_css') == 1 ? "" : "checked='checked'" ?> /> <?php _e("Yes", "gravityforms"); ?>&nbsp;&nbsp;
                        <input type="radio" name="gforms_disable_css" value="1" id="gforms_css_output_disabled" <?php echo get_option('rg_gforms_disable_css') == 1 ? "checked='checked'" : "" ?> /> <?php _e("No", "gravityforms"); ?><br />
                        <?php _e("Set this to No if you would like to disable the plugin from outputting the form CSS.", "gravityforms"); ?>
                    </td>
                </tr>
                 <tr valign="top">
                     <th scope="row"><label for="gforms_enable_html5"><?php _e("Output HTML5", "gravityforms"); ?></label>  <?php gform_tooltip("settings_html5") ?></th>
                    <td>
                        <input type="radio" name="gforms_enable_html5" value="1" <?php echo get_option('rg_gforms_enable_html5') == 1 ? "checked='checked'" : "" ?> id="gforms_enable_html5"/> <?php _e("Yes", "gravityforms"); ?>&nbsp;&nbsp;
                        <input type="radio" name="gforms_enable_html5" value="0" <?php echo get_option('rg_gforms_enable_html5') == 1 ? "" : "checked='checked'" ?> /><?php _e("No", "gravityforms"); ?><br />
                        <?php _e("Set this to No if you would like to disable the plugin from outputting HTML5 form fields.", "gravityforms"); ?>
                    </td>
                </tr>
            </table>

            <div class="hr-divider"></div>

              <h3><?php _e("reCAPTCHA Settings", "gravityforms"); ?></h3>

              <p style="text-align: left;"><?php _e("Gravity Forms integrates with reCAPTCHA, a free CAPTCHA service that helps to digitize books while protecting your forms from spam bots. ", "gravityforms"); ?><a href="http://recaptcha.net/" target="_blank"><?php _e("Read more about reCAPTCHA", "gravityforms"); ?></a>.</p>

              <table class="form-table">


                <tr valign="top">
                   <th scope="row"><label for="gforms_captcha_public_key"><?php _e("reCAPTCHA Public Key", "gravityforms"); ?></label>  <?php gform_tooltip("settings_recaptcha_public") ?></th>
                    <td>
                        <input type="text" id="gforms_captcha_public_key" name="gforms_captcha_public_key" style="width:350px;" value="<?php echo get_option("rg_gforms_captcha_public_key") ?>" /><br />
                        <?php _e("Required only if you decide to use the reCAPTCHA field.", "gravityforms"); ?> <?php _e(sprintf("%sSign up%s for a free account to get the key.", '<a target="_blank" href="https://admin.recaptcha.net/recaptcha/createsite/?app=php">', '</a>'), "gravityforms"); ?>
                    </td>
                </tr>
                <tr valign="top">
                   <th scope="row"><label for="gforms_captcha_private_key"><?php _e("reCAPTCHA Private Key", "gravityforms"); ?></label>  <?php gform_tooltip("settings_recaptcha_private") ?></th>
                    <td>
                        <input type="text" id="gforms_captcha_private_key" name="gforms_captcha_private_key" style="width:350px;" value="<?php echo esc_attr(get_option("rg_gforms_captcha_private_key")) ?>" /><br />
                        <?php _e("Required only if you decide to use the reCAPTCHA field.", "gravityforms"); ?> <?php _e(sprintf("%sSign up%s for a free account to get the key.", '<a target="_blank" href="https://admin.recaptcha.net/recaptcha/createsite/?app=php">', '</a>'), "gravityforms"); ?>
                    </td>
                </tr>

              </table>

              <div class="hr-divider"></div>

              <h3><?php _e("Installation Status", "gravityforms"); ?></h3>
              <table class="form-table">

                <tr valign="top">
                   <th scope="row"><?php _e("PHP Version", "gravityforms"); ?></th>
                    <td class="installation_item_cell">
                        <strong><?php echo phpversion(); ?></strong>
                    </td>
                    <td>
                        <?php
                            if(version_compare(phpversion(), '5.0.0', '>')){
                                ?>
                                <img src="<?php echo GFCommon::get_base_url() ?>/images/tick.png"/>
                                <?php
                            }
                            else{
                                ?>
                                <img src="<?php echo GFCommon::get_base_url() ?>/images/stop.png"/>
                                <span class="installation_item_message"><?php _e("Gravity Forms requires PHP 5 or above.", "gravityforms"); ?></span>
                                <?php
                            }
                        ?>
                    </td>
                </tr>
                <tr valign="top">
                   <th scope="row"><?php _e("MySQL Version", "gravityforms"); ?></th>
                    <td class="installation_item_cell">
                        <strong><?php echo $wpdb->db_version();?></strong>
                    </td>
                    <td>
                        <?php
                            if(version_compare($wpdb->db_version(), '5.0.0', '>')){
                                ?>
                                <img src="<?php echo GFCommon::get_base_url() ?>/images/tick.png"/>
                                <?php
                            }
                            else{
                                ?>
                                <img src="<?php echo GFCommon::get_base_url() ?>/images/stop.png"/>
                                <span class="installation_item_message"><?php _e("Gravity Forms requires MySQL 5 or above.", "gravityforms"); ?></span>
                                <?php
                            }
                        ?>
                    </td>
                </tr>
                <tr valign="top">
                   <th scope="row"><?php _e("WordPress Version", "gravityforms"); ?></th>
                    <td class="installation_item_cell">
                        <strong><?php echo get_bloginfo("version"); ?></strong>
                    </td>
                    <td>
                        <?php
                            if(version_compare(get_bloginfo("version"), '2.8.0', '>')){
                                ?>
                                <img src="<?php echo GFCommon::get_base_url() ?>/images/tick.png"/>
                                <?php
                            }
                            else{
                                ?>
                                <img src="<?php echo GFCommon::get_base_url() ?>/images/stop.png"/>
                                <span class="installation_item_message"><?php _e("Gravity Forms requires WordPress 2.8 or above.", "gravityforms"); ?></span>
                                <?php
                            }
                        ?>
                    </td>
                </tr>
                 <tr valign="top">
                   <th scope="row"><?php _e("Gravity Forms Version", "gravityforms"); ?></th>
                    <td class="installation_item_cell">
                        <strong><?php echo GFCommon::$version ?></strong>
                    </td>
                    <td>

                        <?php

                            if(version_compare(GFCommon::$version, $version_info["version"], '>=')){
                                ?>
                                <img src="<?php echo GFCommon::get_base_url() ?>/images/tick.png"/>
                                <?php
                            }
                            else{
                                _e(sprintf("New version %s available. Automatic upgrade available on the %splugins page%s", $version_info["version"], '<a href="plugins.php">', '</a>'), "gravityforms");
                            }
                        ?>
                    </td>
                </tr>
            </table>

            <?php if(GFCommon::current_user_can_any("gravityforms_edit_settings")){ ?>
                <br/><br/>
                <p class="submit" style="text-align: left;">
                <?php
                $save_button = '<input type="submit" name="submit" value="' . __("Save Settings", "gravityforms"). '" class="button-primary"/>';
                echo apply_filters("gform_settings_save_button", $save_button);
                ?>
                </p>
           <?php } ?>
        </form>

        <form action="" method="post">
            <?php if(GFCommon::current_user_can_any("gravityforms_uninstall") && (!function_exists("is_multisite") || !is_multisite() || is_super_admin())){ ?>
                <div class="hr-divider"></div>

                <h3><?php _e("Uninstall Gravity Forms", "gravityforms") ?></h3>
                <div class="delete-alert"><?php _e("Warning! This operation deletes ALL Gravity Forms data.", "gravityforms") ?>
                    <?php
                    $uninstall_button = '<input type="submit" name="uninstall" value="' . __("Uninstall Gravity Forms", "gravityforms") . '" class="button" onclick="return confirm(\'' . __("Warning! ALL Gravity Forms data will be deleted, including entries. This cannot be undone. \'OK\' to delete, \'Cancel\' to stop", "gravityforms") . '\');"/>';
                    echo apply_filters("gform_uninstall_button", $uninstall_button);
                    ?>

                </div>
            <?php } ?>
        </form>

        <?php

    }


}
?>