<?php
   // Load the child theme stylesheet after Hello Elementor's
   add_action( 'wp_enqueue_scripts', function () {
   	wp_enqueue_style(
   		'eelaf-child',
   		get_stylesheet_uri(),
   		array( 'hello-elementor' ),
   		wp_get_theme()->get( 'Version' )
   	);
   } );
   /**
 * Format follower counts: 12400 -> 12.4k
 */
function eelaf_format_count( $n ) {
	return $n >= 1000 ? round( $n / 1000, 1 ) . 'k' : (string) $n;
}

/**
 * [eelaf_designers limit="4"]
 * Designer cards: avatar, name, role · followers.
 */
add_shortcode( 'eelaf_designers', function ( $atts ) {
	$atts = shortcode_atts( array( 'limit' => 4 ), $atts, 'eelaf_designers' );

	$designers = new WP_Query( array(
		'post_type'      => 'designer',
		'posts_per_page' => (int) $atts['limit'],
		'orderby'        => 'date',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	) );

	if ( ! $designers->have_posts() ) {
		return '<p class="eelaf-empty">No designers yet.</p>';
	}

	$avatar_colors = array( '#7FB8C4', '#A0F2ED', '#F2A6E6', '#F2C29B' );
	$i = 0;

	ob_start();
	echo '<div class="eelaf-designers">';

	while ( $designers->have_posts() ) {
		$designers->the_post();
		$name      = get_the_title();
		$role      = get_field( 'role' );
		$followers = (int) get_field( 'followers' );
		$color     = $avatar_colors[ $i % count( $avatar_colors ) ];
		$i++;
		?>
		<a class="eelaf-designer-card" href="<?php the_permalink(); ?>">
			<span class="eelaf-designer-card__avatar" style="background: <?php echo esc_attr( $color ); ?>">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'thumbnail' ); ?>
				<?php else : ?>
					<?php echo esc_html( mb_substr( $name, 0, 1 ) ); ?>
				<?php endif; ?>
			</span>
			<span class="eelaf-designer-card__name"><?php echo esc_html( $name ); ?></span>
			<span class="eelaf-designer-card__meta">
				<?php echo esc_html( $role ); ?> · <?php echo esc_html( eelaf_format_count( $followers ) ); ?> followers
			</span>
		</a>
		<?php
	}

	echo '</div>';
	wp_reset_postdata();

	return ob_get_clean();
} );
/**
 * [eelaf_designs type="sketch" limit="4" show_title="yes"]
 * Design cards: image, title, designer name and likes.
 * type = a design_type slug: sketch, ai-model or matching (empty = all).
 */
add_shortcode( 'eelaf_designs', function ( $atts ) {
	$atts = shortcode_atts( array(
		'type'       => '',
		'limit'      => 4,
		'show_title' => 'yes',
	), $atts, 'eelaf_designs' );

	$query_args = array(
		'post_type'      => 'design',
		'posts_per_page' => (int) $atts['limit'],
		'no_found_rows'  => true,
	);

	// Filter by design type (taxonomy) when one is given
	if ( $atts['type'] ) {
		$query_args['tax_query'] = array(
			array(
				'taxonomy' => 'design_type',
				'field'    => 'slug',
				'terms'    => sanitize_title( $atts['type'] ),
			),
		);
	}

	$designs = new WP_Query( $query_args );

	if ( ! $designs->have_posts() ) {
		return '<p class="eelaf-empty">No designs yet.</p>';
	}

	ob_start();
	echo '<div class="eelaf-designs">';

	while ( $designs->have_posts() ) {
		$designs->the_post();
		$designer = get_field( 'designer' );        // linked Designer post (or null)
		$likes    = (int) get_field( 'likes' );
		?>
		<a class="eelaf-design-card" href="<?php the_permalink(); ?>">
			<span class="eelaf-design-card__image">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'medium_large' ); ?>
				<?php endif; ?>
			</span>

			<?php if ( 'yes' === $atts['show_title'] ) : ?>
				<span class="eelaf-design-card__title"><?php echo esc_html( get_the_title() ); ?></span>
			<?php endif; ?>

			<span class="eelaf-design-card__meta">
				<span class="eelaf-design-card__designer">
					<?php echo $designer ? esc_html( get_the_title( $designer ) ) : ''; ?>
				</span>
				<?php if ( $likes ) : ?>
					<span class="eelaf-design-card__likes">&#9825; <?php echo esc_html( eelaf_format_count( $likes ) ); ?></span>
				<?php endif; ?>
			</span>
		</a>
		<?php
	}

	echo '</div>';
	wp_reset_postdata();

	return ob_get_clean();
} );