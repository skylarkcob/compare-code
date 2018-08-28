<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$post_id = get_the_ID();

$post_slider = get_post_meta( $post_id, 'post_slider', true );

$slider_ids = get_post_meta( $post_id, 'slider_ids', true );

if ( ! empty( $post_slider ) || HP()->array_has_value( $slider_ids ) ) {
	if ( HP()->array_has_value( $slider_ids ) ) {
		$count = count( $slider_ids );
		?>
		<div class="pxf-post-slider clearfix">
			<div class="slider-for">
				<?php
				$images = '';

				foreach ( $slider_ids as $att_id ) {
					$images .= wp_get_attachment_image( $att_id, 'auto-slider', true );
				}

				echo $images;
				?>
			</div>
			<?php
			if ( 1 < $count ) {
				?>
				<div class="slider-nav">
					<?php echo $images; ?>
				</div>
				<?php
			}
			?>
		</div>
		<?php
	} else {
		preg_match_all( "/<img>/", $post_slider, $m );
		$count = substr_count( $post_slider, '<img' );

		if ( HP()->is_positive_number( $count ) ) {
			?>
			<div class="pxf-post-slider clearfix">
				<div class="slider-for">
					<?php echo $post_slider; ?>
				</div>
				<?php
				if ( 1 < $count ) {
					?>
					<div class="slider-nav">
						<?php echo $post_slider; ?>
					</div>
					<?php
				}
				?>
			</div>
			<?php
		}
	}
}