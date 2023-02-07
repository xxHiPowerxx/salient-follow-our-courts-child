<?php
/**
 * Widget areas.
 *
 * @package salient
 */

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function foc_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'After Single Blog Post', 'salient' ),
		'id'            => 'after-single-post',
		'description'   => esc_html__( 'Add Widgets the bottom of a Single Blog Post.', 'salient' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
	));
}
add_action( 'widgets_init', 'foc_widgets_init' );