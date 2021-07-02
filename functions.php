<?php 

add_action( 'wp_enqueue_scripts', 'salient_child_enqueue_styles', 100);

function salient_child_enqueue_styles() {
		
		$nectar_theme_version = nectar_get_theme_version();
		wp_enqueue_style( 'salient-child-style', get_stylesheet_directory_uri() . '/style.css', '', $nectar_theme_version );
		
    if ( is_rtl() ) {
   		wp_enqueue_style(  'salient-rtl',  get_template_directory_uri(). '/rtl.css', array(), '1', 'screen' );
		}
}

/**
 * Search For Terms - Searches database for Specified Term
 * 
 * @param string - $term The Specified Term EG: Category
 * @return array|false - Returns Array of found Terms or False
 */
function search_for_terms( $term ) {
	// Use search term and cross-referrence Categories as well.
	$search_term = explode( ' ', get_search_query( false ) );
	global $wpdb;
	$select = "
	SELECT DISTINCT t.*, tt.* 
	FROM wp_terms AS t 
	INNER JOIN wp_term_taxonomy AS tt 
	ON t.term_id = tt.term_id 
	WHERE tt.taxonomy IN ('$term')";

	$first = true;
	foreach ( $search_term as $s ) :
		if ( $first ) :
			$select .= " AND (t.name LIKE '%s')";
			$string_replace[] = '%'.$wpdb->esc_like( $s ).'%';
			$first            = false;
		else :
			$select .= " OR (t.name LIKE '%s')";
			$string_replace[] = '%'. $wpdb->esc_like( $s ).'%';
		endif;
	endforeach; // endforeach ( $search_term as $s ) :
	$select .= " ORDER BY t.name ASC";
	$terms       = $wpdb->get_results( $wpdb->prepare( $select, $string_replace ) );
	if ( count( $terms ) > 0 ) :
		add_filter( 'body_class', function( $classes ) {
			return str_replace( 'search-no-results', 'search-results', $classes );
		} );
	endif;

	return $terms;
}

?>