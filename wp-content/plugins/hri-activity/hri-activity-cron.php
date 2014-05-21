<?php

if (isset($_GET['207AE66E72933CAB54DF111568C1D8ED'])) {

	function hri_notify_email() {
		//	file_put_contents( ABSPATH . '/hricron.txt', date('H:i:s') . "\n", FILE_APPEND);

		global $wpdb;

		$query = "(SELECT COUNT(1) AS c, post_type, 1 AS source FROM wp_posts WHERE post_status = 'pending' GROUP BY post_type) UNION
(SELECT COUNT(1) AS c, post_type, 2 AS source FROM wp_2_posts WHERE post_status = 'pending' GROUP BY post_type) UNION
(SELECT COUNT(1) AS c, post_type, 3 AS source FROM wp_3_posts WHERE post_status = 'pending' GROUP BY post_type) UNION
(SELECT COUNT(1) AS c, post_type, 4 AS source FROM wp_4_posts WHERE post_status = 'pending' GROUP BY post_type)";

		$results = $wpdb->get_results($query);

		$message_rows = array();

		if ($results) {

			$post_types = array(
				'post' => array('artikkeli', 'artikkelia'),
				'page' => array('sivu', 'sivua'),
				'data' => array('data', 'dataa'),
				'application' => array('sovellus', 'sovellusta'),
				'application-idea' => array('sovellusidea', 'sovellusideaa'),
				'data-request' => array('datatoive', 'datatoivetta'),
				'discussion' => array('keskustelun avaus', 'keskustelun avausta')
			);

			foreach ($results as $r) {

				switch_to_blog($r->source);

				$site = '';
				switch($r->source) {
					case 1: $site = '/'; break;
					case 2: $site = '/fi'; break;
					case 3: $site = '/en'; break;
					case 4: $site = '/se'; break;
				}

				$field = $r->c == 1 ? 0 : 1;

				$message_rows[] = "\nSivusto $site: {$r->c} {$post_types[ $r->post_type ][$field]}: " . home_url('/wp-admin/edit.php?post_status=pending&post_type=') . $r->post_type;

				restore_current_blog();

			}
		}

		$query = "(SELECT COUNT(1) AS c, 1 AS source FROM wp_comments WHERE comment_approved = '0') UNION
(SELECT COUNT(1) AS c, 2 AS source FROM wp_2_comments WHERE comment_approved = '0') UNION
(SELECT COUNT(1) AS c, 3 AS source FROM wp_3_comments WHERE comment_approved = '0') UNION
(SELECT COUNT(1) AS c, 4 AS source FROM wp_4_comments WHERE comment_approved = '0');";

		$results = $wpdb->get_results($query);
		if ($results) {
			foreach ($results as $r) {
				if ($r->c > 0) {

					switch_to_blog($r->source);

					$site = '';
					switch($r->source) {
						case 1: $site = '/'; break;
						case 2: $site = '/fi'; break;
						case 3: $site = '/en'; break;
						case 4: $site = '/se'; break;
					}

					$word = $r->c > 1 ? 'kommenttia' : 'kommentti';
					$message_rows[] = "\nSivusto $site: {$r->c} $word: " . home_url('/wp-admin/edit-comments.php?comment_status=moderated');

					restore_current_blog();

				}
			}
		}

		if (!empty($message_rows)) {

			$message = "HRI-verkkopalvelussa on moderointia odottavaa sisältöä:\n";
			$message .= implode($message_rows);

			$query = "SELECT * FROM {$wpdb->users}, {$wpdb->usermeta} WHERE user_id = ID AND meta_key = 'hri_digest_interval' AND meta_value ";

			if (date('w') == 1) {
				$query .= "IN(1,2)";
			} else {
				$query .= "= 1";
			}

			$users = $wpdb->get_results($query);

			if ($users) {

//				$from = get_option('admin_email');
				$from = 'notifications@hri.fi';
				$headers = "From: HRI <$from>\r\n";

				foreach ($users as $user) {
					$mail_to[] = $user->user_email;
				}

				if (isset($mail_to) && !empty($mail_to)) wp_mail($mail_to, 'HRI mail', $message, $headers);

			} // no users to notify

		} // nothing to moderate

		if( !headers_sent() ) {
			header('HTTP/1.1 200 OK');
			echo '200';
		}
		exit;
	}

	add_action('init', 'hri_notify_email');

}

?>