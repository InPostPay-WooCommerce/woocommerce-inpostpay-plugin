<?php

remove_action( 'wp_head', 'wp_title' );
get_header();
the_content();
get_footer();


