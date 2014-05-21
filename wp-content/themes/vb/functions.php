<?php

//tmp: add hri's image sizes for thumbnail regeneration
add_theme_support( 'post-thumbnails' );
add_image_size( 'tiny-square',	50, 50, true );
add_image_size( 'med-square',	160, 160, true );

add_action( 'admin_notices', function() {
	echo '<div class="error"><p>Huomio! Tämä sivusto ei ole enää käytössä. Sisältö on kopiotu <code>/fi/</code>-sivuston alle ja tämän blogin artikkelit toimivat ainoastaan uudelleenohjauksina <code>/fi/</code>-sivustolle.</p></div>';
});

?>