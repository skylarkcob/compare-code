<?php
$text  = '';
$per   = 10;
$pers  = array( 10, 20, 50 );
$size  = 80;
$sizes = array(
	'60'  => __( 'Small', 'hocwp-theme' ),
	'80'  => __( 'Medium', 'hocwp-theme' ),
	'100' => __( 'Large', 'hocwp-theme' )
);
$sort  = 'latest';
$sorts = array(
	'name'      => __( 'Font Name', 'hocwp-theme' ),
	'latest'    => __( 'Newest', 'hocwp-theme' ),
	'downloads' => __( 'Most Downloads', 'hocwp-theme' )
);
$color = '#000';
if ( isset( $_POST['submitPreviewSettings'] ) ) {
	if ( 'update' == $_POST['submitPreviewSettings'] ) {
		$text  = $_POST['customPreviewText'];
		$per   = $_POST['perPage'];
		$size  = $_POST['customPreviewSize'];
		$sort  = $_POST['order'];
		$color = $_POST['customPreviewTextColour'];
	}
}
?>
<div class="sortFilterForm">
	<form method="POST" action="" class="custom-preview-form">
		<div class="elementWrapper">
			<label for="customPreviewText">
				Custom Preview:
			</label>
			<input name="customPreviewText" id="customPreviewText" value="<?php echo esc_attr( $text ); ?>"
			       placeholder="<?php _e( 'Type your text here', 'hocwp-theme' ); ?>" type="text" autocomplete="off" maxlength="80">
		</div>
		<div class="elementWrapper">
			<label for="perPage">Fonts:</label>
			<select id="perPage" name="perPage">
				<?php
				foreach ( $pers as $num ) {
					?>
					<option value="<?php echo $num; ?>" <?php selected( $per, $num ); ?>><?php echo $num; ?></option>
					<?php
				}
				?>
			</select>
		</div>

		<div class="elementWrapper">
			<label for="customPreviewSize">
				Size:
			</label>
			<select name="customPreviewSize" id="customPreviewSize">
				<?php
				foreach ( $sizes as $key => $value ) {
					?>
					<option value="<?php echo $key; ?>" <?php selected( $size, $key ); ?>><?php echo $value; ?></option>
					<?php
				}
				?>
			</select>
		</div>
		<div class="elementWrapper">
			<label for="order">Sort By:</label>
			<select id="order" name="order">
				<?php
				foreach ( $sorts as $key => $value ) {
					?>
					<option value="<?php echo $key; ?>" <?php selected( $sort, $key ); ?>><?php echo $value; ?></option>
					<?php
				}
				?>
			</select>
		</div>

		<div class="elementWrapper">
			<label for="customPreviewTextColour">
				Text Colour:
			</label>
			<input name="customPreviewTextColour" id="customPreviewTextColour" value="<?php echo $color; ?>" size="7"
			       type="text"
			       autocomplete="off" data-default-color="#000">
		</div>
		<div class="elementWrapper form-actions" style="vertical-align: -28px">
			<input name="submitPreviewSettings" id="submitPreviewSettings" value="update" type="submit">
			<input name="resetPreviewSettings" onclick="hocwp_theme_reset_preview()" value="clear" type="reset">
			<script>
				function hocwp_theme_reset_preview() {
					window.location.href = window.location.href;
				}
			</script>
		</div>
	</form>
</div>