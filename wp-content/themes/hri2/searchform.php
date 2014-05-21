<form role="search" method="get" id="searchform" action="<?php echo home_url( '/' ); ?>">
	<input type="text" value="<?php

$words = isset( $_REQUEST['words'] ) ? $_REQUEST['words'] : false;

if ( $words ) echo esc_attr( str_replace( ',', ' ', $words) );

?>" name="s" id="s" placeholder="<?php _e('Hae sivustolta...', 'hri'); ?>" />
<?php /*
	<div id="searchselect-c">
		<span id="searchselecttext"><?php _e( 'Kaikkea', 'hri' ); ?></span>
		<select name="searchselect" id="searchselect">
			<option value="1"><?php _e( 'Kaikkea', 'hri' ); ?></option>
		</select>
	</div>

<script type="text/javascript">
// <!--
var $=jQuery;
$('#searchselect').change(function(){
	$('#searchselecttext').text( $(this).find('option:selected').text() );
});
// -->
</script>
 */ ?>
	<input type="submit" id="searchsubmit" value="<?php _e('Hae','hri'); ?>" />
</form>