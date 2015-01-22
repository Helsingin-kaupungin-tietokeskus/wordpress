<?php

function hri_search() {

	/** @var wpdb $wpdb */
	global $wpdb;

	/**
	 * Parse search string to array
	 */

	$illegal = array("\0", "\n", "\r", "\t", "\x1a", "\x0B", '\\');
	$s = str_replace( $illegal, '', $_POST['search_string']);

	$s = html_entity_decode( $s, ENT_QUOTES );
	$s = mysql_real_escape_string( stripslashes( $s ) );

	list( $search_type, $lang, $search_string ) = explode( '|', $s );
	if( !in_array( $lang, array( 'fi', 'en', 'se' ) ) ) $lang = 'fi';

	switch_to_blog(1);
	
	$search_array = array();
	$search_params = explode('&', $search_string);

	// Replace spaces with comma and replace double commas with sinlge comma until they are not found anymore
	$search_params[0] = str_replace( ' ', ',', $search_params[0] );
	while ( strpos( $search_params[0], ',,' ) !== false ) $search_params[0] = str_replace( ',,', ',', $search_params[0] );

	$convert_to_int = array( 'category', 'post_tag', 'page', 'sort' );

	foreach( $search_params as $search_param ) {
		
		if ( substr( $search_param, -1) == ',' ) $search_param = substr( $search_param, 0, strlen( $search_param ) - 1 );
		
		list($key,$values_string) = explode('=', $search_param);
		
		if ($values_string) {
		
			$values = explode(',',$values_string);
			$search_array[$key] = array();
			foreach ( $values as $value ) {

				if ( in_array( $key, $convert_to_int ) ) $value = (int) $value;
				$search_array[$key][] = $value;

			}
		}
	}

	// All search params has been parsed to an array.

	require_once(ABSPATH . '/wp-content/plugins/hri-ckan/hri_ckan_query_class.php');

	if( isset($search_array['searchpage'][0]) ) {
		$page = (int) $search_array['searchpage'][0];
		if ( $page < 1 ) $page = 1;
	} else $page = 1;

	switch ( $search_type ) {

		case 'search':

			$response = null;
			$filters_used = (
				isset( $search_array['area'] ) ||
				isset( $search_array['category'] ) ||
				isset( $search_array['filetype'] ) ||
				isset( $search_array['post_tag'] ) ||
				isset( $search_array['producer'] ) ||
				isset( $search_array['search_text'] )
			) ? true : false;

			foreach( array( 'area','category', 'post_tag', 'filetype','producer' ) as $option_type ) {

				if ( $filters_used ) {
					$data_query = new hri_ckan_query( $wpdb->prefix, false );
					$data_query->set_where( 'p.post_type', 'data' );

					if ( isset( $search_array['post_tag'] ) && $option_type != 'post_tag' )
						$data_query->search_taxonomy( array( 'post_tag' => array( 'id' => $search_array['post_tag'] )));

					if ( isset( $search_array['category'] ) && $option_type != 'category' )
						$data_query->search_taxonomy( array( 'category' => array( 'id' => $search_array['category'] )));

					if ( isset( $search_array['search_text'] ) )
						$data_query->search_text( $search_array['search_text'] );

					if ( isset( $search_array['filetype'] ) && $option_type != 'filetype' )
						$data_query->search_meta( 'resources\__\_format', $search_array['filetype'], true );

					if ( isset( $search_array['producer'] ) && $option_type != 'producer' )
						$data_query->search_meta( 'author', $search_array['producer'] );

					if ( isset( $search_array['area'] ) && $option_type != 'area' )
						$data_query->search_meta( 'extras\_geographic\_coverage', $search_array['area'], true );

					$data_query->no_limit();
					$data_query->build_query();

					$query = $data_query->get_query();

					$ids = $wpdb->get_col( $query );
					$ids = 'AND p.ID IN (' . ( !empty($ids) ? implode( ',', $ids ) : '0' ) . ')';

				} else $ids = null;

				$query = null;
				$compare = array();

				switch ( $option_type ) {

					case 'area':
					case 'producer':
					case 'filetype':

						switch ( $option_type ) {
							case 'area': $where = "WHERE meta_key LIKE 'extras\\_geographic\\_coverage%'"; break;
							case 'producer': $where = "WHERE meta_key = 'author'"; break;
							case 'filetype': $where = "WHERE meta_key LIKE 'resources\\__\\_format'"; break;
						}

						$query = "SELECT m.meta_value, COUNT(DISTINCT p.ID) AS c FROM {$wpdb->postmeta} m
LEFT JOIN {$wpdb->posts} p ON p.ID = m.post_id {$ids} AND p.post_status = 'publish' AND p.post_type = 'data'
$where
GROUP BY m.meta_value
ORDER BY m.meta_value ASC";

						$field1 = 'meta_value';
						$field2 = false;

					break;

					case 'category':
					case 'post_tag':

						$query = "SELECT t.term_id, t.name, COUNT(p.ID) AS c
FROM {$wpdb->term_relationships} tr
JOIN {$wpdb->term_taxonomy} tx ON tr.term_taxonomy_id = tx.term_taxonomy_id
JOIN {$wpdb->terms} t ON t.term_id = tx.term_id
LEFT JOIN {$wpdb->posts} p ON p.ID = tr.object_id {$ids} AND p.post_status = 'publish' AND p.post_type = 'data'
WHERE tx.taxonomy = '$option_type'
GROUP BY t.term_id
ORDER BY t.name ASC";

						$compare = $wpdb->get_col( "SELECT t.term_id
FROM {$wpdb->term_relationships} tr
JOIN {$wpdb->term_taxonomy} tx ON tr.term_taxonomy_id = tx.term_taxonomy_id
JOIN {$wpdb->terms} t ON t.term_id = tx.term_id
JOIN {$wpdb->posts} p ON p.ID = tr.object_id AND p.post_status = 'publish' AND p.post_type = 'data'
WHERE tx.taxonomy = '$option_type'
GROUP BY t.term_id
ORDER BY t.name ASC" );

						$field1 = 'term_id';
						$field2 = 'name';

					break;

				}

				if( isset( $query ) && isset( $field1 ) && isset( $field2 ) ) {

					$res = $wpdb->get_results( $query );
					if($res) foreach ($res as $r) {

						if( empty($compare) || ( !empty($compare) && in_array( $r->$field1, $compare ) ) ) {

							if ( isset($search_array[ $option_type ]) && in_array( $r->$field1, $search_array[ $option_type ] )) $active = 1;
							else $active = 0;

							if( isset( $r->$field1 ) && trim($r->$field1) != '' ) {

								$option = array( (int) $r->c, $active, $r->$field1 );
								if ( $field2 ) $option[] = $r->$field2;

								$response[ $option_type ][] = $option;

							}
						}
					}
				}
			}

			echo json_encode( $response );

		break;

		case 'data':

			$data_query = new hri_ckan_query( $wpdb->prefix );
			$data_query->set_where( 'p.post_type', 'data' );
			
			// Added by O.Kokko 18.1.2012 to remove empty titled results from list
			/*if ( $lang == 'en' ) {
				$data_query->search_meta( 'extras_title_en', '', false, 'NOT NULL' );

			} elseif ( $lang == 'se' ) {
				$data_query->search_meta( 'extras_title_se', '', false, 'NOT NULL' );

			} else {*/
				$data_query->set_where_raw("p.post_title <> ''");
			/*}*/

			if ( isset( $search_array['post_tag'] ) )		$data_query->search_taxonomy( array( 'post_tag' => array( 'id' => $search_array['post_tag'] )));
			if ( isset( $search_array['category'] ) )		$data_query->search_taxonomy( array( 'category' => array( 'id' => $search_array['category'] )));
			if ( isset( $search_array['search_text'] ) )	$data_query->search_text( $search_array['search_text'] );
			if ( isset( $search_array['filetype'] ) )		$data_query->search_meta( 'resources\__\_format', $search_array['filetype'], true );
			if ( isset( $search_array['producer'] ) )		$data_query->search_meta( 'author', $search_array['producer'] );
			if ( isset( $search_array['area'] ) )			$data_query->search_meta( 'extras\_geographic\_coverage', $search_array['area'], true );

			$data_query->set_limit( ($page-1) * $data_query->resultsPerPage, $data_query->resultsPerPage );

			if ( isset( $search_array['sort'] ) && !empty($search_array['sort']) ) $data_query->set_sorts( $search_array['sort'] );

			$data_query->build_query();
			$ids = $data_query->execute();
			
			if ( isset( $ids) && $ids != false  ) {

				$GLOBALS['posts_IDs'] = $ids;

				function hri_order_by_field() {
					return " FIELD( ID, " . implode( ',', $GLOBALS['posts_IDs'] ) . ")";
				}
				add_filter( 'posts_orderby', 'hri_order_by_field' );

				query_posts( array( 'post_type' => 'data', 'post__in' => $ids, 'posts_per_page' => -1 ) );

				remove_filter( 'posts_orderby', 'hri_order_by_field' );

				if (have_posts()) {
				
					echo '<div id="rescount" class="clearfix rescount"><div class="left rescount-results"><span>', $data_query->get_count(), '</span> ', __('results','hri-ckan'), '</div><div class="search-wish-data"><a href="', NEW_DATA_REQUEST_URL, '" class="icon-wish"></a>', __( 'Etkö löydä haluamaasi dataa HRIstä?', 'hri' ), '<br><a href="', NEW_DATA_REQUEST_URL, '">', __( 'Tee datatoive ja kerro meille siitä', 'hri' ), '</a></div></div>';

					$nsearch = 0;
					
					while (have_posts()) {
					
						the_post();

						global $post;

						$slug = $post->post_name;
						$post_id = $post->ID;
						
						if(get_post_meta( $post_id, 'state', true ) == "deleted") continue;
					
						?>
<div id="post-<?php the_ID(); ?>" class="searchpost <?php echo 'nsearch_', ++$nsearch, ' ', ( $nsearch % 2 == 1 ? 'odd' : 'even' ); ?>">
	<div class="spc">
		<?php hri_rating(); ?>
		<h3 class="clearfix"><a href="<?php echo hri_link( get_permalink( $post_id ), $lang, 'dataset'); ?>"><?php data_title( $lang ); ?></a></h3>
		<?php

			$notes = notes( false, false, $lang );

			if ( $notes ) {

				?><p><?php

				if ( strlen( $notes ) > 150 ) {

					$cut_from = strpos( $notes, ' ', 150 );
					if( !$cut_from ) $cut_from = 150;

					echo nl2br( substr( $notes, 0, $cut_from)) . ' &hellip;';

				}
				else echo nl2br( $notes );

				?></p><?php

			}

		?><strong><?php _e('Modified','hri-ckan'); ?>:</strong> <?php

		$meta = get_post_meta( get_the_ID(), 'metadata_modified', true );

		if( $meta ) {
			list( $y, $m, $d ) = explode( '-', substr( $meta, 0, 10 ) );
			echo "$d.$m.$y";
		}

//		echo date_i18n( get_option('date_format'), strtotime(get_post_meta( get_the_ID(), 'metadata_modified', true )) );

		?> &nbsp; <?php

		$producer = get_post_meta( $post_id, 'author', true );

		if ( $producer ) {
			?><strong><?php _e('Produced by','hri-ckan'); ?>:</strong> <?php echo $producer;
		}

		?><strong><br /><?php

		$i = 0;

		while ( get_post_meta( $post_id, "resources_{$i}_id", true ) ) {

			$format = get_post_meta( $post_id, "resources_{$i}_format", true );

			?><a href="<?php echo get_post_meta( $post_id, "resources_{$i}_url", true ); ?>" class="download file-icon icon-<?php echo $format; ?>"><?php

				echo $format;

			?></a><?php ++$i; } ?></strong>
	</div>

	<div class="spb box_comment">
		<div class="cc_n"><?php echo $post->comment_count; ?></div>
	</div><?php //<!-- .spb (.box_comment) -->?>

	<div class="spb box_discussion">
		<div class="cc_n"><?php

		$discussion = new WP_Query('post_type=discussion&post_status=publish&posts_per_page=-1&meta_key=_link_to_data&meta_value=' . $post_id);
		echo count( $discussion->posts );

		?></div>
	</div><?php //<!-- .spb .box_discussion -->?>

	<div class="spb box_app">
		<?php

		$args = array(
			'post_type' => 'application',
			'post_status' => 'publish',
			'posts_per_page' => 1,
			'meta_query' => array(
				array(
					'key' => '_link_to_data',
					'value' => $post_id,
					'compare' => '='
				)
			),
			'orderby' => 'date',
			'order' => 'DESC'
		);

		$app = new WP_Query( $args );

		if ( $app->have_posts() ) {

			$app->the_post();

			?><a class="searchboxlink" href="<?php echo hri_link( get_permalink( $post->ID ), $lang, 'application'); ?>"><?php the_title(); ?></a><?php

		}

		?>
	</div><?php // <!-- .spb .box_app --> ?>
</div><?php //<!-- #post_ID .searchpost -->
					
					}

					echo '<div class="clear"></div>' . hri_pager( $page, htmlspecialchars_decode($_COOKIE['searchString']), $data_query->get_count() );

				}
				
			} else {
				echo '<p class="no_results">', __('Data matching your search was not found', 'hri-ckan'), '.</p>';
			}

		break;

		case 'datalite':

			$data_query = new hri_ckan_query( $wpdb->prefix );
			$data_query->set_where( 'p.post_type', 'data' );
			if ( isset( $search_array['search_text'] ) ) $data_query->search_text( $search_array['search_text'] );
			if ( isset( $search_array['sort'] ) && !empty($search_array['sort']) ) $data_query->set_sorts($search_array['sort']);

			$data_query->set_limit(0,3);
			$data_query->build_query();
			$ids = $data_query->execute();

			if( $ids ) {

				$GLOBALS['posts_IDs'] = $ids;

				add_filter( 'posts_orderby', function() {
					return " FIELD( ID, " . implode( ',', $GLOBALS['posts_IDs'] ) . ")";
				});

				query_posts( array( 'post_type' => 'data', 'post__in' => $ids, 'posts_per_page' => -1 ) );

				if (have_posts()) {

					$nsearch = 0;

					while ( have_posts() ) {
						the_post();

						global $post;

						?>
<div id="post-<?php the_ID(); ?>" class="searchpost <?php echo 'nsearch_', ++$nsearch, ' ', ( $nsearch % 2 == 1 ? 'odd' : 'even' ); ?>"><?php hri_rating(); ?>
<h3><a href="<?php echo hri_link( get_permalink( $post->ID ), $lang, 'dataset' ); ?>"><?php data_title( $lang ); ?></a></h3><?php

						$notes = notes( false, false, $lang );

						if ( $notes ) {

							?><p><?php

							if ( strlen( $notes ) > 150 ) echo nl2br( substr( $notes, 0, strpos( $notes, ' ', 150 ))) . ' &hellip;';
							else echo nl2br( $notes );

							?></p><?php

						}

						?><strong><?php _e('Modified','hri-ckan'); ?>:</strong> <?php

						$meta = get_post_meta( get_the_ID(), 'metadata_modified', true );

						list( $y, $m, $d ) = explode( '-', substr( $meta, 0, 10 ) );

						echo "$d.$m.$y";

						?><br /><?php

						$producer = get_post_meta( $post->ID, 'author', true );

						if ( $producer ) {
							?><strong><?php _e('Produced by','hri-ckan'); ?>:</strong> <?php echo $producer;
						}

						?><br /><strong><?php

						$i = 0;

						while ( get_post_meta( $post->ID, "resources_{$i}_id", true ) ) {

							$format = get_post_meta( $post->ID, "resources_{$i}_format", true );

							?><a href="<?php echo get_post_meta( $post->ID, "resources_{$i}_url", true ); ?>" class="download file-icon icon-<?php echo $format; ?>"><?php

								echo $format;

							?></a><?php ++$i;

						} ?></strong><?php

/*
							$i = 0;
							while ( get_post_meta( $post->ID, "resources_{$i}_id", true ) ) { ?>
<a href="<?php echo get_post_meta( $post->ID, "resources_{$i}_url", true ); ?>" class="blocklink download"><?php
									echo (string) get_post_meta( $post->ID, "resources_{$i}_format", true );
?></a>
							<?php ++$i; } */

						?></div><?php
						
					}

					?>

					<a href="<?php

					echo home_url();
					if ( $lang == 'fi' ) echo '/fi/data-haku/#search_text=';
					if ( $lang == 'en' ) echo '/fi/data-search/#search_text=';

					if ( is_array( $search_array['search_text'] ) ) echo esc_html( implode(' ', $search_array['search_text']));

						?>" id="alldata" style="width:280px" class="blocklink clear floatr"><?php _e('More search results and advanced search','hri-ckan'); ?></a>
					<?php
				} else echo '<p class="no_results">' . __('Data matching your search was not found', 'hri-ckan') . '.</p>';
			} else echo '<p class="no_results">' . __('Data matching your search was not found', 'hri-ckan') . '.</p>';

		break;

		case 'fullsearch':

			$sort3 = ( isset( $search_array['sort'] ) && ($search_array['sort'][0] == 3 || $search_array['sort'][0] == -3) ) ? $search_array['sort'][0] : false;

			if ( $sort3 ) $query1 = new hri_ckan_query( $wpdb->prefix, true, 'p.*, c.*', 1 );
			else $query1 = new hri_ckan_query( $wpdb->prefix, true, 'p.*', 1 );

			$query1->set_where( 'p.post_type', array('application','discussion') );
			if ( $sort3 ) {
				$query1->left_join( $wpdb->prefix . 'comments c', 'comment_post_ID = ID AND comment_approved = 1' );
				$query1->groupby('ID');
			}
			if ( isset( $search_array['search_text'] ) )	$query1->search_text( $search_array['search_text'] );

			if ($lang == 'en') $n = 3;
			if ($lang == 'se') $n = 4;
			if ( !isset($n) ) $n = 2;

			if ( $sort3 ) $query2 = new hri_ckan_query( $wpdb->prefix . $n . '_', false, 'p.*, c.*', $n );
			else $query2 = new hri_ckan_query( $wpdb->prefix . $n . '_', false, 'p.*', $n );

			$query2->set_where( 'p.post_type', array('page','post') );
			if ( $sort3 ) {
				$query2->left_join( $wpdb->prefix . $n . '_comments c', 'comment_post_ID = ID AND comment_approved = 1' );
				$query2->groupby('ID');
			}
			if ( isset( $search_array['search_text'] ) )	$query2->search_text( $search_array['search_text'] );

			$query = "({$query1->get_query()})\nUNION\n({$query2->get_query()})";

			if ( isset( $search_array['sort'] ) && $search_array['sort'][0] == 1 ) $query .= "\nORDER BY post_date DESC, post_title ASC";
			if ( isset( $search_array['sort'] ) && $search_array['sort'][0] == -1 ) $query .= "\nORDER BY post_date ASC, post_title ASC";
			if ( isset( $search_array['sort'] ) && $search_array['sort'][0] == 2 ) $query .= "\nORDER BY post_title ASC";
			if ( isset( $search_array['sort'] ) && $search_array['sort'][0] == -2 ) $query .= "\nORDER BY post_title DESC";
			if ( $sort3 == 3 ) $query .= "\nORDER BY comment_date DESC, post_title ASC";
			if ( $sort3 == -3 ) $query .= "\nORDER BY comment_date ASC, post_title ASC";

			$query .= "\nLIMIT " . (($page-1) * 10) . ",10";

//			echo '<pre>';
//			var_dump($query);
//			echo '</pre>';

			$res = $wpdb->get_results( $query );

			if ( $res ) {

				$nsearch = 0;

				$res2 = $wpdb->get_var( "SELECT FOUND_ROWS();" );

				switch_to_blog( 2 );

				$term_vb = get_term_by( 'slug', 'visualisointiblogi', 'category' );
				if( $term_vb ) $term_vb_link = get_term_link( $term_vb, 'category' );
				if( !isset( $term_vb_link ) || is_wp_error( $term_vb_link ) ) $term_vb_link = '';

				restore_current_blog();

				foreach( $res as $r ) {

					?><div id="post-<?php the_ID(); ?>" class="searchpost <?php echo 'nsearch_', ++$nsearch, ' ', ( $nsearch % 2 == 1 ? 'odd' : 'even' ); ?>">

						<div class="search-other-result-left">
							<div class="entry-meta"><?php hri_time_since( $r->post_date ); ?></div>
							<div class="s-result-type"><?php

							switch_to_blog( $r->source );

							if ( $r->post_type == 'application' ) {
								if ($lang == 'fi') echo '<a class="term" href="' . home_url() . '/fi/sovellukset/">Sovellukset</a>';
								if ($lang == 'en') echo '<a class="term" href="' . home_url() . '/en/applications/">Applications</a>';
							}

							elseif ( $r->post_type == 'discussion' ) {
								if ($lang == 'fi') echo '<a class="term" href="' . home_url() . '/fi/keskustelut/">Keskustelut</a>';
								if ($lang == 'en') echo '<a class="term" href="' . home_url() . '/en/dicussions/">Dicussions</a>';
							}

							else {
								if ( $r->post_type == 'page' ) {

									$top_parent = end( get_ancestors( $r->ID, 'page' ));
									if ( $top_parent ) echo '<a class="term" href="' . get_permalink( $top_parent ) . '">' . get_the_title( $top_parent ) . '</a>';
									else echo '<a class="term" href="' . get_permalink( $r->ID ) . '">' . $r->post_title . '</a>';

								} else {
	// Commented out 28.10.2011 by O.Kokko. Sillander asked that all the categories would be shown
	/*								$last_cat_id = end(wp_get_post_categories( $r->ID ));
									$last_cat = get_category( $last_cat_id );

	//								if ($lang == 'fi') echo '<a href="' . home_url() . '/category/' . $last_cat->slug . '">' . $last_cat->name . '</a>';
	//								if ($lang == 'en') echo '<a href=""></a> ';

									echo '<a href="' . home_url() . '/category/' . $last_cat->slug . '">' . $last_cat->name . '</a>';
	*/
									$post_categories = wp_get_post_categories( $r->ID );
									if (isset($post_categories)
										&& is_array($post_categories)
										&& count($post_categories)>0) {
										echo '<div class="result-type-content clearfix">';
										foreach ($post_categories as $post_category_id) {
											$post_category = get_category( $post_category_id );
											echo '<a class="term" href="' . home_url() . '/category/' . $post_category->slug . '">' . $post_category->name . '</a>';
										}
										echo '</div>';
									}
								}
							}

						?></div>

					<h3><a href="<?php echo hri_link( get_permalink( $r->ID ), $lang, $r->post_type); ?>"><?php echo $r->post_title; ?></a></h3><?php

						if( $r->post_type == 'post' && ( in_category( $term_vb->term_id, $r ) || post_is_in_descendant_category( $term_vb->term_id, $r ) ) ) {

							 ?><a class="vb-link" href="<?php echo $term_vb_link ?>"><?php echo $term_vb->name; ?></a><?php

						}

					?>
					<p><?php
						
					$content = strip_tags( $r->post_content );

					if ( strlen( $content ) > 200 ) echo mb_substr( $content, 0, strpos( $content, ' ', 200 )) . '&hellip;';
					else echo $content;

					?></p><?php

					if ( $r->post_type == 'post' ) {

						$author_name = get_the_author_meta('user_nicename', $r->post_author);

						if ( $author_name && $author_name != 'admin' ) : ?>
					<div class="entry-author-info">

						<?php echo get_avatar( get_the_author_meta( 'user_email', $r->post_author ), 50 ); ?>
						<div class="author-description">
							<h4><?php echo get_the_author_meta( 'display_name', $r->post_author); ?></h4>

<?php if ( get_the_author_meta( 'position', $r->post_author ) ) : ?><span class="author-detail"><?php echo get_the_author_meta( 'position', $r->post_author ); ?></span><br /><?php endif; ?>
<?php if ( get_the_author_meta( 'organization', $r->post_author ) ) :
	if ( get_the_author_meta( 'organization_home_page', $r->post_author ) ) echo '<span class="author-detail"><a target="_blank" href="' . get_the_author_meta( 'organization_home_page', $r->post_author ) . '">' . get_the_author_meta( 'organization', $r->post_author ) . '</a></span>';
	else echo '<span class="author-detail">' . get_the_author_meta( 'organization', $r->post_author ) . '</span><br />';
endif; ?>

						</div><!-- #author-description -->
					</div><!-- #entry-author-info -->
						<?php endif;

					}

					$tags = get_the_tags( $r->ID );
					if ( $tags ) {
						?>

						<span class="tag-links">
							<h6><?php _e('Tags', 'twentyten'); ?></h6>

						<?php
						foreach($tags as $tag) {

							//todo: link applications's tags to app gallery

							$link = false;
							if (ORIGINAL_BLOG_ID == 2) $link = home_url() . '/fi/data-haku/#post_tag=' . $tag->term_id;
							if (ORIGINAL_BLOG_ID == 3) $link = home_url() . '/en/data-search/#post_tag=' . $tag->term_id;

							if( $link ) echo '<a class="term" href="', $link, '" rel="tag">', $tag->name, '</a>';
						}
						?>
						</span>
					<?php
						
					}

					?>
					</div><!-- .search-other-result-left -->
					<div class="search-other-result-right">
					<?php if ( $r->comment_count > 0 ) { /* ?>
						<span class="comments-link">
							<span class="entry-utility-prep-comments-links"><?php _e('Comments','hri-ckan'); ?></span>
							<a href="<?php echo get_permalink( $r->ID ); ?>#comments"><?php

						if( $r->comment_count == 1 ) echo '1 ' . __('comment','hri-ckan');
						else echo $r->comment_count . ' ' . __('comments', 'hri-ckan');

						?></a>
						</span>
						<?php */

						$latest = $wpdb->get_results("SELECT * FROM {$wpdb->comments} WHERE comment_post_ID = {$r->ID} AND comment_approved = 1 ORDER BY comment_ID DESC LIMIT 0,1");
						if ( $latest ) {

							hri_comment_excerpt( $latest[0] );

						}

					} ?>
					</div><!-- .search-other-result-right -->
					<br class="clear" />

					</div><?php

				}

				if ( $res2 > 10 ) {

					$sort = (isset($search_array['sort'][0])) ? (int) $search_array['sort'][0] : 1;

					$words = ( isset($search_array['search_text']) ) ? urlencode( implode(' ', $search_array['search_text']) ) : '';

					echo hri_pager( $page, 'words=' . $words . '&sort=' . $sort , $res2 );

				}

			} else {

				echo '<p class="no_results">', __('No search results','hri-ckan'), '.</p>';

			}

		break;
		
		case 'application':
			
			$app_query = new hri_ckan_query( $wpdb->prefix );
			$app_query->set_where( 'p.post_type', 'application' );
			if ( isset( $search_array['search_text'] ) ) $app_query->search_text( $search_array['search_text'] );
			if ( isset( $search_array['author'] ) && !empty($search_array['author']) ) $app_query->search_meta( 'app_author', urldecode( $search_array['author'][0] ) );
			if ( isset( $search_array['app_tag'] ) && !empty($search_array['app_tag']) ) $app_query->search_taxonomy( array( 'post_tag' => array( 'id' => $search_array['app_tag'] )));
			if ( isset( $search_array['category'] ) && !empty($search_array['category']) ) $app_query->search_taxonomy( array( 'hri_appcats' => array( 'id' => $search_array['category'] )));
			if ( isset( $search_array['sort'] ) && !empty($search_array['sort']) ) $app_query->set_sorts($search_array['sort']);

			$per_page = 6;
			if ( isset( $search_array['perpage'] ) && !empty( $search_array['perpage'] ) ) {
				$per_page = (int) $search_array['perpage'][0];
			}
			if( !$per_page ) $per_page = 6;

			$app_query->set_limit( ($page-1) * $per_page, $per_page );

			$app_query->build_query();
			$ids = $app_query->execute();

			if (
				isset( $search_array['search_text'] ) ||
				isset( $search_array['author'] ) ||
				isset( $search_array['app_tag'] ) ||
				isset( $search_array['category'] )
			) {

				?><div class="search-result-meta"><h1><?php

				if ( isset( $search_array['search_text'] ) ) {


					printf( __( 'Haun tulos haulle: "%s"', 'hri' ), esc_html( urldecode( stripslashes( implode( ' ', $search_array['search_text'] )))));


				}

				if ( isset( $search_array['author'] ) ) {

					printf( __( 'Sovellukset tekijältä: %s', 'hri' ), esc_html( urldecode( stripslashes( $search_array['author'][0] ))));


				}

				if ( isset( $search_array['app_tag'] ) ) {

					$tag_name = get_term_by( 'id', $search_array['app_tag'][0], 'post_tag' );

					printf( __( 'Sovellukset avainsanalla: %s', 'hri' ), "<div class='term'>$tag_name->name</div>" );
					$no_top_margin = true;

				}

				if ( isset( $search_array['category'] ) ) {

					$tag_name = get_term_by( 'id', $search_array['category'][0], 'hri_appcats' );

					printf( __( 'Sovellukset kategorialla: %s', 'hri' ), "<span class='term'>$tag_name->name</span>" );
					$no_top_margin = true;

				}

				?></h1>
				<a id="app-back" class="block" href="<?php echo APP_SEARCH_URL; ?>"<?php if( isset($no_top_margin) ) echo ' style="margin-top:-5px"'; ?>>
					<div class="icon-back-arrow"></div><?php _e( 'Takaisin sovellusgalleriaan', 'hri' ); ?></a>
			</div><div id="app-result-count"><?php printf( __( 'Löytyi %s kpl', 'hri' ), ($app_query->get_count() > 0 ? $app_query->get_count() : 0 ) ); ?></div><?php

			}

			if( $ids ) {

				$GLOBALS['posts_IDs'] = $ids;

				add_filter( 'posts_orderby', function() {
					return " FIELD( ID, " . implode( ',', $GLOBALS['posts_IDs'] ) . ")";
				});

				query_posts( array( 'post_type' => 'application', 'post__in' => $ids, 'posts_per_page' => -1 ) );

				if (have_posts()) {

					?><div class="application-container clear clearfix"><?php

					while ( have_posts() ) {

						the_post();

						global $post;

						?><a href="<?php echo hri_link( get_permalink(), HRI_LANG, 'application' ); ?>"><div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<?php the_post_thumbnail( 'app2' ); ?>
						<h2><?php the_title(); ?></h2>
						<div class="inner"><?php echo get_post_meta( $post->ID, 'short_text', true ); ?></div>
					</div></a><?php

					}

					?></div><?php

					echo hri_pager( $page, htmlspecialchars_decode($_COOKIE['searchString']), $app_query->get_count(), $per_page );

				}

			}

		break;

		default:

			echo 'Search type mismatch.';

		break;

	}

	exit;

}

?>