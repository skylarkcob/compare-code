<?php
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}
if ( post_password_required() ) {
	return;
}
$post_id = get_the_ID();
$cpost   = get_post( $post_id );
if ( ! is_a( $cpost, 'WP_Post' ) ) {
	return;
}
$comments_title = apply_filters( 'hocwp_comments_title_text', __( 'Leave your comment', 'hocwp-theme' ) );
?>
<div id="comments" class="comments-area">
	<h3 class="comments-title">
		<span class="title-left"><?php echo $comments_title; ?></span>
		<?php if ( have_comments() ) : ?>
			<span class="count">
                <?php
                $comments_number = get_comments_number();
                $comments_count  = apply_filters( 'hocwp_comments_title_count', sprintf( _nx( '1 comment', '%d comments', $comments_number, 'comments title', 'hocwp-theme' ), number_format_i18n( $comments_number ) ), $comments_number );
                echo $comments_count;
                ?>
            </span>
		<?php endif; ?>
	</h3>
	<?php
	do_action( 'hocwp_comment_form_before' );
	if ( have_comments() ) {
		do_action( 'hocwp_comment_list_before' );
		hocwp_comment_nav();
		$avatar_size = apply_filters( 'hocwp_comment_avatar_size', 64 );
		$classes     = apply_filters( 'hocwp_comment_list_class', array() );
		$classes[]   = 'comment-list';
		$classes[]   = 'list-comments';
		hocwp_sanitize_array( $classes );
		echo '<ol class="' . implode( ' ', $classes ) . '">';
		wp_list_comments( array( 'avatar_size' => $avatar_size ) );
		echo '</ol>';
		hocwp_comment_nav();
		do_action( 'hocwp_comment_list_after' );
	}
	if ( ! comments_open( $post_id ) && get_comments_number( $post_id ) && post_type_supports( get_post_type( $cpost ), 'comments' ) ) {
		$no_comment_text = apply_filters( 'hocwp_comments_closed_text', __( 'Comments are closed.', 'hocwp-theme' ) );
		?>
		<p class="no-comments"><?php echo $no_comment_text; ?></p>
		<?php
	}
	comment_form();
	do_action( 'hocwp_comment_form_after' );
	?>
</div><!-- .comments-area -->
