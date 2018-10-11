<article id="post-<?php the_ID(); ?>" <?php post_class("loop-single clearfix"); ?>>
    <header class="entry-header">
        <h1 class="entry-title"><?php the_title(); ?></h1>
        <div class="post-meta" style="display: none">
            <span class="meta-date date updated" style="display: inline-block"><i class="fa fa-calendar"></i><?php echo get_the_date(); ?></span>
            <span class="meta-author vcard author"><span class="fn"><i class="fa fa-user"></i><?php the_author_posts_link(); ?></span></span>
        </div>
    </header>

    <div class="entry-content">
        <?php the_content(); ?>
    </div>
    <footer class="entry-footer">
        <?php if('post' == get_post_type(get_the_ID())) : ?>
            <?php $cats = wp_get_post_categories(get_the_ID()); ?>
            <?php $myqr = new WP_Query(array('category__in' => $cats, 'post_type' => 'post', 'posts_per_page' => 5, 'post__not_in' => array(get_the_ID()))); ?>
            <?php if( $myqr->have_posts() ) : ?>
                <div class="related-post">
                    <h4 class="title-related">Bài viết liên quan</h4>
                    <ul class="box-list-related">
                        <?php while( $myqr->have_posts() ) : $myqr->the_post(); ?>
                            <li class="post-related-item">
                                <a class="title" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
                            </li>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </footer>
</article>