<?php
	$message = null;
		$message_updated = __("All in one SEO Pack migrated.", 'platinum_seo_pack');

		// update options
		if ($_POST['action'] && $_POST['action'] == 'aioseop-migrate') {
		$nonce = $_POST['aioseop-migrate-nonce'];
			if (!wp_verify_nonce($nonce, 'aioseop-migrate-nonce')) die ( 'Security Check - If you receive this in error, log out and back in to WordPress');

$aioseop_options = get_option('aioseop_options');

update_option('aiosp_home_title',$aioseop_options['aiosp_home_title']);
update_option('aiosp_home_keywords',$aioseop_options['aiosp_home_keywords']);
update_option('aiosp_home_description',$aioseop_options['aiosp_home_description']);

update_option('psp_canonical', $aioseop_options['aiosp_can']);
update_option('aiosp_rewrite_titles', $aioseop_options['aiosp_rewrite_titles']);
update_option('aiosp_post_title_format', $aioseop_options['aiosp_post_title_format']);
update_option('aiosp_page_title_format', $aioseop_options['aiosp_page_title_format']);
update_option('aiosp_category_title_format', $aioseop_options['aiosp_category_title_format']);
update_option('aiosp_archive_title_format', $aioseop_options['aiosp_archive_title_format']);
update_option('aiosp_tag_title_format', $aioseop_options['aiosp_tag_title_format']);
update_option('aiosp_search_title_format', $aioseop_options['aiosp_search_title_format']);
update_option('aiosp_description_format', $aioseop_options['aiosp_description_format']);
update_option('aiosp_404_title_format', $aioseop_options['aiosp_404_title_format']);
update_option('aiosp_paged_format', $aioseop_options['aiosp_paged_format']);

update_option('psp_category_noindex', $aioseop_options['aiosp_category_noindex']);
update_option('psp_archive_noindex', $aioseop_options['aiosp_archive_noindex']);
update_option('psp_tags_noindex', $aioseop_options['aiosp_tags_noindex']);

update_option('aiosp_generate_descriptions', $aioseop_options['aiosp_generate_descriptions']);
update_option('aiosp_post_meta_tags', $aioseop_options['aiosp_post_meta_tags']);
update_option('aiosp_page_meta_tags', $aioseop_options['aiosp_page_meta_tags']);
update_option('aiosp_home_meta_tags', $aioseop_options['aiosp_home_meta_tags']);
update_option('aiosp_do_log', $aioseop_options['aiosp_do_log']);

global $wpdb;
$wpdb->query("UPDATE $wpdb->postmeta SET meta_key = 'keywords' WHERE meta_key = '_aioseop_keywords'");
$wpdb->query("UPDATE $wpdb->postmeta SET meta_key = 'title' WHERE meta_key = '_aioseop_title'");	
$wpdb->query("UPDATE $wpdb->postmeta SET meta_key = 'description' WHERE meta_key = '_aioseop_description'");
$wpdb->query("UPDATE $wpdb->postmeta SET meta_key = 'psp_disable' WHERE meta_key in ('_aioseop_disable', 'aioseop_disable')" );
echo "<div class='updated fade' style='background-color:green;border-color:green;'><p><strong>Migrated All in One SEO to Platinum SEO.</strong></p></div";
}
?>
<div class="wrap"><h2><?php _e('Migrate from All in one SEO', 'platinum_seo_pack'); ?></h2>
<p>
<a target="_blank" title="<?php _e('FAQ', 'platinum_seo_pack') ?>" href="http://techblissonline.com/platinum-seo-pack-faq/"><?php _e('FAQ', 'platinum_seo_pack') ?></a>
| <a target="_blank" title="<?php _e('Platinum SEO Plugin Feedback', 'platinum_seo_pack') ?>" href="http://techblissonline.com/platinum-seo-pack/"><?php _e('Feedback', 'platinum_seo_pack') ?></a>
| <a target="_blank" title="<?php _e('Platinum SEO - What is new in version 1.3.4?', 'platinum_seo_pack') ?>" href="http://techblissonline.com/platinum-seo-pack/"><?php _e('What is new in version 1.3.4?', 'platinum_seo_pack') ?></a>
| <a target="_blank" title="<?php _e('Platinum SEO - Smart Options, Smart Benefits', 'platinum_seo_pack') ?>" href="http://techblissonline.com/wordpress-seo-plugin-smart-options-benefits/"><?php _e('Wordpress SEO options', 'platinum_seo_pack') ?></a>
| <a target="_blank" title="<?php _e('Donations for Platinum SEO Plugin', 'platinum_seo_pack') ?>" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=rrajeshbab%40gmail%2ecom&item_name=Platinum%20SEO%20plugin%20development%20and%20support%20expenses&item_number=1&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=IN&bn=PP%2dDonationsBF&charset=UTF%2d8"><?php _e('Please Donate', 'platinum_seo_pack') ?></a>
| <a target="_blank" title="<?php _e('Save Bandwidth with Chennai Central Plugin', 'platinum_seo_pack') ?>" href="http://techblissonline.com/save-bandwidth/"><?php _e('Save Bandwidth with Chennai Central Plugin', 'platinum_seo_pack') ?></a>
</p>
<p><strong><?php _e('Click the button to migrate All in one SEO options to Platinum SEO.(It is recommended to back up your database before updating.)', 'platinum_seo_pack') ?></em></strong></p></div>
<form name="aioseop-migrate" action="" method="post">
<p class="submit">
<input type="hidden" name="action" value="aioseop-migrate" />
<input type="hidden" name="aioseop-migrate-nonce" value="<?php echo wp_create_nonce('aioseop-migrate-nonce'); ?>" />
<input type="submit" name="Submit" value="<?php _e('Migrate from All in One SEO', 'platinum_seo_pack')?> &raquo;" />
</p>
</form>
<?php $pspurl = get_option( 'siteurl' ). "/wp-admin/admin.php?page=platinum-seo-pack/platinum_seo_pack.php"; ?>
<p><a title="<?php _e('Go back to Platinum SEO Options Page', 'platinum_seo_pack') ?>" href="<?php echo "$pspurl" ?>"><?php _e('Go back to Platinum SEO Options Page', 'platinum_seo_pack') ?>&raquo;</a></p>
</div>