<?php
/**
 * Single Designer template: public profile header + all of this designer's designs.
 * Opened from "Designers to follow" (Home) and from the Community.
 * WordPress uses this file automatically for posts of type "designer".
 */
get_header();

while ( have_posts() ) :
	the_post();

	$name      = get_the_title();
	$role      = get_field( 'role' );
	$followers = (int) get_field( 'followers' );
	?>

	<main class="eelaf-designer-page">
		<a class="eelaf-back" href="javascript:history.back()">&larr; Back</a>

		<section class="eelaf-designer-page__profile">
			<div class="eelaf-designer-page__avatar">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'thumbnail' ); ?>
				<?php else : ?>
					<?php echo esc_html( mb_substr( $name, 0, 1 ) ); ?>
				<?php endif; ?>
			</div>

			<div class="eelaf-designer-page__details">
				<h1 class="eelaf-designer-page__name"><?php echo esc_html( $name ); ?></h1>
				<?php if ( $role ) : ?>
					<span class="eelaf-badge"><?php echo esc_html( $role ); ?></span>
				<?php endif; ?>
				<p class="eelaf-designer-page__followers">
					<strong><?php echo esc_html( eelaf_format_count( $followers ) ); ?></strong> followers
				</p>
				<div class="eelaf-designer-page__bio"><?php the_content(); ?></div>
			</div>
		</section>

		<h2 class="eelaf-designer-page__heading">Designs by <?php echo esc_html( $name ); ?></h2>
		<?php echo do_shortcode( '[eelaf_designs designer="' . get_the_ID() . '" limit="12"]' ); ?>
	</main>

	<?php
endwhile;

get_footer();
