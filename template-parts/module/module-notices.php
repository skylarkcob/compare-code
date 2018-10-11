<?php
$notice = hocwp_theme_get_option( 'notices' );
if ( ! empty( $notice ) ) {
	?>
	<div class="module gray-box notices">
		<div class="module-body">
			<?php echo wpautop( $notice ); ?>
		</div>
	</div>
	<?php hocwp_show_ads( 'below_notices' ); ?>
	<?php
}