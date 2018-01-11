<?php get_template_part('templates/post_single/content-meta'); ?>
<div class="post-content">
	<?php if (ct_get_option("posts_single_show_image", 1)): ?>
		<?php get_template_part('templates/post_single/content-featured-image'); ?>
	<?php endif; ?>

	<?php if (ct_get_option("posts_single_show_title", 1)): ?>
		<h2 class="post-title">
	        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
	    </h2>
	<?php endif;?>

    <div class="post-abstract">
        <?php if (ct_get_option("posts_single_show_content", 1)): ?>
            <p><?php the_content();?></p>
	        <?php wp_link_pages(array('before' => '<nav class="pager">', 'after' => '</nav>')); ?>
        <?php endif;?>
    </div>

</div>