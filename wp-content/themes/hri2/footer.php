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
			<div>
				<em>© Helsinki Region Infoshare</em><?php _e('Joitakin oikeuksia pidätetään.', 'hri'); ?>
			</div>
		</div>
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
				
				<?php

				if( ORIGINAL_BLOG_ID != 3 && ORIGINAL_BLOG_ID != 4 ) {

					?><li><a href="<?php

					echo ROOT_URL;
					if( ORIGINAL_BLOG_ID == 2 ) echo '/fi/sahkopostilista/';

					?>" class="a-email"><?php _e('Sähköpostilista', 'hri'); ?></a></li><?php

				}

				if ( ORIGINAL_BLOG_ID != 4 ) {

				?><li><a href="<?php echo home_url('/feed'); ?>" class="a-rss"><?php _e('RSS-syötteet', 'hri'); ?></a></li><?php

				}

				?>
				<li><a href="http://www.facebook.com/helsinkiregioninfoshare" class="a-facebook">Facebook</a></li>
				<li><a href="https://twitter.com/intent/user?screen_name=HRInfoshare" class="a-twitter">Twitter</a></li>
				<li><a href="http://www.slideshare.net/helsinkiregioninfoshare" class="a-slideshare">Slideshare</a></li>
			</ul>
		</nav>
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