<?php
$option = hocwp_theme_get_home_setting('category_home');
$option = hocwp_json_string_to_array($option);
$count_item = count($option);
$taxonomy = "category";
$post_type = "";
$posts_per_page = hocwp_theme_get_home_setting('posts_per_page');
if($count_item > 0) : ?>
<div class="category-home">
    <?php foreach($option as $term_item) :
        $term_id = $term_item['id'];
        if(is_numeric($term_id)) :
            $term = get_term($term_id, $taxonomy); ?>
            <div class="category-item hoverflow">
                <h3 class="box-title"><a href="<?php echo get_term_link($term, $taxonomy); ?>"><?php echo $term->name; ?></a></h3>
                <?php
                $query = hocwp_query(
						array(
							'posts_per_page' => $posts_per_page,
							'post_type' => $post_type,
							'tax_query' => array(
									array(
										'taxonomy' => $taxonomy,
										'field'    => 'id',
										'terms'    => $term_id
									)
								)
						));
                if($query->have_posts()) :
                ?>
                    <div class="box-list">
                        <?php while($query->have_posts()) : $query->the_post(); ?>
                    
                        <?php wp_reset_postdata(); endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>