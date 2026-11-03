<?php
/**
 * Single Design template: big image, title, type, likes, description.
 * - Owner of the design: sees their own actions (Download, Share to Community).
 * - Anyone else: sees the designer card linking to the designer's profile.
 * WordPress uses this file automatically for posts of type "design".
 */
get_header();

while ( have_posts() ) :
	the_post();

	$designer = get_field( 'designer' );                    // linked Designer post
	$likes    = (int) get_field( 'likes' );
	$types    = get_the_terms( get_the_ID(), 'design_type' ); // Sketch / AI Studio / Matching
	$type     = ( $types && ! is_wp_error( $types ) ) ? $types[0] : null;

	// Is the visitor the owner of this design?
	$is_owner = is_user_logged_in() && get_current_user_id() === (int) get_post_field( 'post_author' );
	?>

	<main class="eelaf-single-design">
		<a class="eelaf-back" href="<?php echo esc_url( home_url( '/gallery/' ) ); ?>">&larr; Back to Gallery</a>

		<div class="eelaf-single-design__grid">
			<div class="eelaf-single-design__image">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'large' ); ?>
				<?php endif; ?>
			</div>

			<div class="eelaf-single-design__info">
				<?php if ( $type ) : ?>
					<span class="eelaf-badge"><?php echo esc_html( $type->name ); ?></span>
				<?php endif; ?>

				<h1 class="eelaf-single-design__title"><?php the_title(); ?></h1>

				<?php if ( $is_owner ) : ?>
					<div class="eelaf-owner-actions">
						<?php if ( has_post_thumbnail() ) : ?>
							<a class="eelaf-btn" href="<?php echo esc_url( get_the_post_thumbnail_url( null, 'full' ) ); ?>" download>Download</a>
						<?php endif; ?>
						<a class="eelaf-btn eelaf-btn--outline" href="#" title="Coming soon">Share to Community</a>
					</div>
				<?php elseif ( $designer ) : ?>
					<a class="eelaf-single-design__designer" href="<?php echo esc_url( get_permalink( $designer ) ); ?>">
						<span class="eelaf-single-design__avatar">
							<?php echo esc_html( mb_substr( get_the_title( $designer ), 0, 1 ) ); ?>
						</span>
						<span>
							<strong><?php echo esc_html( get_the_title( $designer ) ); ?></strong>
							<small><?php echo esc_html( get_field( 'role', $designer ) ); ?></small>
						</span>
					</a>
				<?php endif; ?>

				<div class="eelaf-single-design__stats">
					<span>&#9825; <?php echo esc_html( eelaf_format_count( $likes ) ); ?> likes</span>
					<span><?php echo esc_html( get_the_date() ); ?></span>
				</div>

				<div class="eelaf-single-design__content">
					<?php the_content(); ?>
				</div>
			</div>
		</div>
	</main>

	<?php
endwhile;

get_footer();
