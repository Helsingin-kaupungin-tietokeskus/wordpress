<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content
 * after.  Calls sidebar-footer.php for bottom widgets.
 *
 * @package WordPress
 * @subpackage HRI
 * @since HRI 0.1
 */
?>
	</div><!-- #main -->
</div><!-- #wrapper -->
</div><!-- #maincontainer -->

<div id="footer" role="contentinfo">

	<div class="floatl footer-column">
			
		<div class="copyright"><img src="<?php echo home_url( '/' ); ?>wp-content/themes/hri/images/footer_logo.png" alt="Helsinki Region Infoshare" />
		    <ul id="footer_links">
		    <?php 
		    restore_current_blog();
		    dynamic_sidebar( 'second-footer-widget-area' ); 
		    ?>
		    </ul>
		</div>
		
	</div>
	
	<?php get_sidebar( 'footer' ); ?>	
	
	<p class="poweredby">Powered by <a href="http://www.ckan.net" target="_blank">CKAN</a> and <a href="http://www.wordpress.org" target="_blank">Wordpress</a>.</p>
	</div><!-- #footer -->

<?php wp_footer(); ?>

<?php
if ( function_exists( 'yoast_analytics' ) ) {
	yoast_analytics();
}
?>

</body>
</html>
