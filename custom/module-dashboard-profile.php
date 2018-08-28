<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$user_id = get_current_user_id();
$user    = wp_get_current_user();
?>
<div id="fes-vendor-dashboard" class="fes-vendor-dashboard">
	<?php
	if ( isset( $_POST['submit'] ) ) {
		$first_name   = isset( $_POST['first_name'] ) ? $_POST['first_name'] : '';
		$last_name    = isset( $_POST['last_name'] ) ? $_POST['last_name'] : '';
		$user_email   = isset( $_POST['user_email'] ) ? $_POST['user_email'] : '';
		$description  = isset( $_POST['description'] ) ? $_POST['description'] : '';
		$city_country = isset( $_POST['_pixelify_residence'] ) ? $_POST['_pixelify_residence'] : '';
		$user_pass    = isset( $_POST['user_pass'] ) ? $_POST['user_pass'] : '';
		$pass2        = isset( $_POST['pass2'] ) ? $_POST['pass2'] : '';

		$error = false;

		if ( ! is_email( $user_email ) ) {
			?>
			<p class="alert alert-error alert-danger">
				<?php _e( 'Invalid email address.', 'pixelify' ); ?>
			</p>
			<?php
			$error = true;
		}

		if ( ! empty( $user_pass ) && $pass2 !== $user_pass ) {
			?>
			<p class="alert alert-error alert-danger">
				<?php _e( 'Invalid password or passwords do not match.', 'pixelify' ); ?>
			</p>
			<?php
			$error = true;
		}

		if ( ! $error ) {
			$data = array(
				'ID'         => $user_id,
				'first_name' => $first_name,
				'last_name'  => $last_name,
				'user_email' => $user_email
			);

			if ( ! empty( $user_pass ) ) {
				$data['user_pass'] = $user_pass;
			}

			$updated = wp_update_user( $data );

			if ( $updated ) {
				update_user_meta( $user_id, 'description', $description );

				update_user_meta( $user_id, 'city_country', $city_country );
			}

			if ( $updated ) {
				$user = new WP_User( $user_id );
				?>
				<p class="alert alert-success">
					<?php _e( 'Your profile has been updated successfully.', 'pixelify' ); ?>
				</p>
				<?php
			} else {
				?>
				<p class="alert alert-error alert-danger">
					<?php _e( 'There was an error occurred, please try again.', 'pixelify' ); ?>
				</p>
				<?php
			}
		}
	}
	?>
	<form class="fes-ajax-form fes-profile-form" action="" name="fes-profile-form" method="post"
	      enctype="multipart/form-data">
		<div class="fes-form fes-profile-form-div">
			<fieldset class="fes-form-fieldset fes-form-fieldset-profile">
				<legend class="fes-form-legend"
				        id="fes-profile-form-title"><?php _e( 'Profile', 'pixelify' ); ?></legend>
				<div class="fes-el html html_1519802597">
					<div class="fes-fields">
						<div class="zf-user-avatar-field">
							<div class="change-avatar-form">
								<a href="javascript:" data-user-id="<?php echo $user_id; ?>">
									<?php echo get_avatar( $user_id ); ?>
								</a>
								<input type="file" name="avatar_id" style="display: none" accept=".jpg, .png">
								<script>
									jQuery(document).ready(function ($) {
										(function () {
											$(".change-avatar-form").on("click", "a", function (e) {
												e.preventDefault();
												var element = $(this),
													form = element.parent(),
													input = form.find("input"),
													avatar = form.find("img");

												input.on("change", function () {
													var files = this.files;

													if (files.length) {
														var file = files[0];

														if (file) {
															var _URL = window.URL || window.webkitURL,
																imageWidth = 0,
																imageHeight = 0;

															var img = new Image();

															img.onload = function () {
																imageWidth = this.width;
																imageHeight = this.height;
															};

															img.src = _URL.createObjectURL(file);

															setTimeout(function () {
																if (imageWidth != imageHeight || imageWidth > 128) {
																	alert("Avatar must be a square and image size smaller than 128x128 pixels!");
																	return;
																}

																avatar.attr("data-old-src", avatar.attr("src"));
																avatar.attr("src", pixelify.spinnerUrl);

																var Upload = function (file) {
																	this.file = file;
																};

																Upload.prototype.getType = function () {
																	return this.file.type;
																};

																Upload.prototype.getSize = function () {
																	return this.file.size;
																};

																Upload.prototype.getName = function () {
																	return this.file.name;
																};

																Upload.prototype.doUpload = function () {
																	var that = this;
																	var formData = new FormData();

																	// add assoc key values, this will be posts values
																	formData.append("file", this.file);
																	formData.append("upload_file", true);
																	formData.append("user_id", element.attr("data-user-id"));

																	$.ajax({
																		type: "POST",
																		url: pixelify.ajaxUrl + "?action=hocwp_pxf_upload_avatar",
																		xhr: function () {
																			var myXhr = $.ajaxSettings.xhr();

																			if (myXhr.upload) {
																				myXhr.upload.addEventListener("progress", that.progressHandling, false);
																			}

																			return myXhr;
																		},
																		success: function (response) {
																			if (response.success) {
																				avatar.attr("src", response.data.url);
																			} else {
																				avatar.attr("src", avatar.attr("data-old-src"));
																			}
																		},
																		error: function (error) {
																			// handle error
																		},
																		async: true,
																		data: formData,
																		cache: false,
																		contentType: false,
																		processData: false,
																		timeout: 60000
																	});
																};

																Upload.prototype.progressHandling = function (event) {
																	var percent = 0;
																	var position = event.loaded || event.position;
																	var total = event.total;

																	if (event.lengthComputable) {
																		percent = Math.ceil(position / total * 100);
																	}
																};

																var upload = new Upload(file);

																// maby check size or type here with upload.getSize() and upload.getType()

																// execute upload
																upload.doUpload();
															}, 1000);
														}
													}
												});

												input.trigger("click");
											});
										})();
									});
								</script>
							</div>
						</div>
					</div>
				</div>
				<div class="fes-el first_name first_name">
					<div class="fes-label">
						<label for="first_name"><?php _e( 'First Name', 'pixelify' ); ?></label>
					</div>
					<div class="fes-fields">
						<input class="textfield" id="first_name" type="text" data-required="" data-type="text"
						       name="first_name" placeholder="" value="<?php echo $user->first_name; ?>" size="40">
					</div>
				</div>
				<div class="fes-el last_name last_name">
					<div class="fes-label">
						<label for="last_name"><?php _e( 'Last Name', 'pixelify' ); ?></label>
					</div>
					<div class="fes-fields">
						<input class="textfield" id="last_name" type="text" data-required="" data-type="text"
						       name="last_name" placeholder="" value="<?php echo $user->last_name; ?>" size="40">
					</div>
				</div>
				<div class="fes-el user_email user_email">
					<div class="fes-label">
						<label for="user_email"><?php _e( 'Email', 'pixelify' ); ?></label>
					</div>
					<div class="fes-fields">
						<input id="user_email" type="email" class="email" data-required="" data-type="text"
						       name="user_email" placeholder="" value="<?php echo $user->user_email; ?>" size=""
						       required>
					</div>
				</div>
				<div class="fes-el user_bio description">
					<div class="fes-label">
						<label for="description"><?php _e( 'Bio', 'pixelify' ); ?></label>
					</div>
					<div class="fes-fields">
						<textarea class="textareafield" id="description" name="description" data-required=""
						          data-type="textarea" placeholder="" rows="5"
						          cols="50"><?php echo get_user_meta( $user_id, 'description', true ); ?></textarea>
					</div>
				</div>
				<div class="fes-el text _pixelify_residence">
					<div class="fes-label">
						<label for="_pixelify_residence"><?php _e( 'Residence', 'pixelify' ); ?></label>
						<span class="fes-help"><?php _e( 'City/Country', 'pixelify' ); ?></span>
					</div>
					<div class="fes-fields">
						<input class="textfield" id="_pixelify_residence" type="text" data-required="" data-type="text"
						       name="_pixelify_residence" placeholder=""
						       value="<?php echo get_user_meta( $user_id, 'city_country', true ); ?>" size="">
					</div>
				</div>
				<div class="fes-el password user_pass">
					<div class="fes-label">
						<label for="user_pass"><?php _e( 'New Password', 'pixelify' ); ?></label>
					</div>
					<div class="fes-fields">
						<input id="user_pass" type="password" class="password textfield" data-required=""
						       data-type="text" name="user_pass" value="" autocomplete="off">
					</div>

					<div class="fes-label">
						<label for="fes-pass2"><?php _e( 'Confirm Password', 'pixelify' ); ?></label>
					</div>
					<div class="fes-fields">
						<input id="fes-pass2" type="password" class="password textfield" data-required=""
						       data-type="text" name="pass2" value="" autocomplete="off">
					</div>
					<div class="fes-fields fes-submit">
						<input type="submit" class="edd-submit blue button" name="submit"
						       value="<?php _e( 'Save Changes', 'pixelify' ); ?>"></div>
			</fieldset>
		</div>
	</form>
</div>