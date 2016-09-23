		<div class="clear"></div>
	</div><!-- #main -->
</div><!-- /div.bg -->

<footer class="wrapper">

	<div class="top-scroll">
		<div class="wrapper rel">
			<a href="#top"></a>
		</div>
	</div>

	<div class="footer-col footer-col-1st">
		<div id="copyright">
			<img id="logo-footer" src="<?php bloginfo('template_url'); ?>/img/logo-footer.png" alt="Helsinki Region Infoshare"/>
			<?php // HRI-165: Add language specific suffix to all city URLs.
			$url_suffix = '';
			$espoo_url = 'http://www.espoo.fi';
			if(HRI_LANG != 'fi') {
				$url_suffix = "/" . HRI_LANG;
				if(HRI_LANG == 'en') {
					$espoo_url .= '/en-US';
				}
				else if(HRI_LANG == 'sv') {
					$espoo_url .= '/sv-FI';
				}
			}
			?>
			<div>
				<a href="http://www.hel.fi<?php echo $url_suffix; ?>"><img src="/wp-content/uploads/2016/01/Helsingin_Vaakuna_RGB_png_62254.png" height="48" /></a>
				<a href="<?php echo $espoo_url; ?>"><img src="/wp-content/uploads/2016/01/Espoo_vaakuna.png" height="48" /></a>
				<a href="http://www.vantaa.fi<?php echo $url_suffix; ?>"><img src="/wp-content/uploads/2016/01/Vantaa_vaakuna.png" height="48" /></a>
				<a href="http://www.kauniainen.fi<?php echo $url_suffix; ?>"><img src="/wp-content/uploads/2016/01/Kauniainen_vaakuna.png" height="48" /></a>
			</div>
		</div>
		<?php if(HRI_LANG == 'fi' || HRI_LANG == 'en'): ?>
		<div id="site-search">
			<form action="<?php echo home_url() . '/' . HRI_LANG; ?>" id="searchform" method="get" role="search" class="hri-search">
				<input type="text" placeholder="<?php _e('Hae sivustolta...', 'hri'); ?>" id="s" name="s" value="" class="hri-input">
				<input type="submit" value="Hae" id="searchsubmit" class="hri-submit">
			</form>
		</div>
		<?php endif; ?>
		<nav id="footer-nav">
			<?php

			while(restore_current_blog());

			wp_nav_menu( array( 'container' => false, 'theme_location' => 'footer', 'fallback_cb' => false ) );

			?>
		</nav>
	</div>
	<div class="footer-col footer-col-2nd">
	
	</div>
	<div class="footer-col footer-col-3rd">
		<nav>
			<ul>
				<li><em><?php _e('Seuraa HRI:tä', 'hri'); ?></em></li>
				
				<li><a href="http://www.facebook.com/helsinkiregioninfoshare" class="a-facebook">Facebook</a></li>
				<li><a href="https://github.com/Helsingin-kaupungin-tietokeskus" class="a-github">Github</a></li>
				<?php

				if ( ORIGINAL_BLOG_ID != 4 ) {

					?><li><a href="<?php echo home_url('/feed'); ?>" class="a-rss"><?php _e('RSS-syötteet', 'hri'); ?></a></li><?php

				}

				?>

				<li><a href="http://www.slideshare.net/helsinkiregioninfoshare" class="a-slideshare">Slideshare</a></li>
				<li><a href="https://twitter.com/HRInfoshare" class="a-twitter">Twitter</a></li>
				
				<?php

				if( ORIGINAL_BLOG_ID != 3 && ORIGINAL_BLOG_ID != 4 ) {

					?><li><a href="<?php echo home_url(); ?>/uutiskirje" class="a-email"><?php _e('Uutiskirje', 'hri'); ?></a></li><?php

				}

				?>
				<li><a href="https://www.youtube.com/user/HRInfoshare" class="a-youtube">Youtube</a></li>
			</ul>
		</nav>

		<span style="float: right; margin-top: -25px;"><em>© Helsinki Region Infoshare</em><?php _e('Joitakin oikeuksia pidätetään.', 'hri'); ?></span>

		<?php
		if(HRI_LANG == 'sv') {
			// Add a little dummy element to push the copyright text above to the same level as other languages.
			?><span style="height: 39px; width: 100%;"><br><br></span><?php
		}
		?>
	</div>
	<div class="clear"></div>
</footer>
<?php

if ( function_exists( 'yoast_analytics' ) ) { yoast_analytics(); }

wp_footer();

?>
<!-- BEGIN Snoobi v1.4 -->
<script type="text/javascript" src="http://eu1.snoobi.com/snoop.php?tili=hel_fi_hki_tieke"></script>
<!-- END Snoobi v1.4 -->
</body>
</html>