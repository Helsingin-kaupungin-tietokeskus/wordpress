<?php

if(have_posts()) {
	
	$lang = HRI_LANG;
	
	switch_to_blog(1);

	the_post();

	// Redirect to CKAN's dataset page by digging its URL from the metadata and stripping it from redundant info.
	$ckan_url = get_post_meta($post->ID, 'ckan_url', true);
	$dataset_position = strpos($ckan_url , "/dataset");
	$target_url = '/' . $lang . substr($ckan_url, $dataset_position);

	header("Location: {$target_url}");
}

restore_current_blog();

global $wpdb;

if(isset($_POST['email2owner_message']) && ctype_digit($_POST['email2owner_pid']) && !empty($_POST['email2owner_from'])) {

	switch_to_blog(1);

	$pid = $_POST['email2owner_pid'];
	$from = strip_tags( $_POST['email2owner_name'] ) . " <" . strip_tags($_POST['email2owner_from']) . ">";
	$subject = strip_tags($_POST['email2owner_subject']);
	$message = strip_tags($_POST['email2owner_message']);
	$post = get_post( $pid, 'OBJECT' );

	$message .= "\r\n\r\n--------------------\r\nViesti on lähetetty hri.fi-palvelusta otsikolle ".$post->post_title."";

	$to = get_post_meta( $pid, 'author_email', true );

	$headers = 'From: '.$from . "\r\n" .
    'Reply-To: '.$from . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

	$message_sent = wp_mail( $to, $subject, $message, $headers );

	restore_current_blog();

} else {

	$message_sent = false;

}

get_header();

?>
<script type="text/javascript">
// <!--
jQuery('#main-nav .data-parent').addClass('current-page-ancestor');

jQuery(document).ready(function($) {

	jQuery('.comment_type_comment .comment-body').each(function () {
		if (jQuery(this).text() == '') jQuery(this).hide();
	});

	var url = "<?php bloginfo('template_url'); ?>/img/";

	var star_on = 's2.png';
	var star_off = 's1.png';

	function total() {
		var avg = ( parseInt(jQuery('#quality-score').val()) + parseInt(jQuery('#topicality-score').val()) + parseInt(jQuery('#usability-score').val()) ) / 3;
		jQuery('#overall_rating .rate').css({ width:avg * 20 });
	}

	jQuery('#quality').raty({
		starOn:		star_on,
		starOff:	star_off,
		path:		url,
		scoreName:	'r1',
		click:		total
	});
	jQuery('#topicality').raty({
		starOn:		star_on,
		starOff:	star_off,
		path:		url,
		scoreName:	'r2',
		click:		total
	});
	jQuery('#usability').raty({
		starOn:		star_on,
		starOff:	star_off,
		path:		url,
		scoreName:	'r3',
		click:		total
	});

	jQuery('#ratercontainer').show().insertAfter( jQuery('.comment-form-comment') );

	function remove_stars( el_id, val ) {
		jQuery(el_id+'-score').val(0);

		for( var i = val; i>0; --i ) {
			var f = function(n){ return function() { jQuery(el_id+'-'+n).attr( 'src', url+star_off ); }; }(i);
			setTimeout(f, val*50 - i*50);
		}
	}

	jQuery('#rater-toggle-bar').click(function() {

		if( jQuery('#rater').is(':visible') ) {

			var fields = new Array("#quality","#topicality","#usability");
			var time = new Array();
			var i = 0;

			time[0] = 0;
			time[1] = 0;
			time[2] = 0;

			for( var f in fields ) {
				time[i] = parseInt( jQuery(fields[i]+'-score').val(), 10 );
				++i;
			}

			setTimeout( function() { remove_stars( fields[0], time[0] ); }, 0 );
			setTimeout( function() { remove_stars( fields[1], time[1] ); }, time[0] * 30 );
			setTimeout( function() { remove_stars( fields[2], time[2] ); }, (time[0]+time[1]) * 30 );

			var overall_time = (time[0]+time[1]+time[2])*120;

			if (overall_time == 0) {
				jQuery('#rater').slideUp('slow');
			} else {
				jQuery('#overall_rating .rate').animate( {width:0}, overall_time, function() {
					jQuery('#rater').slideUp('slow', function() {
//						jQuery('#rater').prependTo( jQuery('#ratercontainer'));
					});
				});
			}

			jQuery(this).find('h4').text('<?php _e( 'Lisää myös arvostelu', 'hri' ); ?>');
			jQuery('#ratertogglelink').text('<?php _e( 'Avaa', 'hri' ); ?>');

		} else {

			jQuery(this).find('h4').text('<?php _e( 'Arvostele', 'hri' ); ?>');
			jQuery('#ratertogglelink').text('<?php _e( 'Sulje', 'hri' ); ?>');
			jQuery('#rater').slideDown('slow');

		}

		return false;
		
	});

	jQuery('#contact-button').click(function(){
		if( $('#email2owner_row').hasClass('open') ) {
			$('#email2owner_row').hide().removeClass('open');
		} else {
			$('#email2owner_row').show().addClass('open');
		}
	});

	jQuery('#email2owner_submit-link').click(function(){
		jQuery('#email2owner_form').submit();
	});
	
	jQuery('#commentform-rate').submit(function() {

		var error = false;
		
		if(jQuery('#comment').val() == "" && !jQuery('#rater').is(':visible')) {
			jQuery("label[for='comment']").html('<span class="formerror"><?php _e('Täytä kenttä','hri'); ?></span><?php _e('Kommentti','hri'); ?>');
			error = true;
		} else {
			jQuery("label[for='comment']").html('<?php _e('Kommentti','hri'); ?>');
		}
		

		<?php if ( !is_user_logged_in() ) { ?>
		if(jQuery('#author').val() == "") {
			jQuery("label[for='author']").html('<span class="formerror"><?php _e('Täytä kenttä','hri'); ?></span><?php _e('Nimi','hri'); ?>');
			error = true;
		} else {
			jQuery("label[for='author']").html('<?php _e('Nimi','hri'); ?>');
		}
		if(jQuery('#email').val() == "") {
			jQuery("label[for='email']").html('<span class="formerror"><?php _e('Täytä kenttä','hri'); ?></span><?php _e('Sähköposti','hri'); ?>');
			error = true;
		} else {
			var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
			var address = jQuery('#email').val();
			if(reg.test(address) == false) {
				jQuery("label[for='email']").html('<span class="formerror"><?php _e('Anna kunnollinen sähköpostiosoite','hri'); ?></span><?php _e('Sähköposti','hri'); ?>');
				error = true;
			} else {
				jQuery("label[for='email']").html('<?php _e('Sähköpostiosoite','hri'); ?>');
			}
			
		}
		<?php } ?>

		if (error) {
			return false;
		} 
	});

});
// -->
</script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
google.load("visualization", "1", {packages:["corechart", "imagesparkline"]});
</script>
<?php

if ( have_posts() ) {

	switch_to_blog(1);

	the_post();

	get_sidebar( 'data' );

	global $post;

	?><div class="column col-wide"><article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<?php hri_rating(); ?>
		<h1><?php data_title(); ?></h1>

<?php if( ORIGINAL_BLOG_ID == 2 ) { ?>

		<div class="row clearfix">
			<a class="with-medium-circle left" href="<?php echo NEW_DISCUSSION_URL, '?linked_id=', $post->ID; ?>"><div class="circle medium green icon-discussion"></div><?php _e( 'Aloita uusi keskustelu tästä datasta', 'hri' ); ?></a>
			<a class="with-medium-circle left" href="<?php echo NEW_APP_URL, '?linked_id=', $post->ID; ?>"><div class="circle medium green icon-phone"></div><?php _e( 'Ilmoita uusi sovellus tästä datasta', 'hri' ); ?></a>
		</div>

<?php } ?>

		<div class="row content"><?php

			$notes = notes( false, false );
			echo nl2br(links_in_text( $notes ));

		?></div>

		<?php
		
		if( get_post_meta( $post->ID, "resources_0_id", true ) ) {
		
		?><h2 class="green row"><?php _e( 'Ladattavat tiedostot ja linkit', 'hri' ); ?></h2><table class="data-table download-table"><?php
					
			$i = 0;

			while ( get_post_meta( $post->ID, "resources_{$i}_id", true ) ) {

				$data_link = get_post_meta( $post->ID, "resources_{$i}_url", true );
				$data_format = get_post_meta( $post->ID, "resources_{$i}_format", true );
				$data_description = get_post_meta( $post->ID, "resources_{$i}_description", true );
				$data_size = get_post_meta( $post->ID, "resources_{$i}_size", true );

//				$data_gacount = get_post_meta( $post->ID, "resources_{$i}_gacount", true );
//				$ga_str = '';
//				if ($data_gacount
//					&& $data_gacount != '0') {
//					$ga_str = '<span class="data_gacount">'.$data_gacount.'</span>';
//				}
                $table_name = "wp_hri_analytics_downloads_by_day";
                
                $downloads_arr = array();
                $has_data = false;
                $max_count = 0;
                for ($j=30;$j>0;$j--) {
                    $dateSQL = date('Y-m-d 00:00:00', strtotime("-".$j." days"));
                    $datePretty = date('d.m.Y', strtotime("-".$j." days"));
                    
                    $results = $wpdb->get_row('
                    	SELECT * FROM ' . $table_name . '
        				WHERE event_date = "' . $dateSQL . '"
        				AND event_label =  "' . $data_link . '"
        				AND event_action = "' . $data_format . '"
                    ');
                    if (   $results
                        && isset($results->event_count)) {
                        $downloads_arr[] = array('count' =>$results->event_count, 'date'=>$datePretty);
                        if ($results->event_count > 0) {
                            $has_data = true;
                        }
                        
                        if ($results->event_count > $max_count) {
                            $max_count = (int) $results->event_count;
                        }
                        
                    } else {
                        $downloads_arr[] = array('count' => 0 , 'date'=>$datePretty);
                    }
                    
                }
                ?>
				<tr>
					<td>
						<a onclick="_gaq.push(['_trackEvent', 'Ladattavat tiedostot ja linkit', '<?php echo $data_format; ?>', '<?php echo $data_link; ?>']);" href="<?php echo $data_link; ?>">
							<span class="download file-icon icon-<?php echo $data_format; ?>"><?php

								echo '<strong>', $data_format, '</strong>', $data_description;
								if( $data_size ) echo ' ', hri_filesize( $data_size );

							?></span>
							<?php
							// don't show at this point
							if ($has_data && 1==2) {
            				?>
            				<script>
            				    google.setOnLoadCallback(drawChart2_<?php echo $i; ?>);
                                function drawChart2_<?php echo $i; ?>() {
                                    var data = google.visualization.arrayToDataTable([
                                        <?php
                                        $is_first = true;
                                        foreach ($downloads_arr as $downloads_by_day) {
                                            if ($is_first) {
                                                echo "['".$downloads_by_day['date']."', ".$downloads_by_day['count']."]";
                                                $is_first = false;
                                            } else {
                                                echo ",\n". "['".$downloads_by_day['date']."', ".$downloads_by_day['count']."]";
                                            }

                                        }
                                        ?>

                                    ]);
                                    
                                    var options = {
                                        title: '',
                                        height: '60',
                                        width: '280',
                                        colors: ['#084169'],
                                        vAxis :{
                                            gridlines : {
                                                <?php
                                                if ($max_count == 1) {
                                                ?>
                                                count:2
                                                <?php
                                                } elseif($max_count == 2) {
                                                ?>
                                                count:3
                                                <?php
                                                } elseif($max_count == 3) {
                                                ?>
                                                count:4
                                                <?php
                                                } else {
                                                ?>
                                                count:4
                                                <?php
                                                }
                                                ?>
                                            }
                                        },
                                        hAxis : {
                                            textPosition : 'none'
                                        },
                                        legend: {
                                            position: 'none'
                                        }
                                    };

                                    var chart = new google.visualization.LineChart(document.getElementById('chart_div_<?php echo $i; ?>'));
//                                    chart.draw(data, {width: 80, height: 30, showAxisLines: false,  showValueLabels: false, labelPosition: 'left',color:'#666666'});
                                    chart.draw(data, options);
                                  }
                            </script>
                            <div class="google_sparklines_tooltip_opener">
                                <div class="google_sparklines_tooltip" style="display: none;">
                                    <div style="padding: 15px 20px;">
                                        <span style="font-size: 0.9em; color: #000000;"><?php _e('Tiedoston lataukset viimeisen 30 päivän aikana.', 'hri'); ?></span>
                                        <div style="margin-left: -10px;" class="google_sparklines" id="chart_div_<?php echo $i ?>"></div>
                                    </div>
                                </div>
                                <img src="/wp-content/themes/hri2/img/chart_icon.png">
                            </div>
                            <?php
                            }
                            ?>
							<div class="link-area"><div class="download icon-download"><?php _e( 'Lataa', 'hri' ); ?></div></div>
						</a>
					</td>
					<style>
					.google_sparklines_tooltip_opener {
					    position: relative;
					    float: right;
					    margin-right: 110px;
					    width: 40px;
					}
					
					.google_sparklines_tooltip {
					    position: absolute;
					    top: -120px;
					    left: -135px;
					    height: 120px;
					    width: 300px;
					    background: url('/wp-content/themes/hri2/img/chart_tooltip_bg.png') top left no-repeat;
					}
					</style>
					<script>
					jQuery('.google_sparklines_tooltip_opener').hover(function() {
					    jQuery('.google_sparklines_tooltip', this).show();
					},  function() {
					    jQuery('.google_sparklines_tooltip', this).hide();
					}); 
				    </script>
				</tr><?php

				$i++;

			}

			?></table><?php

		}

			$created = get_post_meta( $post->ID, 'metadata_created', true );
			$modified = get_post_meta( $post->ID, 'metadata_modified', true );
			$url = get_post_meta( $post->ID, 'url', true );
			$license = get_post_meta( $post->ID, 'license', true );
			$license_url = get_post_meta( $post->ID, 'license_url', true );
			$author = get_post_meta( $post->ID, 'author', true );

			if( $modified || $url || $license || $author ) {

				?><h2 class="green row"><?php _e( 'Lisätiedot', 'hri' ); ?></h2>
		<table class="data-table blue-table"><?php

				if ( $author ) {

					?><tr><th scope="row"><?php _e('Ylläpitäjä','hri'); ?></th><td><a title="<?php printf( __( 'Kaikki datat ylläpitäjältä %s', 'hri' ), $author ); ?>" class="term" href="<?php echo DATA_SEARCH_URL; ?>#producer=<?php echo str_replace( ' ', '%20', $author ); ?>" style="position:relative;top:1px"><?php echo $author; ?></a><div class="rel"><a id="contact-button" title="<?php _e( 'Ota yhteyttä ylläpitäjään', 'hri' ); ?>"></a></div></td></tr><?php

					?>
					<tr id="email2owner_row" style="display:none"><td colspan="2"><div id="email2owner">
<form id="email2owner_form" action="" method="post">
	<label for="email2owner_name"><?php _e('Nimi', 'hri'); ?></label>
	<input class="text" type="text" name="email2owner_name" id="email2owner_name" required="required" /><br />
	<label for="email2owner_from"><?php _e('Sähköpostiosoitteesi', 'hri'); ?></label>
	<input class="text" type="text" name="email2owner_from" id="email2owner_from" required="required" /><br />
	<label for="email2owner_subject"><?php _e('Aihe', 'hri'); ?></label>
	<input class="text" type="text" name="email2owner_subject" id="email2owner_subject" required="required" /><br />
	<label for="email2owner_message"><?php _e('Viesti', 'hri'); ?></label>
	<textarea name="email2owner_message" id="email2owner_message" cols="45" rows="8" required="required"></textarea>
	<input type="hidden" name="email2owner_pid" value="<?php echo $post->ID; ?>" />

	<a class="bold" id="email2owner_submit-link">
		<img style="margin-right:10px" src="<?php bloginfo('template_url'); ?>/img/send_btn.png" alt=""/><?php _e( 'Lähetä', 'hri' ); ?>
	</a>
</form>
</div></td></tr><?php

					if( $message_sent ) {

						?><tr>
							<td colspan="2"><?php _e( 'Viesti lähetty ylläpitäjälle.', 'hri' ); ?></td>
						</tr><?php

					} elseif( isset( $_POST['email2owner_message'] ) ) {

						?><tr>
							<td colspan="2"><?php _e( 'Viestin lähetys ei onnistunut.', 'hri' ); ?></td>
						</tr><?php

					}

				}

				if ( $created ) echo '<tr><th scope="row">' . __('Luotu','hri') . '</th><td>' . date( 'j.n.Y', strtotime( $created ) ) . '</td></tr>';
				if ( $modified ) echo '<tr><th scope="row">' . __('Muokattu','hri') . '</th><td>' . date( 'j.n.Y', strtotime( $modified ) ) . '</td></tr>';
				if ( $license ) {
					?><tr><th scope="row"><?php _e('Lisenssi','hri'); ?></th><td><?php

					if( isset( $license_url ) && $license_url != '' ) {

						?><a href="<?php echo $license_url ?>" target="_blank"><?php echo $license; ?></a><?php

					} else echo $license;

					?></td></tr><?php

				}

				if ( $url ) echo '<tr><th scope="row">' . __('Ylläpitäjän www-sivu','hri') . '</th><td>' . hri_make_short_link($url) . '</td></tr>';

				function ckan_get_extra_field($query_field, $post_id) {

					global $wpdb;

					return $wpdb->get_results("SELECT m.meta_value FROM {$wpdb->prefix}postmeta m, {$wpdb->prefix}posts p WHERE meta_key LIKE '{$query_field}' AND m.post_id = p.ID AND p.ID = {$post_id}");
				}

				function ckan_extra_fields( $query_field, $title , $type = 'string') {

					global $post, $wpdb;
					$results = ckan_get_extra_field($query_field, $post->ID);

					if ($results) {

						if( $results[0]->meta_value == "" ) return;

						echo '<tr><th scope="row">' . $title . '</th><td>';
						$g = null;

						$previous_values = array();

						foreach ($results as $r) {
							if( !in_array( $r->meta_value, $previous_values ) ) {
							if ($type == 'date' && strlen($r->meta_value) == 10) {

								list( $y,$m,$d ) = explode( '-', $r->meta_value );

								$g[] = "$d.$m.$y";

							} else {

								$g[] = $r->meta_value;

							}
								$previous_values[] = $r->meta_value;
							}
						}

						echo implode(', ', $g) . '</td></tr>';
					}
				}

				ckan_extra_fields('extras\_agency%', __('Virasto','hri'));
				ckan_extra_fields('extras\_department%', __('	Viraston osasto','hri'));
				ckan_extra_fields('extras\_geographic\_coverage%', __('Maantieteellinen alue','hri'));
				ckan_extra_fields('extras\_geographic\_granularity%', __('Maantieteellinen tarkkuus','hri'));
				ckan_extra_fields('extras\_source%', __('Lähde','hri'));
				ckan_extra_fields('extras\_update\_frequency%', __('Päivitysväli','hri'));
				ckan_extra_fields('extras\_temporal\_granularity%', __('Aikajakson tarkkuus','hri'));
				ckan_extra_fields('extras\_temporal\_coverage', __('Aikasarja alkaa','hri'));
				ckan_extra_fields('extras\_temporal\_coverage-from', __('Aikasarjan alku','hri'), 'date');
				ckan_extra_fields('extras\_temporal\_coverage-to', __('Aikasarjan loppu','hri'), 'date');
				ckan_extra_fields('extras\_temporal\_granularity-other', __('Muuta','hri'));

				if (ORIGINAL_BLOG_ID == 3) $externalref = get_post_meta( $post->ID, 'extras_external_reference_en', true );
				elseif (ORIGINAL_BLOG_ID == 4) $externalref = get_post_meta( $post->ID, 'extras_external_reference_se', true );
				else $externalref = get_post_meta( $post->ID, 'extras_external_reference', true );

				if ( $externalref ) echo '<tr><th>' . __('Ulkoinen linkki','hri') . '</th><td>' . hri_make_short_link($externalref) . '</td></tr>';

				// Lähdeviitegeneraattori, HRI-85
				$wpdb->get_results("SELECT m.meta_value FROM {$wpdb->prefix}postmeta m, {$wpdb->prefix}posts p WHERE meta_key LIKE '$query_field' AND m.post_id = p.ID AND p.ID = " . $post->ID);

				$link = hri_link(get_permalink(), HRI_LANG, 'data');
				$name_and_link = "<a href='{$link}'>{$post->post_title}</a>";
				$author = get_post_meta($post->ID, 'author', true);
				$source = ckan_get_extra_field('extras\_source%', $post->ID);
				if($source[0]->meta_value != "") { $original_creator =  sprintf(__(' ja alkuperäinen tekijä %s'), $source[0]->meta_value); }
				else { $original_creator = ''; }
				$hrilink = '<a href="http://www.hri.fi">Helsinki Region Infoshare</a>';
				$timestamp = date('j.n.Y');
				if($license_url) { $license_link = "<a href='{$license_url}'>{$license}</a>"; }
				else { $license_link = $license; }
				
				$reference = 
					sprintf(__('Lähde: %s. Aineiston ylläpitäjä on %s%s. Aineisto on ladattu %s -palvelusta %s lisenssillä %s.'),
						$name_and_link,
						$author,
						$original_creator,
						$hrilink,
						$timestamp,
						$license_link
					);
				echo '<tr><td scope="row" colspan="2"><b>' . __('Lähdeviite aineistoon') . ':</b><br>' . $reference . '</td></tr>';

		?></table><?php

		}
//		}

	?></article><?php

	comments_template( '/comments-ratings.php', true );

	?></div><?php // .column

}

get_footer();

?>