<?php
/**
 * Widget & shortcode that display random images from your media library as an embedded iframe of the Content Slideshow plugin's page.
*/

// Register 'Content Slideshow' widget.
function content_slideshow_widget_init() {
	return register_widget( 'Content_Slideshow_Widget' );
}
add_action( 'widgets_init', 'content_slideshow_widget_init' );

// Add the Content Slideshow shortcode.
add_shortcode( 'content_slideshow', 'content_slideshow_do_shortcode' );
function content_slideshow_do_shortcode( $atts ){
	extract( shortcode_atts( array(
		'size'  => 'auto',
		'year'  => '',
		'month' => '',
		'mode'  => '',
		'captions'  => 'auto',
	), $atts ) );

	return content_slideshow_get_embed( $size, $year, $month, $mode, $captions );
}

class Content_Slideshow_Widget extends WP_Widget {
	/* Constructor */
	function __construct() {
		parent::__construct( 'Content_Slideshow_Widget', __( 'Content Slideshow', 'content-slideshow' ), array( 
			'customize_selective_refresh' => true,
		) );
	}

	/* This is the Widget */
	function widget( $args, $instance ) {
		global $post;
		extract( $args );

		if ( ! array_key_exists( 'size', $instance ) ) {
			$instance['size'] = 'medium';
		}

		if ( ! array_key_exists( 'title', $instance ) ) {
			$instance['title'] = '';
		}

		if ( ! array_key_exists( 'captions', $instance ) ) {
			$instance['captions'] = '';
		}

		$mode_featured = isset( $instance['mode_featured'] ) ? (bool) $instance['mode_featured'] : false;

		// Widget options
		$title = apply_filters('widget_title', $instance['title'] ); // Title
		$size  = ( in_array( $instance['size'], array( 'thumbnail', 'medium', 'large', 'full' ) ) ? $instance['size'] : 'medium' );
		$mode = $mode_featured ? 'featured' : false;
		$captions = ( in_array( $instance['captions'], array( 'auto', 'none', 'title', 'titlecaption', 'caption', 'description' ) ) ? $instance['captions'] : 'auto' );

		// Output
		echo $before_widget;

		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		echo content_slideshow_get_embed( $size, '', '', $mode, $captions );

		echo $after_widget;
	}

	/* Widget control update */
	function update( $new_instance, $old_instance ) {
		$instance    = $old_instance;
		$instance['title']  = strip_tags( $new_instance['title'] );
		$instance['size'] = ( in_array( $new_instance['size'], array( 'thumbnail', 'medium', 'large', 'full' ) ) ? $new_instance['size'] : 'medium' );
		$instance['mode_featured'] = isset( $new_instance['mode_featured'] ) ? (bool) $new_instance['mode_featured'] : false;
		$instance['captions'] = ( in_array( $new_instance['captions'], array( 'auto', 'none', 'title', 'titlecaption', 'caption', 'description' ) ) ? $new_instance['captions'] : 'auto' );

		return $instance;
	}

	/* Widget settings */
	function form( $instance ) {
	    if ( $instance ) {
			$title = $instance['title'];
			$size  = $instance['size'];
			$captions  = $instance['captions'];
	    }
		else {
		    // These are the defaults.
			$title = '';
			$size = 'medium';
			$captions = 'auto';
	    }

		$mode_featured = isset( $instance['mode_featured'] ) ? (bool) $instance['mode_featured'] : false;

		// The widget form. ?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __( 'Title:', 'content-slideshow' ); ?></label>
			<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" class="widefat" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('captions'); ?>"><?php echo __( 'Captions:', 'content-slideshow' ); ?></label>
			<select name="<?php echo $this->get_field_name('captions'); ?>" id="<?php echo $this->get_field_id('captions'); ?>" class="widefat">
				<option value="auto" <?php if( 'auto' === $captions ) { echo 'selected="selected"'; } printf( '>%s', __( 'Automatic', 'content-slideshow' ) ); ?></option>
				<option value="none" <?php if( 'none' === $captions ) { echo 'selected="selected"'; } printf( '>%s', __( 'None', 'content-slideshow' ) ); ?></option>
				<option value="title" <?php if( 'title' === $captions ) { echo 'selected="selected"'; } printf( '>%s', __( 'Title', 'content-slideshow' ) ); ?></option>
				<option value="titlecaption" <?php if( 'titlecaption' === $captions ) { echo 'selected="selected"'; } printf( '>%s', __( 'Title & Caption', 'content-slideshow' ) ); ?></option>
				<option value="caption" <?php if( 'caption' === $captions ) { echo 'selected="selected"'; } printf( '>%s', __( 'Caption', 'content-slideshow' ) ); ?></option>
				<option value="description" <?php if( 'description' === $captions ) { echo 'selected="selected"'; } printf( '>%s', __( 'Description', 'content-slideshow' ) ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('size'); ?>"><?php echo __( 'Image Size:', 'content-slideshow' ); ?></label>
			<select name="<?php echo $this->get_field_name('size'); ?>" id="<?php echo $this->get_field_id('size'); ?>" class="widefat">
				<option value="thumbnail" <?php if( 'thumbnail' === $size ) { echo 'selected="selected"'; } printf( '>%s', __( 'Thumbnail', 'content-slideshow' ) ); ?></option>
				<option value="medium" <?php if( 'medium' === $size ) { echo 'selected="selected"'; } printf( '>%s', __( 'Medium', 'content-slideshow' ) ); ?></option>
				<option value="large" <?php if( 'large' === $size ) { echo 'selected="selected"'; } printf( '>%s', __( 'Large', 'content-slideshow' ) ); ?></option>
				<option value="full" <?php if( 'full' === $size ) { echo 'selected="selected"'; } printf( '>%s', __( 'Full', 'content-slideshow' ) ); ?></option>
			</select>
		</p>
		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('mode_featured'); ?>" name="<?php echo $this->get_field_name('mode_featured'); ?>"<?php checked( $mode_featured ); ?> />
			<label for="<?php echo $this->get_field_id('mode_featured'); ?>"><?php _e( 'Featured Images Only', 'content-slideshow' ); ?></label>
		</p>
	<?php 
	}

} // class Content_Slideshow_Widget

function content_slideshow_get_embed( $size = 'auto', $year = '', $month = '', $mode = '', $captions = 'auto' ) {
	$url = '/' . _x( 'slideshow', 'content-slideshow', 'slideshow URL slug' );
	$args = array(
		'size'     => $size,
		'year'     => $year,
		'month'    => $month,
		'mode'     => $mode,
		'captions' => $captions,
	);
	$url = add_query_arg( $args, $url );

	$html = '<a href="' . home_url( remove_query_arg( 'size', $url ) ) . '" target="_blank"><div class="content-slideshow-widget-container" style="position: relative; width: 100%; height: 0; padding-bottom: 66.67%;">';
	$html .= '<iframe src="' . home_url( $url ) . '" loading="lazy" style="position: absolute; top:0; left: 0; width: 100%; height: 100%; border: none;"></iframe>';
	$html .= '</div></a>';

	return $html;
}
