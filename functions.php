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