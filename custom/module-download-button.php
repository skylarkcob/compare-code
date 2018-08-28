<?php
/*
 * Old download url getting.
 */

/*
$download_url = get_post_meta( get_the_ID(), 'download_url', true );

if ( empty( $download_url ) || ( is_array( $download_url ) && ( ! isset( $download_url['url'] ) || empty( $download_url['url'] ) ) ) ) {
	$download = get_post_meta( get_the_ID(), 'download', true );

	if ( is_numeric( $download ) ) {
		$download_url = wp_get_attachment_url( $download );
	}
} else {
	if ( is_array( $download_url ) ) {
		$download_url = Pixelify()->sanitize_media_value( $download_url );
		$download_url = isset( $download_url['url'] ) ? $download_url['url'] : '';
	}
}
*/

$atts = 'data-toggle="modal" data-target="#edd-free-downloads-modal"';

$download_url = Pixelify()->get_download_url();

if ( empty( $download_url ) ) {
	return;
}
?>
<div class="sidebar-download-button">
	<form id="edd_purchase_971120" class="edd_download_purchase_form">
		<div class="edd_free_downloads_form_class">
			<a class="button blue edd-submit edd-submit edd-free-download edd-free-download-single"
			   href="<?php echo Pixelify()->get_virtual_download_url(); ?>" data-download-id="<?php the_ID(); ?>"
			   target="_blank">
				<span><?php _e( 'Download for Free', 'pixelify' ); ?></span>
				<span><?php _e( 'You must credit the author', 'pixelify' ); ?></span>
			</a>
		</div>
	</form>
	<?php
	if ( is_user_logged_in() ) {
		$user = wp_get_current_user();
		?>
		<!-- Modal -->
		<div id="edd-free-downloads-modal" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><?php _e( 'Download', 'pixelify' ); ?></h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-sm-6 col-xs-12">
								<div class="modal-left">
									<form id="edd_free_download_form" method="post">
										<p>
											<label for="edd_free_download_email" class="edd-free-downloads-label">
												<?php _e( 'Email Address', 'pixelify' ); ?>
												<span class="edd-free-downloads-required">*</span>
											</label>
											<input type="text" name="edd_free_download_email"
											       id="edd_free_download_email"
											       class="edd-free-download-field"
											       placeholder="<?php _e( 'Email Address', 'pixelify' ); ?>"
											       value="<?php echo $user->user_email; ?>" tabindex="-1">
										</p>
										<button data-id="<?php the_ID(); ?>" type="button"
										        name="edd_free_download_submit"
										        class="edd-free-download-submit edd-submit button blue"
										        data-waiting="<?php _e( 'Please wait...', 'pixelify' ); ?>">
											<span><?php _e( 'Download Now', 'pixelify' ); ?></span>
										</button>
									</form>
									<p class="sub-title"><?php printf( __( 'Don\'t forget to credit <a href="%s">%s</a>' ), get_author_posts_url( get_the_author_meta( 'ID' ) ), get_the_author() ) ?></p>

									<div class="share-links">
										<p><?php _e( 'Credit the author by sharing', 'pixelify' ); ?></p>
										<?php dynamic_sidebar( 'addthis' ); ?>
									</div>
									<!--/share-links-->
									<p class="link-back"><?php _e( 'or link back to this site', 'pixelify' ); ?></p>
									<label>
										<input type="text" class="link-to-product"
										       value="<?php the_permalink(); ?>"
										       onclick="this.select();">
									</label>
									<?php
									$credit = Pixelify()->get_credit_page();

									if ( $credit instanceof WP_Post ) {
										?>
										<a href="<?php echo get_permalink( $credit ); ?>"
										   class="credit-link"><?php _e( 'Why I have to credit?', 'pixelify' ); ?></a>
										<?php
									}
									?>
								</div>
								<!--/modal-left-->
							</div>
							<?php
							$args = array(
								'posts_per_page' => 4,
								'post_status'    => 'publish',
								'post__not_in'   => array( get_the_ID() ),
								'author'         => get_the_author_meta( 'ID' ),
								'orderby'        => 'rand',
								'post_type'      => 'post'
							);

							$query = new WP_Query( $args );

							if ( ! $query->have_posts() ) {
								unset( $args['author'] );

								$query = new WP_Query( $args );
							}

							if ( $query->have_posts() ) {
								?>
								<div class="modal-right col-sm-6 col-xs-12">
									<div class="modal-related-items">
										<h2><?php _e( 'You might also like', 'pixelify' ); ?></h2>

										<div class="row">
											<?php
											while ( $query->have_posts() ) {
												$query->the_post();
												include Pixelify()->get_basedir() . '/custom/loop-pixelify-small.php';
											}

											wp_reset_postdata();
											?>
										</div>
									</div>
									<!--/modal-related-items-->
								</div>
								<!--/modal-right-->
								<?php
							}
							?>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default"
						        data-dismiss="modal"><?php _e( 'Close', 'pixelify' ); ?></button>
					</div>
				</div>

			</div>
		</div>
		<?php
	}
	?>
</div>