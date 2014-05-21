<?php
class TwitterGoodiesWidgetsWidget extends WP_Widget {
	var $widget_types = array('list', 'profile', 'faves', 'search');
	
    /** constructor */
    function TwitterGoodiesWidgetsWidget() {
    	$widget_ops = array('description' => __( "Add one of the four official twitter widgets to your blog.") );
        parent::WP_Widget(false, $name = 'Twitter Goodies Widgets', $widget_ops);
        if( is_admin() ){
        	add_action('admin_head', array(&$this, 'head'));
        }
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
    	global $TwitterGoodiesWidgets;
    	$TwitterGoodiesWidgets->display($instance, $args);
    }
    
    function head(){
    	?>
    	<script type="text/javascript">
			var tgw_change_type = function( binding, value ){
				switch( value ){
					case "profile":
						jQuery('#'+binding+' .tgw-title').hide();
						jQuery('#'+binding+' .tgw-subject').hide();
						jQuery('#'+binding+' .tgw-search').hide();
						jQuery('#'+binding+' .tgw-username').show();
						jQuery('#'+binding+' .tgw-list').hide();
						break;
					case "search":
						jQuery('#'+binding+' .tgw-title').show();
						jQuery('#'+binding+' .tgw-subject').show();
						jQuery('#'+binding+' .tgw-search').show();
						jQuery('#'+binding+' .tgw-username').hide();
						jQuery('#'+binding+' .tgw-list').hide();						
						break;
					case "faves":
						jQuery('#'+binding+' .tgw-title').show();
						jQuery('#'+binding+' .tgw-subject').show();
						jQuery('#'+binding+' .tgw-search').hide();
						jQuery('#'+binding+' .tgw-username').show();
						jQuery('#'+binding+' .tgw-list').hide();						
						break;
					case "list":
						jQuery('#'+binding+' .tgw-title').show();
						jQuery('#'+binding+' .tgw-subject').show();
						jQuery('#'+binding+' .tgw-search').hide();
						jQuery('#'+binding+' .tgw-username').show();
						jQuery('#'+binding+' .tgw-list').show();						
						break;
				}
			}
			var tgw_get_list = function( binding ){
				jQuery('#'+binding+' .tgw-list-button').val('Loading ...');
				jQuery.getJSON(
					'<?php echo admin_url("admin-ajax.php"); ?>',
					{ 
						'action':'tgw_ajax_lists',
						'_wpnonce':'<?php echo wp_create_nonce('tgw_ajax_lists')?>',
						'username':jQuery('#'+binding+' .tgw-username input').val()						
					},
					function(data){
						data = jQuery(data.lists);
						if( data.length > 0  ){
							jQuery('#'+binding+' .tgw-list select').empty();
							for( i=0; i<data.length; i++ ){
								name = (data[i].name) ? data[i].name:data[i].slug;
								jQuery('#'+binding+' .tgw-list select').append(jQuery('<option value="'+data[i].slug+'">'+name+'</option>'));
							};
							jQuery('#'+binding+' .tgw-list-button').val('<?php _e('Get Lists') ?>');
						}else{
							jQuery('#'+binding+' .tgw-list select').append(jQuery('<option value="0"><?php _e('No Lists Available') ?></option>'));
						}
					}
				);
			}
		</script>    	
    	<?php
    }

    /** @see WP_Widget::form */
    function form($instance) {				
        $tgw_data = get_option('tgw_data');
        $id = rand(0,100000000000); //Random id for widget (to use in js functions, $this->id went crazy)
        
        //First we have a selection of the theme to use, this is constant across all widgets
		if( is_array($tgw_data['themes']) && count($tgw_data['themes'])>0 ){
			?>
			<p>
				<label for="<?php echo $this->get_field_id('tgw_theme'); ?>"><?php _e('Widget Theme') ?></label>
				<select name="<?php echo $this->get_field_name('tgw_theme'); ?>" id="<?php echo $this->get_field_id('tgw_theme'); ?>">
					<?php 
					foreach( array_keys($tgw_data['themes']) as $theme_key ){
						?>
						<option <?php echo ($instance['tgw_theme'] == $theme_key) ? 'selected="selected"':""; ?>><?php echo $theme_key ?></option>
						<?php
					}
					?>
				</select>
			</p> 
			<?php 
		}else{
			?>
			<p style="font-style:italic">You currently have no themes, default values will be used. <a href="options-general.php?page=twitter-goodies-widgets">Create a new theme.</a></p>
			<?php
		}
		
		//Now we worry about what widget to show
		?>
		<p>
			<label for="<?php echo $this->get_field_id('type'); ?>"><?php _e('Widget Type') ?></label>
			<select name="<?php echo $this->get_field_name('type'); ?>" id="<?php echo $this->get_field_id('type'); ?>" onchange="tgw_change_type( jQuery(this).closest('.widget').attr('id'), this.value );">
				<?php 
				foreach ( $this->widget_types as $type ){
					?>
					<option <?php echo ($instance['type'] == $type) ? 'selected="selected"':""; ?>><?php echo $type ?></option>
					<?php
				}
				?>
			</select> 
		</p>
		<?php
		 		
		//Depending on the choice above, we show the relevant form fields.
		?>	
        <p class="tgw-title" style="<?php echo ($instance['type']=='profile') ? 'display:none;':'' ?>">
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title'); ?></label>
            <input name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title'] ?>" />
		</p>
        <p class="tgw-subject" style="<?php echo ($instance['type']=='profile') ? 'display:none;':'' ?>">
            <label for="<?php echo $this->get_field_id('subject'); ?>"><?php _e('Caption'); ?></label>
            <input name="<?php echo $this->get_field_name('subject'); ?>" type="text" value="<?php echo $instance['subject'] ?>" />
		</p>
        <p class="tgw-search" style="<?php echo ($instance['type']!='search') ? 'display:none;':'' ?>">
            <label for="<?php echo $this->get_field_id('search'); ?>"><?php _e('Search'); ?></label>
            <input name="<?php echo $this->get_field_name('search'); ?>" type="text" value="<?php echo $instance['search'] ?>" />
		</p>
        <p class="tgw-username" style="<?php echo ($instance['type']=='search') ? 'display:none;':'' ?>">
            <label for="<?php echo $this->get_field_id('tgw_username'); ?>"><?php _e('Username'); ?></label>
            <input name="<?php echo $this->get_field_name('tgw_username'); ?>" type="text" value="<?php echo $instance['tgw_username'] ?>" onkeypress="jQuery(this).parent().next().children('select').empty()" />
		</p>
        <p class="tgw-list" style="<?php echo ($instance['type']!='list') ? 'display:none;':'' ?>">
            <label for="<?php echo $this->get_field_id('tgw_list'); ?>"><?php _e('List'); ?></label>
            <select name="<?php echo $this->get_field_name('tgw_list'); ?>" >
            	<?php if($instance['tgw_list'] != ''): ?>
            	<option value="<?php echo $instance['tgw_list'] ?>"><?php echo $instance['tgw_list'] ?></option>
            	<?php endif; ?>
            </select>
            <input type="button" value="<?php _e('Get Lists') ?>" class='tgw-list-button' onclick="tgw_get_list( jQuery(this).closest('.widget').attr('id') )" />
		</p>
		<script>
			tgw_change_type( jQuery(this).closest('.widget').attr('id'), '<?php echo $instance['type'] ?>' );
		</script>
        <?php 
    }

}
?>