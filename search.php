<?php
/**
 * The template for search results.
 *
 * @package Salient WordPress Theme
 * @version 10.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$terms       = search_for_terms( 'category' );
$found_terms = count( $terms );
$have_terms  = $found_terms > 0;

get_header();

global $nectar_options;
$header_format = ( ! empty( $nectar_options['header_format'] ) ) ? $nectar_options['header_format'] : 'default';
$theme_skin    = ( ! empty( $nectar_options['theme-skin'] ) ) ? $nectar_options['theme-skin'] : 'original';
if ( 'centered-menu-bottom-bar' === $header_format ) {
	$theme_skin = 'material';
}

$search_results_layout           = ( ! empty( $nectar_options['search-results-layout'] ) ) ? $nectar_options['search-results-layout'] : 'default';
$search_results_header_bg_image  = ( ! empty( $nectar_options['search-results-header-bg-image'] ) && isset( $nectar_options['search-results-header-bg-image'] ) ) ? nectar_options_img( $nectar_options['search-results-header-bg-image'] ) : null;

?>

<div id="page-header-bg" data-midnight="light" data-text-effect="none" data-bg-pos="center" data-alignment="center" data-alignment-v="middle" data-height="250">

	<?php if ( $search_results_header_bg_image ) { ?>
		<div class="page-header-bg-image-wrap" id="nectar-page-header-p-wrap">
			<div class="page-header-bg-image" style="background-image: url(<?php echo esc_url( $search_results_header_bg_image ); ?>);"></div>
		</div> 
		
		<div class="page-header-overlay-color"></div> 
	<?php } ?>
	
	<div class="container">
		<div class="row">
			<div class="col span_6 ">
				<div class="inner-wrap">
					<h1><?php echo esc_html__( 'Results For', 'salient' ); ?> <span>"<?php echo esc_html( get_search_query( false ) ); ?>"</span></h1>
					<?php
					$found_posts = $wp_query->found_posts;
					$found_posts_and_terms = $found_terms + $found_posts;
					if ( $found_posts > 0 || $found_terms > 0 ) :
						echo '<span class="result-num">' . esc_html( $found_posts_and_terms ) . ' ' . esc_html__( 'results found', 'salient' ) . '</span>';
					endif;
					?>
				</div>
			</div>
		</div>
	</div>
</div>


<div class="container-wrap" data-layout="<?php echo esc_attr( $search_results_layout ); ?>">
	
	<div class="container main-content">

		<div class="row">
			
			<?php $search_col_span = ( $search_results_layout === 'default' ) ? '9' : '12'; ?>
			<div class="col span_<?php echo esc_attr( $search_col_span ); // WPCS: XSS ok. ?>">
				
				<div id="search-results" data-layout="<?php echo esc_attr( $search_results_layout ); ?>">
						
					<?php

					add_filter( 'wp_get_attachment_image_attributes', 'nectar_remove_lazy_load_functionality' );

					if ( have_posts() || $have_terms ) :
						// Posts
						while ( have_posts() ) :

							the_post();

							$using_post_thumb = has_post_thumbnail( $post->ID );

							if ( get_post_type( $post->ID ) === 'post' ) {
								?>
								<article class="result" data-post-thumb="<?php echo esc_attr( $using_post_thumb ); ?>">
									<div class="inner-wrap">
										<?php
										if ( has_post_thumbnail( $post->ID ) ) {
											echo '<a href="' . esc_url( get_permalink() ) . '">' . get_the_post_thumbnail( $post->ID, 'full', array( 'title' => '' ) ) . '</a>';
										}
										?>
										<h2 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> <span><?php echo esc_html__( 'Blog Post', 'salient' ); ?></span></h2>
										<?php
										if ( $search_results_layout === 'list-no-sidebar' ) {
											the_excerpt(); }
										?>
									</div>
								</article><!--/search-result-->	
								
								<?php
							} elseif ( get_post_type( $post->ID ) === 'page' ) {
								?>
								<article class="result" data-post-thumb="<?php echo esc_attr( $using_post_thumb ); ?>">
									<div class="inner-wrap">
										<?php
										if ( has_post_thumbnail( $post->ID ) ) {
											echo '<a href="' . esc_url( get_permalink() ) . '">' . get_the_post_thumbnail( $post->ID, 'full', array( 'title' => '' ) ) . '</a>';
										}
										?>
										<h2 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> <span><?php echo esc_html__( 'Page', 'salient' ); ?></span></h2>	
										
										<?php
										if ( has_excerpt() ) {
											the_excerpt();}
										?>
									</div>
								</article><!--/search-result-->	
								
								<?php
							} elseif ( get_post_type( $post->ID ) === 'portfolio' ) {
								?>
								<article class="result" data-post-thumb="<?php echo esc_attr( $using_post_thumb ); ?>">
									<div class="inner-wrap">
										<?php

										$nectar_custom_project_link   = get_post_meta( $post->ID, '_nectar_external_project_url', true );
										$nectar_portfolio_project_url = ( ! empty( $nectar_custom_project_link ) ) ? $nectar_custom_project_link : esc_url( get_permalink() );

										if ( has_post_thumbnail( $post->ID ) ) {
											echo '<a href="' . esc_url( $nectar_portfolio_project_url ) . '">' . get_the_post_thumbnail( $post->ID, 'full', array( 'title' => '' ) ) . '</a>';
										}
										?>
										<h2 class="title"><a href="<?php echo esc_url( $nectar_portfolio_project_url ); ?>"><?php the_title(); ?></a> <span><?php echo esc_html__( 'Portfolio Item', 'salient' ); ?></span></h2>
									</div>
								</article><!--/search-result-->		
								
								<?php
							} elseif ( get_post_type( $post->ID ) === 'product' ) {
								?>
								<article class="result" data-post-thumb="<?php echo esc_attr( $using_post_thumb ); ?>">
									<div class="inner-wrap">
										<?php
										if ( has_post_thumbnail( $post->ID ) ) {
											echo '<a href="' . esc_url( get_permalink() ) . '">' . get_the_post_thumbnail( $post->ID, 'full', array( 'title' => '' ) ) . '</a>';
										}
										?>
										<h2 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> <span><?php echo esc_html__( 'Product', 'salient' ); ?></span></h2>	
										<?php
										if ( $search_results_layout === 'list-no-sidebar' ) {
											the_excerpt(); }
										?>
									</div>
								</article><!--/search-result-->	
								
							<?php } else { ?>
								<article class="result" data-post-thumb="<?php echo esc_attr( $using_post_thumb ); ?>">
									<div class="inner-wrap">
										<?php
										if ( has_post_thumbnail( $post->ID ) ) {
											echo '<a href="' . esc_url( get_permalink() ) . '">' . get_the_post_thumbnail( $post->ID, 'full', array( 'title' => '' ) ) . '</a>';
										}
										?>
										<h2 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
										<?php
										if ( $search_results_layout === 'list-no-sidebar' ) {
											the_excerpt();
										}
										?>
									</div>
								</article><!--/search-result-->	
								
							<?php }

						endwhile;
						// /Posts

						// Terms
						if ( $have_terms ) :
							foreach ( $terms as $term ) :
								$t_id             = $term->term_id;
								$term_name        = $term->name;
								$term_desc        = $term->description;
								$term_meta        = get_option( "taxonomy_$t_id" );
								$term_thumb_url   = $term_meta['category_thumbnail_image'];
								$term_thumb_id    = attachment_url_to_postid( $term_thumb_url );
								$term_thumb_img   = $term_thumb_id ?
									wp_get_attachment_image( $term_thumb_id, 'full', array( 'title' => '' ) ) :
									null;
								$using_term_thumb = $term_thumb_img ?
									1 :
									null;
								$term_link        = get_term_link( $term );
								?>
								<article class="result" data-post-thumb="<?php echo esc_attr( $using_term_thumb ); ?>">
									<div class="inner-wrap">
										<?php
										if ( $term_thumb_img ) {
											echo '<a href="' . esc_url( $term_link ) . '">' . $term_thumb_img . '</a>';
										}
										?>
										<h2 class="title">
											<a href="<?php echo esc_url( $term_link ); ?>"><?php echo esc_attr( $term_name ); ?></a>
											<span><?php echo esc_html__( 'Category', 'salient' ); ?></span>
										</h2>
										
										<?php
										if (
											$search_results_layout === 'list-no-sidebar' &&
											! empty($term_desc )
										) :
											$excerpt_length   = (int) _x( '55', 'excerpt_length' );
											$excerpt_length   = (int) apply_filters( 'excerpt_length', $excerpt_length );
											$excerpt_more     = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
											$term_excerpt     = wp_trim_words( $term_desc, $excerpt_length, $excerpt_more );
											echo wp_kses_post( $term_excerpt );
										endif;
										?>
									</div>
								</article><!--/search-result-->	
								<?php
							endforeach; // endforeach ( $terms as $term ) :
							// /Terms
						endif; // endif ( $have_terms ) :
					else : // no posts or terms

						echo '<h3>' . esc_html__( 'Sorry, no results were found.', 'salient' ) . '</h3>';
						echo '<p>' . esc_html__( 'Please try again with different keywords.', 'salient' ) . '</p>';
						get_search_form();

				  endif;

					?>

				</div><!--/search-results-->
				
				<?php if ( get_next_posts_link() || get_previous_posts_link() ) { ?>
					<div id="pagination" data-layout="<?php echo esc_attr( $search_results_layout ); ?>">
						<div class="prev"><?php previous_posts_link( '&laquo; Previous Entries' ); ?></div>
						<div class="next"><?php next_posts_link( 'Next Entries &raquo;', '' ); ?></div>
					</div>	
				<?php } ?>
				
			</div><!--/span_9-->
			
			<?php if ( $search_results_layout === 'default' ) { ?>
				
				<div id="sidebar" class="col span_3 col_last">
					<?php get_sidebar(); ?>
				</div><!--/span_3-->
				
			<?php } ?>
		
		</div><!--/row-->
		
	</div><!--/container-->

</div><!--/container-wrap-->

<?php get_footer(); ?>