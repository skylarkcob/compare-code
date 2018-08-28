<div class="modal fade" id="edd-wl-modal" tabindex="-1" role="dialog"
     aria-labelledby="edd-wl-modal-label" aria-hidden="true" style="display: none;">
	<div class="modal-dialog text-left">
		<div class="modal-content">
			<div class="modal-header">
				<h2 id="edd-wl-modal-label"><?php _e( 'Collection', 'pixelify' ); ?></h2>

				<p><?php the_title(); ?></p>
				<a class="edd-wl-close edd-wl-button" href="#" data-dismiss="modal">
					<i class="glyphicon glyphicon-remove"></i>
					<span class="hide-text"><?php _e( 'Close', 'pixelify' ); ?></span>
				</a>
			</div>
			<div class="modal-body">
				<div class="messages"></div>
				<form method="post" action="" class="form-modal">
					<?php
					$user_id = get_current_user_id();

					$args = array(
						'post_type'      => 'collection',
						'posts_per_page' => - 1,
						'author'         => $user_id,
						'post_status'    => 'private',
						'orderby'        => 'name',
						'order'          => 'asc'
					);

					$private = new WP_Query( $args );

					$args['post_status'] = 'publish';

					$public = new WP_Query( $args );

					$has_post = false;

					if ( $private->have_posts() || $public->have_posts() ) {
						$has_post = true;
						?>
						<p id="current_lists">
							<input type="radio" checked="" id="existing-list" value="existing-list"
							       name="list-options">
							<label
								for="existing-list"><?php _e( 'Add to existing', 'pixelify' ); ?></label>
							<select id="user-lists" name="user-lists">
								<?php
								if ( $public->have_posts() ) {
									?>
									<optgroup label="<?php _e( 'Public', 'pixelify' ); ?>">
										<?php
										foreach ( $public->posts as $obj ) {
											$childs = get_post_meta( $obj->ID, 'childs', true );

											$childs = (array) $childs;
											$childs = array_filter( $childs );
											$childs = array_unique( $childs );

											$count = count( $childs );
											?>
											<option
												value="<?php echo $obj->ID; ?>"><?php printf( '%s (%d)', $obj->post_title, $count ) ?></option>
											<?php
										}
										?>
									</optgroup>
									<?php
								}

								if ( $private->have_posts() ) {
									?>
									<optgroup label="<?php _e( 'Private', 'pixelify' ); ?>">
										<?php
										foreach ( $private->posts as $obj ) {
											$childs = get_post_meta( $obj->ID, 'childs', true );

											$childs = (array) $childs;
											$childs = array_filter( $childs );
											$childs = array_unique( $childs );

											$count = count( $childs );
											?>
											<option
												value="<?php echo $obj->ID; ?>"><?php printf( '%s (%d)', $obj->post_title, $count ) ?></option>
											<?php
										}
										?>
									</optgroup>
									<?php
								}
								?>
							</select>
						</p>
						<?php
					}
					?>
					<p>
						<input type="radio" id="new-list" value="new-list"
						       name="list-options"<?php checked( false, $has_post ); ?>>
						<label for="new-list"><?php _e( 'Add to new', 'pixelify' ); ?></label>
						<?php
						$style = 'display:none';

						if ( ! $has_post ) {
							$style = '';
						}
						?>
						<input type="text" id="list-name" name="list-name" placeholder="Title"
						       style="<?php echo $style; ?>">
						<label>
							<select id="list-status" name="list-status" style="<?php echo $style; ?>">
								<option
									value="private"><?php _e( 'Private - only viewable by you', 'pixelify' ); ?></option>
								<option
									value="publish"><?php _e( 'Public - viewable by anyone', 'pixelify' ); ?></option>
							</select>
						</label>
					</p>
				</form>
			</div>
			<div class="modal-footer">
				<a href="#" class="edd-wl-button edd-wl-save edd-wl-action glyph-left"
				   data-id="<?php the_ID(); ?>">
					<span class="label"><?php _e( 'Save', 'pixelify' ); ?></span>
									<span class="edd-loading">
										<i class="edd-icon-spinner edd-icon-spin"></i>
									</span>
				</a>
				<a class="edd-wl-button edd-wl-success edd-wl-action" href="#" data-dismiss="modal"
				   style="display:none;"><?php _e( 'Great, I\'m done', 'pixelify' ); ?></a>
			</div>
		</div>
	</div>
</div>