<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Boilerplate
 * @since Boilerplate 1.0
 */
?><!DOCTYPE html>
<!--[if lt IE 7 ]><html <?php language_attributes(); ?> class="no-js ie ie6 lte7 lte8 lte9"><![endif]-->
<!--[if IE 7 ]><html <?php language_attributes(); ?> class="no-js ie ie7 lte7 lte8 lte9"><![endif]-->
<!--[if IE 8 ]><html <?php language_attributes(); ?> class="no-js ie ie8 lte8 lte9"><![endif]-->
<!--[if IE 9 ]><html <?php language_attributes(); ?> class="no-js ie ie9 lte9"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html <?php language_attributes(); ?> class="no-js"><!--<![endif]-->
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<title><?php
			/*
			 * Print the <title> tag based on what is being viewed.
			 * We filter the output of wp_title() a bit -- see
			 * boilerplate_filter_wp_title() in functions.php.
			 */
			wp_title( '|', true, 'right' );
		?></title>
		<link rel="profile" href="http://gmpg.org/xfn/11" />
		<link rel="stylesheet" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
		<link rel="shortcut icon" href="<?php bloginfo('stylesheet_directory'); ?>/images/favicon.png" />
<?php
		/* We add some JavaScript to pages with the comment form
		 * to support sites with threaded comments (when in use).
		 */
		if ( is_singular() && get_option( 'thread_comments' ) )
			wp_enqueue_script( 'comment-reply' );

		/* Always have wp_head() just before the closing </head>
		 * tag of your theme, or you will break many plugins, which
		 * generally use this hook to add elements to <head> such
		 * as styles, scripts, and meta tags.
		 */
		wp_head();
?>
	</head>
	<body <?php body_class(); ?>>
    	<div id="header-container">
          <div id="header" role="banner" class="site-width">
              <h1>
              	<a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
              </h1>
              <p><?php bloginfo( 'description' ); ?></p>
          </div><!--end header-->
        </div><!--end header-container-->
        
        <div id="navigation-container">
		<div id="navigation" id="access" role="navigation" class="site-width">
			<?php wp_nav_menu( array( 'container_class' => 'menu-header', 'theme_location' => 'primary' ) ); ?>
		</div><!-- end navigation -->
        </div><!-- end navigation container-->
        
        <div id="featured-container">
        	<div id="featured" class="site-width">
            	<?php 
                  if(is_front_page()){
                    if ( function_exists( 'meteor_slideshow' ) ) { meteor_slideshow(); }
                  }elseif (has_post_thumbnail( $post->ID )) {
                      echo get_the_post_thumbnail( $post->ID, 'post-thumbnail' );
                  }else {
                      echo '<img src="' . (get_bloginfo('template_url')) . '/images/featured-home.jpg" />';
                  }
			  	?>
            </div><!--end featured-->
        </div><!--end featured-container-->
        
		<div id="content-container">
  			<div id="content" class="site-width">