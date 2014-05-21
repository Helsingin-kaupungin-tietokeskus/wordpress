<form role="search" method="get" id="searchform" action="<?php echo home_url( '/' ); ?>">
    <div><label class="screen-reader-text" for="s">Search for:</label>
        <input type="text" value="<?php

$words = $_REQUEST['words'];

if ( $words ) echo esc_attr( str_replace( ',', ' ', $words) );

?>" name="s" id="s" placeholder="<?php _e('Search from entire HRI','twentyten'); ?>" />
        <input type="submit" id="searchsubmit" value="Search" />
    </div>
</form>