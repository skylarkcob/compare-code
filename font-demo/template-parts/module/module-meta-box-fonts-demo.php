<?php
global $post;
$font_demos = hocwp_get_post_meta( 'font_demos', $post->ID );
$key        = 0;
if ( hocwp_array_has_value( $font_demos ) ) {
	$key = count( $font_demos );
}
$key ++;
?>
<div class="list-demos" data-key="<?php echo $key; ?>">
	<?php
	if ( hocwp_array_has_value( $font_demos ) ) {
		foreach ( $font_demos as $key => $data ) {
			$id = $data['id'];
			if ( ! hocwp_id_number_valid( $id ) ) {
				continue;
			}
			$item = '';
			$item .= '<div class="item" style="border-bottom: 1px dotted #eee">';
			$item .= '<div class="meta-row">';
			$item .= '<label class="">Name</label>';
			$item .= '<input class="widefat regular-text demo-name" value="' . $data['name'] . '" name="font_demos[' . $key . '][name]" type="text">';
			$item .= '</div>';
			$item .= '<div class="meta-row">';
			$item .= '<label class="">Download</label>';
			$item .= '<div class="media-container field-group">';
			$item .= '<span class="media-preview"></span>';
			$item .= '<input autocomplete="off" class="media-url widefat regular-text demo-url" value="' . $data['url'] . '" name="font_demos[' . $key . '][url]" type="url" style="margin-right: 10px">';
			$item .= '<button class="button btn-add-media btn btn-insert-media hidden">Add media</button>';
			$item .= '<button class="btn button btn-remove">Remove</button>';
			$item .= '<input class="media-id widefat regular-text demo-id" value="' . $id . '" name="font_demos[' . $key . '][id]" type="hidden">';
			$item .= '</div>';
			$item .= '</div>';
			$item .= '</div>';
			echo $item;
		}
	}
	?>
</div>
<button class="button default add-demo" style="margin-top: 15px;">Add</button>