<article <?php post_class("loop-page"); ?>>
    <header class="entry-header">
        <h1 class="entry-title" style="display: none"><?php the_title(); ?></h1>
        <div class="post-meta" style="display: none">
            <span class="meta-date date updated"><i class="fa fa-clock-o"></i><?php echo get_the_date('d/m/Y'); ?></span>
            <span class="meta-author vcard author"<span class="fn">By <?php the_author_posts_link(); ?></span></span>
        </div>
    </header>

    <div class="entry-content">
        <?php the_content(); ?>
    </div>
    <footer class="entry-footer">

    </footer>

</article>