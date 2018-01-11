<?php
register_nav_menus(array(
	'primary_navigation' => __('Primary Navigation', 'ct_theme'),
));
register_nav_menus(array(
	'breadcrumbs' => __('Breadcrumbs Navigation', 'ct_theme'),
));

// Register widgetized areas
register_sidebar(array(
	'name' => __('Primary Sidebar', 'ct_theme'),
	'id' => 'sidebar-primary',
	'before_widget' => '<section id="%1$s" class="widget %2$s"><div class="widget-inner">',
	'after_widget' => '</div></section>',
	'before_title' => '<h3>',
	'after_title' => '</h3>',
));

