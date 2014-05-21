<?php
/**
 * The Sidebar containing the primary and secondary widget areas.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

restore_current_blog();
?>

<?php include_once('pagesidebar.php') ?>


		<div id="primary" class="widget-area" role="complementary">
			<?php
			if (   isset($post)
				&& (   is_array($post->ancestors)
					&& $post->ancestors[0] == 877)
				|| $post->ID == 877) {
				?>
				<div class="call_to_action_container">
					<a href="/fi/nain-julkaiset-dataa-hri-verkkopalveluun/" class="no-hover"><h3 class="call_to_action_title add_data">Avaa dataa!</h3></a>
					<div class="call_to_action_image_container"><img src="<?php echo home_url( '/' ); ?>wp-content/themes/hri/images/datatray.jpeg" /></div>
					<p>Onko edustamallasi virastolla tai liikelaitoksella tietoaineistoja, joiden jakamisesta voisi olla kaikille hyötyä? Julkaise aineistosi avoimena datana!</p>
					<a class="readmore" href="/fi/tietoa-avoimesta-datasta/nain-julkaiset-dataa-hri-verkkopalveluun/">Lue tämä ensin: ohjeet alkuun pääsemiseksi</a>
					<a class="readmore" href="/fi/opas/nain-julkaiset-dataa-hri-verkkopalveluun/ilmoita-tietoaineisto/">Ilmoita tietoaineisto -lomake</a>
				</div>
				<?php
			}
			?>
			<ul class="xoxo">
					<?php
					global $args;
					if ( $args['posttype'] == 'application' ) {
						echo '<li>';
						if ( ORIGINAL_BLOG_ID == 2 ) {
							echo '<a class="no-hover" href="' . ROOT_URL . '/fi/ilmoita-uusi-sovellus/"><h3 class="widget-title-link title-add-application">Ilmoita uusi sovellus</h3></a>';
							?>
							<div class="call_to_action_container">
								<a href="/fi/uusi-sovellusidea/" class="no-hover"><h3 class="call_to_action_title add_application_idea">Jaa sovellusidea</h3></a>
								<div class="call_to_action_image_container"><img src="<?php echo home_url( '/' ); ?>wp-content/themes/hri/images/applicationtray.jpg" /></div>
								<p>Onko sinulla idea siitä, miten avointa dataa voisi hyödyntää tai avoimen datan soveltamisesta? Jakamalla ideasi olemme yhden askeleen lähempänä sen toteutumista</p>
								<a class="readmore" href="/fi/uusi-sovellusidea/">Esitä sovellusidea -lomake</a>
							</div>
							<?php
						}
						if ( ORIGINAL_BLOG_ID == 3 ) {
							echo '<a class="no-hover" href="' . ROOT_URL . '/en/new-application-submission/"><h3 class="widget-title-link title-add-application">Application submission</h3></a>';
						}
						echo '</li>';
					}
					?>

<?php dynamic_sidebar( 'primary-widget-area' ); ?>
			</ul>
			
		</div><!-- #primary .widget-area -->
	
