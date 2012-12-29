<?php
/*
Plugin Name: APBC Sermon Widget
Plugin URI: http://tetrahedra.co.uk/ApbcSermonWidget
Description: Plugin and widget to allow display of lists of Sermon Custom Post Types
Version: 0.1 BETA
Author: John Adams
Author URI: http://www.tetrahedra.co.uk
*/

/*
APBC Sermon Widget (Wordpress Widget Plugin)
Copyright (C) 2012 John Adams
Contact me at http://www.tetrahedra.co.uk

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/


/**
 * Add function to widgets_init that'll load our widget.
 * @since 0.1
 */
add_action( 'widgets_init', 'apbc_sermon_load_widgets' );

/**
 * Register our widget.
 * 'Apbc_Sermon_Widget' is the widget class used below.
 *
 * @since 0.1
 */
function apbc_sermon_load_widgets() {
	register_widget( 'Apbc_Sermon_Widget' );
}

/**
 * Apbc_Sermon_Widget class.
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update.  Nice!
 *
 * @since 0.1
 */
class Apbc_Sermon_Widget extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function Apbc_Sermon_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'sermon', 'description' => __('A widget to display lists of sermons from APBC Custom Post Types') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'sermon-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'sermon-widget', __('Apbc_Sermon'), $widget_ops, $control_ops );
	}
	
	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$errorMsg = $instance['error'];
		//$number = $instance['number'];
		//$category = $instance['series'];

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;

		//Query 
		$sermons = new WP_Query( array( 'post_type' => 'apbc_sermon', 'posts_per_page' => 5 ) ); 
		echo '<ul>';
		
		while ( $sermons->have_posts() ) : $sermons->the_post(); 
    		echo '<li>';
    		echo get_the_date() . '<br />';
    		echo '<a href="' . get_permalink() . '">' . get_the_title() . '</a> ';
    		echo '(' . get_the_author() . ')';
    		if (get_post_meta(get_the_ID(), '_audio', true)) 
				echo '.<img src="' . get_stylesheet_directory_uri() . '/podcast.png" alt="podcast available" title="podcast available" height="15px" />';
    		echo '</li>';
		endwhile;

		echo '</ul>';
		echo '<a href="http://www.apbc.net/worship/sermons-2/">More sermons</a>';
		
		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = strip_tags( $new_instance['number'] );
		//$instance['series'] = strip_tags( $new_instance['series'] );
		//$instance['error'] = strip_tags( $new_instance['error'] );

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */

        $defaults = array( 'title' => __('Recent Attachments'), 'num' => __(5), 'error' => __("No documents found."), 'parent' => null );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
		</p>

		<!-- Number: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e('Number:'); ?></label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" value="<?php echo $instance['number']; ?>" />
		</p>

	<?php

	}
}

    /*
     * General plugin functions
     */

    // [apbcsermons series="1-Peter" preacher="stephen"]
    function apbcsermons_func( $atts ) {
        extract( shortcode_atts( array(
            'series' => '',
            'preacher' => '',
        ), $atts ) );

		//if ($series) {
            return showSermonList($series);
		//}
		//else {
		//	echo $errorMsg;
		//}
    }

    function showSermonList($series) {
 		//$sermons = new WP_Query( array( 'post_type' => 'apbc_sermon'));
 		$sermons = new WP_Query( array( 'post_type' => 'apbc_sermon', 'series' => $series, 'posts_per_page' => -1) ); 
		$output = '<div id="inline_sermonlist">';
		$output .= '<ul>';
		
		while ( $sermons->have_posts() ) : $sermons->the_post(); 
    		$output .= '<li>';
    		$output .= '<a href="' . get_permalink() . '">' .  get_post_meta(get_the_ID(), '_passage', true) . " - " . get_the_title() . '</a> ';
     		if (get_post_meta(get_the_ID(), '_audio', true)) 
				$output .= '<img src="' . get_stylesheet_directory_uri() . '/podcast.png" alt="podcast available" title="podcast available" />';
   			$output .= '<br />' . get_the_author() . ', ' . get_the_date() . ' ';
    		$output .= '</li>';
		endwhile;

		$output .= '</ul>';
		$output .= '</div>';

		wp_reset_query();

        return $output;

    }

    add_shortcode( 'apbcsermons', 'apbcsermons_func' );

?>