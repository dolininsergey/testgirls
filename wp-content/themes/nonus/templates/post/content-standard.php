<?php get_template_part('templates/post/content-meta'); ?>
<div class="post-content">
	<?php if (ct_get_option("posts_index_show_image", 1)): ?>
		<?php get_template_part('templates/post/content-featured-image'); ?>
	<?php endif; ?>

	<?php if (ct_get_option("posts_index_show_title", 1)): ?>
		<h2 class="post-title">
	        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
	    </h2>
	<?php endif;?>

    <div class="post-abstract">
	    <?php if (ct_get_option("posts_index_show_excerpt", 1)): ?>
            <?php the_excerpt();?>
        <?php endif;?>
        <?php if (ct_get_option("posts_index_show_fulltext", 0)): ?>
            <p><?php the_content();?></p>
        <?php endif;?>
    </div>

	<?php if (ct_get_option("posts_index_show_more", 1)): ?>
		<a href="<?php the_permalink()?>" class="btn post-more"><?php _e('Read More', 'ct_theme')?></a>
	<?php endif;?>

</div>