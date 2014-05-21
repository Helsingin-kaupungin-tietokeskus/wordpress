<?php /*
<style>
.sparklines_hl {
    color: ;
}
</style>
*/ ?>

<div class="column col-narrow">

	<nav>
		<ul>
			<li>
				<div class="icon icon-back-arrow"></div><a href="<?php

				echo DATA_SEARCH_URL, '#', str_replace(
					array( '&', '\\', 'post_tags' ),
					array( '&amp;', '', 'post_tag' ),
					htmlspecialchars_decode($_COOKIE['searchString'])
				);

				?>"><?php

				if( isset( $_SERVER['HTTP_REFERER'] ) && strpos( $_SERVER['HTTP_REFERER'], DATA_SEARCH_URL ) !== false ) {
					_e( 'Takaisin hakutuloksiin', 'hri' );
				} else _e( 'Hae dataa', 'hri' );

			?></a></li>
		</ul>
	</nav>
	<?php

	global $post;
	
	
	$table_name = "wp_hri_analytics_pageviews_by_day";
	$table_name2 = "wp_hri_analytics_downloads_by_day";
    
    $downloads_arr = array();
    $has_data = false;
    $max_count = 0;
    $total_count = 0;
    $total_count2 = 0;
    
	$show_days = 30;

    for ($j=$show_days;$j>0;$j--) {
        $dateSQL = date('Y-m-d 00:00:00', strtotime("-".$j." days"));
        $datePretty = date('d.m.Y', strtotime("-".$j." days"));
        
        $tmp_count = 0;
        $tmp_count2 = 0;
        
        $results = $wpdb->get_results('
        	SELECT * FROM ' . $table_name . '
			WHERE event_date = "' . $dateSQL . '"
			AND data_post_id =  "' . $post->ID . '"
        ');
        $results2 = $wpdb->get_row('
        	SELECT SUM(event_count) as event_count_total FROM ' . $table_name2 . '
			WHERE event_date = "' . $dateSQL . '"
			AND data_post_id =  "' . $post->ID . '"
        ');
        $tmp = array('date'=>$datePretty);
        if (count($results) > 0) {
            foreach ($results as $result) {
                $tmp_count = $tmp_count + $result->page_pageviews;
            }

            $tmp['pageviews'] = $tmp_count;

            if ($tmp_count > 0) {
                $has_data = true;
            }
            if ($tmp_count > $max_count) {
                $max_count = $tmp_count;
            }
            
            $total_count += $tmp_count;
        } else {
            $tmp['pageviews'] = 0;
        }

        if (   count($results2) > 0
            && $results2->event_count_total != '') {

            $tmp_count2 = $results2->event_count_total;
            $tmp['downloads'] = $tmp_count2;

            if ($tmp_count2 > 0) {
                $has_data = true;
            }
            if ($tmp_count2 > $max_count) {
                $max_count = $tmp_count2;
            }
            
            $total_count2 += $tmp_count2;
        } else {
            $tmp['downloads'] = 0;
        }
        $downloads_arr[] = $tmp;
    }
    
	if ($has_data) {
	?>
	<script>
	    google.setOnLoadCallback(drawChart);
        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ["<?php _e('Päivä', 'hri'); ?>", "<?php _e('Sivunäytöt', 'hri'); ?>", "<?php _e('Lataukset', 'hri'); ?>"]
                <?php
                $is_first = false;
                $counter = 0;
                foreach ($downloads_arr as $downloads_by_day) {
                    $counter++;
                    if ($is_first) {
                        echo "['".$downloads_by_day['date']."', ".$downloads_by_day['pageviews'].", ".$downloads_by_day['downloads']."]";
                        $is_first = false;
                    } else {
                        echo ",\n". "['".$downloads_by_day['date']."', ".$downloads_by_day['pageviews'].", ".$downloads_by_day['downloads']."]";
                    }

                }
                ?>

            ]);
            
            var options = {
                title: '',
                height: '90',
                width: '250',
                colors: ['#084169', '#74c16d'],
                vAxis :{
                    gridlines: {
                        <?php
                        if ($max_count == 1) {
                        ?>
                        count:2
                        <?php
                        } else {
                        ?>
                        count:5
                        <?php
                        }
                        ?>
                        
                    }
                },
                hAxis: {
                    textPosition: 'none',
                    gridlines: {
                        count : 4
                    }
                },
                legend: {
                    position: 'bottom'
                }
            };

            var chart = new google.visualization.LineChart(document.getElementById('chart_div_post_<?php echo $post->ID; ?>'));
//            chart.draw(data, {width: 200, height: 30, showAxisLines: false,  showValueLabels: false, fill: false, labelPosition: 'left',color:'#0a4a74'});
            chart.draw(data, options);
          }
    </script>
    
    <div class="terms-list">
        <h6><?php _e('Sivunäytöt ja lataukset', 'hri'); ?></h6>
        <div style="width:245px; overflow: hidden; margin-left: -15px;" class="google_sparklines" id="chart_div_post_<?php echo $post->ID; ?>"></div>
        <div style="font-size:0.9em;"><?php printf(__('Tätä sivua on katsottu viimeisten %s päivän aikana %s kertaa.', 'hri'), $show_days, $total_count); ?></div>
        <div style="font-size:0.8em;"><?php printf(__('(Päivitetään kerran vuorokaudessa).', 'hri')); ?></div>
        
    </div>
    
    <?php

    }

	$terms = wp_get_post_terms( $post->ID, 'post_tag' );

	if( !empty($terms) ) {

		?><div class="terms-list"><h6><?php _e('Avainsanat', 'hri'); ?></h6><?php

		foreach( $terms as $term ) {

			?><a class="term" href="<?php echo DATA_SEARCH_URL, '#post_tag=', $term->term_id; ?>"><?php echo $term->name; ?></a><?php

		}

		?><div class="clear"></div></div><?php

	}

	$terms = wp_get_post_terms( $post->ID, 'category' );

	if( !empty($terms) ) {

		?><div class="terms-list"><h6><?php _e('Kategoriat', 'hri'); ?></h6><?php

		foreach( $terms as $term ) {

			?><a class="term" href="<?php echo DATA_SEARCH_URL, '#category=', $term->term_id; ?>"><?php echo $term->name; ?></a><?php

		}

		?><div class="clear"></div></div><?php

	}

	hri_add_this();

	?>

	<aside>

	<?php

	restore_current_blog();
	dynamic_sidebar('sidebar-data');
	switch_to_blog(1);


	$related_apps = new WP_Query(array(
		'post_type' => 'application',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'meta_query' => array(
			array(
				'key' => '_link_to_data',
				'value' => $post->ID
			)
		)
	));

	if( $related_apps->have_posts() ) {

		?><div class="h4-c"><h4 class="icon icon-small-apps"><?php _e( 'Sovellukset tästä datasta', 'hri' ); ?></h4></div><div class="sidebar-app-list"><?php

		while( $related_apps->have_posts() ) {

			$related_apps->the_post();

			?><div class="post-highlight clearfix"><a class="block left clearfix" href="<?php

				$url = hri_link( get_permalink(), HRI_LANG, 'application' );
				echo $url;

			?>"><?php hri_thumbnail('tiny-square'); ?></a><div class="highlight-excerpt"><a href="<?php echo $url; ?>"><?php the_title(); ?></a><p><?php the_hri_field( 'short_text' ); ?></p></div></div><?php

		}

		?></div><?php

		wp_reset_postdata();

	}

	unset( $related_apps );

	?>

	</aside>

</div>

