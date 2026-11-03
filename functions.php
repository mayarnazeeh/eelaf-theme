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
		'mine'          => 'no',   // yes = only the logged-in user's designs
		'show_designer' => 'yes',  // no = hide the designer name (personal gallery)
		'designer'      => '',     // a Designer post ID: only this designer's designs
	), $atts, 'eelaf_designs' );

	$query_args = array(
		'post_type'      => 'design',
		'posts_per_page' => (int) $atts['limit'],
		'no_found_rows'  => true,
	);

	// Personal gallery: only designs created by the current user
	if ( 'yes' === $atts['mine'] ) {
		if ( ! is_user_logged_in() ) {
			return '';
		}
		$query_args['author'] = get_current_user_id();
	}

	// Designer profile: only designs linked to this designer (ACF "designer" field)
	if ( $atts['designer'] ) {
		$query_args['meta_query'] = array(
			array(
				'key'   => 'designer',
				'value' => (int) $atts['designer'],
			),
		);
	}

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
		$message = 'yes' === $atts['mine']
			? 'Nothing saved here yet. Start creating and save your work!'
			: 'No designs yet.';
		return '<p class="eelaf-empty">' . esc_html( $message ) . '</p>';
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
					<?php
					if ( 'yes' === $atts['show_designer'] ) {
						echo $designer ? esc_html( get_the_title( $designer ) ) : '';
					} else {
						echo esc_html( get_the_date() );   // personal gallery: show the save date instead
					}
					?>
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
/**
 * [eelaf_feed limit="3"]
 * Latest blog posts as small horizontal cards:
 * thumbnail, designer name, post title, likes and comments.
 */
add_shortcode( 'eelaf_feed', function ( $atts ) {
	$atts = shortcode_atts( array( 'limit' => 3 ), $atts, 'eelaf_feed' );

	$posts = new WP_Query( array(
		'post_type'           => 'post',
		'posts_per_page'      => (int) $atts['limit'],
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	) );

	if ( ! $posts->have_posts() ) {
		return '<p class="eelaf-empty">No posts yet.</p>';
	}

	ob_start();
	echo '<div class="eelaf-feed">';

	while ( $posts->have_posts() ) {
		$posts->the_post();
		$designer = get_field( 'designer' );
		$name     = $designer ? get_the_title( $designer ) : get_the_author();  // fallback: WP author
		$likes    = (int) get_field( 'likes' );
		$comments = (int) get_comments_number();
		?>
		<a class="eelaf-feed-card" href="<?php the_permalink(); ?>">
			<span class="eelaf-feed-card__thumb">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'thumbnail' ); ?>
				<?php endif; ?>
			</span>
			<span class="eelaf-feed-card__body">
				<span class="eelaf-feed-card__name"><?php echo esc_html( $name ); ?></span>
				<span class="eelaf-feed-card__title">Published &ldquo;<?php echo esc_html( get_the_title() ); ?>&rdquo;</span>
				<span class="eelaf-feed-card__stats">
					<span>&#9825; <?php echo esc_html( eelaf_format_count( $likes ) ); ?></span>
					<span>&#128172; <?php echo esc_html( $comments ); ?></span>
				</span>
			</span>
		</a>
		<?php
	}

	echo '</div>';
	wp_reset_postdata();

	return ob_get_clean();
} );

/**
 * [eelaf_gallery limit="8"]
 * Personal gallery: the logged-in user's own saved work,
 * in tabs Matching / Sketch / AI Studio.
 */
add_shortcode( 'eelaf_gallery', function ( $atts ) {
	$atts = shortcode_atts( array( 'limit' => 8 ), $atts, 'eelaf_gallery' );

	// Personal gallery needs a logged-in user
	if ( ! is_user_logged_in() ) {
		return '<div class="eelaf-gallery eelaf-gallery--guest">'
			. '<p>Sign in to see your saved sketches, AI designs and outfits.</p>'
			. '<a class="eelaf-gallery__signin" href="' . esc_url( wp_login_url( get_permalink() ) ) . '">Sign in</a>'
			. '</div>';
	}

	$tabs = array(
		'matching' => 'Matching',
		'sketch'   => 'Sketch',
		'ai-model' => 'AI Studio',
	);

	ob_start();
	?>
	<div class="eelaf-gallery">
		<div class="eelaf-gallery__tabs" role="tablist">
			<?php $first = true; foreach ( $tabs as $slug => $label ) : ?>
				<button type="button" role="tab"
					class="eelaf-gallery__tab<?php echo $first ? ' is-active' : ''; ?>"
					data-tab="<?php echo esc_attr( $slug ); ?>"
					aria-selected="<?php echo $first ? 'true' : 'false'; ?>">
					<?php echo esc_html( $label ); ?>
				</button>
			<?php $first = false; endforeach; ?>
		</div>

		<?php $first = true; foreach ( $tabs as $slug => $label ) : ?>
			<div class="eelaf-gallery__panel<?php echo $first ? ' is-active' : ''; ?>"
				data-panel="<?php echo esc_attr( $slug ); ?>" role="tabpanel">
				<?php echo do_shortcode( '[eelaf_designs mine="yes" show_designer="no" type="' . esc_attr( $slug ) . '" limit="' . (int) $atts['limit'] . '"]' ); ?>
			</div>
		<?php $first = false; endforeach; ?>
	</div>

	<script>
	document.querySelectorAll('.eelaf-gallery').forEach(function (g) {
		g.querySelectorAll('.eelaf-gallery__tab').forEach(function (btn) {
			btn.addEventListener('click', function () {
				g.querySelectorAll('.eelaf-gallery__tab').forEach(function (b) {
					b.classList.remove('is-active');
					b.setAttribute('aria-selected', 'false');
				});
				g.querySelectorAll('.eelaf-gallery__panel').forEach(function (p) {
					p.classList.toggle('is-active', p.dataset.panel === btn.dataset.tab);
				});
				btn.classList.add('is-active');
				btn.setAttribute('aria-selected', 'true');
			});
		});
	});
	</script>
	<?php
	return ob_get_clean();
} );