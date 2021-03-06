<?php
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}

$use = apply_filters( 'hocwp_admin_translation', false );

if ( ! $use && is_admin() ) {
	return;
}

if ( function_exists( 'qtranxf_getLanguage' ) ) {
	$lang = qtranxf_getLanguage();
	if ( 'vi' != $lang ) {
		return;
	}
}

$use = apply_filters( 'hocwp_translate_theme_into_vietnamese', true );

if ( ! $use ) {
	return;
}

global $pagenow;

if ( 'wp-login.php' == $pagenow ) {
	return;
}

function hocwp_theme_translation_comments_title_text() {
	return 'Gửi bình luận của bạn';
}

add_filter( 'hocwp_comments_title_text', 'hocwp_theme_translation_comments_title_text' );

function hocwp_theme_translation_comments_title_count( $text, $comments_number ) {
	if ( $comments_number > 1 ) {
		$text = sprintf( '%d bình luận', number_format_i18n( $comments_number ) );
	} elseif ( 1 == $comments_number ) {
		$text = '1 bình luận';
	} else {
		$text = '0 bình luận';
	}

	return $text;
}

add_filter( 'hocwp_comments_title_count', 'hocwp_theme_translation_comments_title_count', 10, 2 );

function hocwp_theme_translation_comment_form_defaults( $defaults ) {
	$commenter     = wp_get_current_commenter();
	$user          = wp_get_current_user();
	$user_identity = $user->exists() ? $user->display_name : '';
	$format        = current_theme_supports( 'html5', 'comment-form' ) ? 'html5' : 'xhtml';
	$format        = apply_filters( 'hocwp_comment_form_format', $format );
	$req           = get_option( 'require_name_email' );
	$aria_req      = ( $req ? " aria-required='true'" : '' );
	$html_req      = ( $req ? " required='required'" : '' );
	$required_text = sprintf( ' ' . 'Những mục bắt buộc được đánh dấu %s', '<span class="required">*</span>' );
	$html5         = 'html5' === $format;
	$defaults      = array(
		'comment_field'        => '<p class="comment-form-comment"><label for="comment">' . 'Nội dung' . '</label> <textarea id="comment" name="comment" cols="45" rows="8"  aria-required="true" required="required"></textarea></p>',
		'must_log_in'          => '<p class="must-log-in">' . sprintf( 'Bạn phải <a href="%s">đăng nhập</a> trước khi có thể đăng bình luận.', wp_login_url( apply_filters( 'the_permalink', get_permalink( get_the_ID() ) ) ) ) . '</p>',
		'logged_in_as'         => '<p class="logged-in-as">' . sprintf( 'Bạn đang đăng nhập với tài khoản <a href="%1$s">%2$s</a>. <a href="%3$s" title="Thoát khỏi tài khoản này">Thoát?</a>', get_edit_user_link(), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( get_the_ID() ) ) ) ) . '</p>',
		'comment_notes_before' => '<p class="comment-notes"><span id="email-notes">' . 'Địa chỉ email của bạn sẽ được giữ bí mật.' . '</span>' . ( $req ? $required_text : '' ) . '</p>',
		'title_reply'          => '<span class="title-text">' . 'Gửi bình luận' . '</span>',
		'title_reply_to'       => 'Gửi trả lời cho %s',
		'cancel_reply_link'    => 'Nhấn vào đây để hủy trả lời.',
		'label_submit'         => 'Gửi bình luận'
	);

	return $defaults;
}

add_filter( 'comment_form_defaults', 'hocwp_theme_translation_comment_form_defaults' );

function hocwp_theme_translation_comments_list_callback( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	$comment_id         = $comment->comment_ID;
	$style              = isset( $args['style'] ) ? $args['style'] : 'ol';
	$avatar_size        = isset( $args['avatar_size'] ) ? absint( $args['avatar_size'] ) : 64;
	$avatar_size        = apply_filters( 'hocwp_comment_avatar_size', $avatar_size );
	$max_depth          = isset( $args['max_depth'] ) ? absint( $args['max_depth'] ) : '';
	$comment_permalink  = get_comment_link( $comment );
	if ( 'div' == $style ) {
		$tag       = 'div';
		$add_below = 'comment';
	} else {
		$tag       = 'li';
		$add_below = 'div-comment';
	}
	$comment_date     = get_comment_date( 'Y-m-d H:i:s', $comment_id );
	$comment_author   = '<div class="comment-author vcard">' . get_avatar( $comment, $avatar_size ) . '<b class="fn">' . get_comment_author_link() . '</b> <span class="says">nói:</span></div>';
	$comment_metadata = '<div class="comment-metadata"><a href="' . $comment_permalink . '"><time datetime="' . get_comment_time( 'c' ) . '">' . hocwp_human_time_diff_to_now( $comment_date ) . ' ' . 'trước' . '</time></a> <a class="comment-edit-link" href="' . get_edit_comment_link( $comment_id ) . '">(' . 'Sửa' . ')</a></div>';
	if ( $comment->comment_approved == '0' ) {
		$comment_metadata .= '<p class="comment-awaiting-moderation">' . 'Bình luận của bạn đang được chờ để xét duyệt.' . '</p>';
	}
	$footer = new HOCWP_HTML( 'footer' );
	$footer->set_class( 'comment-meta' );
	$footer->set_text( $comment_author . $comment_metadata );
	$comment_text    = get_comment_text( $comment_id );
	$comment_text    = apply_filters( 'comment_text', $comment_text, $comment );
	$comment_content = '<div class="comment-content">' . $comment_text . '</div>';
	$reply           = '<div class="reply comment-tools">';
	$reply .= get_comment_reply_link( array_merge( $args, array(
		'add_below' => $add_below,
		'depth'     => $depth,
		'max_depth' => $max_depth
	) ) );
	$comment_tools_enabled = apply_filters( 'hocwp_comment_tools_enabled', true );
	if ( $comment_tools_enabled ) {
		$class                     = 'comment-like comment-likes';
		$session_comment_liked_key = 'comment_' . $comment_id . '_likes';
		$liked                     = intval( isset( $_SESSION[ $session_comment_liked_key ] ) ? $_SESSION[ $session_comment_liked_key ] : '' );
		if ( $liked == 1 ) {
			hocwp_add_string_with_space_before( $class, 'disabled' );
		}
		$a = new HOCWP_HTML( 'a' );
		$a->set_class( $class );
		$a->set_attribute( 'href', 'javascript:;' );
		$a->set_attribute( 'data-session-likes-key', $session_comment_liked_key );
		$likes = hocwp_get_comment_likes( $comment_id );
		$a->set_attribute( 'data-likes', $likes );
		$a->set_text( '<span class="text">' . 'Thích' . '</span> <i class="fa fa-thumbs-o-up"></i><span class="sep-dot">.</span> <span class="count">' . $likes . '</span>' );
		$reply .= $a->build();
		$a->set_class( 'comment-report' );
		$a->remove_attribute( 'data-session-liked-key' );
		$a->set_text( 'Báo cáo vi phạm' . '<i class="fa fa-flag"></i>' );
		$reply .= $a->build();
		$a->set_class( 'comment-share' );
		$share_text = '<span class="text">' . 'Chia sẻ' . '<i class="fa fa-angle-down"></i></span>';
		$share_text .= '<span class="list-share">';
		$share_text .= '<i class="fa fa-facebook facebook" data-url="' . hocwp_get_social_share_url( array(
				'social_name' => 'facebook',
				'permalink'   => $comment_permalink
			) ) . '"></i>';
		$share_text .= '<i class="fa fa-google-plus google" data-url="' . hocwp_get_social_share_url( array(
				'social_name' => 'googleplus',
				'permalink'   => $comment_permalink
			) ) . '"></i>';
		$share_text .= '<i class="fa fa-twitter twitter" data-url="' . hocwp_get_social_share_url( array(
				'social_name' => 'twitter',
				'permalink'   => $comment_permalink
			) ) . '"></i>';
		$share_text .= '</span>';
		$a->set_text( $share_text );
		$reply .= $a->build();
	}
	$reply .= '</div>';
	$article = new HOCWP_HTML( 'article' );
	$article->set_attribute( 'id', 'div-comment-' . $comment_id );
	$article->set_class( 'comment-body' );
	$article_text = $footer->build();
	$article_text .= $comment_content;
	$article_text .= $reply;
	$article->set_text( $article_text );
	$html          = new HOCWP_HTML( $tag );
	$comment_class = get_comment_class( empty( $args['has_children'] ) ? '' : 'parent' );
	$comment_class = implode( ' ', $comment_class );
	$html_atts     = array(
		'class'           => $comment_class,
		'id'              => 'comment-' . $comment_id,
		'data-comment-id' => $comment_id
	);
	$html->set_attribute_array( $html_atts );
	$html->set_text( $article->build() );
	$html->set_close( false );
	$html->output();
}

function hocwp_theme_translation_comment_form_default_fields( $fields ) {
	$commenter     = wp_get_current_commenter();
	$user          = wp_get_current_user();
	$user_identity = $user->exists() ? $user->display_name : '';
	$format        = current_theme_supports( 'html5', 'comment-form' ) ? 'html5' : 'xhtml';
	$format        = apply_filters( 'hocwp_comment_form_format', $format );
	$req           = get_option( 'require_name_email' );
	$aria_req      = ( $req ? "aria-required='true'" : '' );
	$html_req      = ( $req ? "required='required'" : '' );
	$require_attr  = $aria_req . ' ' . $html_req;
	$html5         = 'html5' === $format;
	$fields        = array(
		'author' => '<p class="comment-form-author">' . '<label for="author">' . 'Họ và tên' . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
		            '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" ' . $require_attr . ' /></p>',
		'email'  => '<p class="comment-form-email"><label for="email">' . 'Địa chỉ email' . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
		            '<input id="email" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30" aria-describedby="email-notes" ' . $require_attr . ' /></p>',
		'url'    => '<p class="comment-form-url"><label for="url">' . 'Trang web' . '</label> ' .
		            '<input id="url" name="url" ' . ( $html5 ? 'type="url"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" /></p>',
	);

	return $fields;
}

add_filter( 'comment_form_default_fields', 'hocwp_theme_translation_comment_form_default_fields' );

function hocwp_theme_translation_wp_list_comments_args( $args ) {
	if ( hocwp_wc_installed() && is_singular( 'product' ) ) {
		return $args;
	}
	$args['reply_text'] = '<i class="fa fa-reply"></i><span class="text">' . 'Trả lời' . '</span>';
	$args['callback']   = 'hocwp_theme_translation_comments_list_callback';

	return $args;
}

add_filter( 'wp_list_comments_args', 'hocwp_theme_translation_wp_list_comments_args', 10 );

function hocwp_theme_translation_gettext( $translation, $text ) {
	switch ( $text ) {
		case 'Nothing Found':
			$translation = 'Không tìm thấy nội dung';
			break;
		case 'Ready to publish your first post? <a href="%1$s">Get started here</a>.':
			$translation = 'Bạn đã sẵn sàng viết bài? <a href="%1$s">Bắt đầu từ đây</a>.';
			break;
		case 'Sorry, but nothing matched your search terms. Please try again with some different keywords.':
			$translation = 'Xin lỗi, nhưng hệ thống không tìm thấy nội dung bạn đang tìm kiếm, có thể thử lại bằng cách dùng từ khóa khác.';
			break;
		case 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.':
			$translation = 'Hệ thống không tìm thấy nội dung bạn đang muốn xem. Bạn có thể thử dùng công cụ tìm kiếm để trợ giúp.';
			break;
		case 'It looks like nothing was found at this location. Maybe try a search?':
			$translation = 'Dường như không có gì được tìm thấy trong đường dẫn này. Bạn có thể thử công cụ tìm kiếm?';
			break;
		case 'Oops! That page can&rsquo;t be found.':
			$translation = 'Xin lỗi! Trang này không được tìm thấy';
			break;
		case 'No Comments<span class="screen-reader-text"> on %s</span>';
			$translation = '0 bình luận<span class="screen-reader-text"> cho %s</span>';
			break;
		case 'Processing...':
			$translation = 'Đang xử lý...';
			break;
		case 'Related posts':
			$translation = 'Bài viết liên quan';
			break;
		case 'Enter captcha code':
			$translation = 'Mã bảo mật';
			break;
		case 'Full name':
			$translation = 'Họ và tên';
			break;
		case 'Full name *':
			$translation = 'Họ và tên *';
			break;
		case 'Message':
			$translation = 'Tin nhắn';
			break;
		case 'Send':
			$translation = 'Gửi';
			break;
		case '[%1$s] Contact message from %2$s':
			$translation = '[%1$s] Tin nhắn liên hệ từ %2$s';
			break;
		case 'Required fields are marked as %s';
			$translation = 'Mục bắt buộc được đánh dấu %s';
			break;
		case 'Page %1$s of %2$s':
			$translation = 'Trang %1$s trên tổng số %2$s';
			break;
		case 'Page %1$s':
			$translation = 'Trang %1$s';
			break;
		case 'Page %s':
			$translation = 'Trang %s';
			break;
		case 'Page %d':
			$translation = 'Trang %d';
			break;
		case 'Page':
			$translation = 'Trang';
			break;
		case 'Search results for &ldquo;%s&rdquo;':
			$translation = 'Kết quả tìm kiếm cho &ldquo;%s&rdquo;';
			break;
		case 'Author: %s':
			$translation = 'Tác giả: %s';
			break;
		case 'Products tagged &ldquo;%s&rdquo;':
		case 'Posts tagged &ldquo;%s&rdquo;':
			$translation = 'Thẻ &ldquo;%s&rdquo;';
			break;
		case 'Error 404':
			$translation = 'Không tìm thấy trang';
			break;
		case 'Close':
			$translation = 'Đóng';
			break;
		case 'Previous Image':
			$translation = 'Ảnh trước';
			break;
		case 'Next Image':
			$translation = 'Ảnh kế tiếp';
			break;
		case 'Edit':
			$translation = 'Chỉnh sửa';
			break;
		case 'Edit <span class="screen-reader-text"> "%s"</span>':
			$translation = 'Chỉnh sửa <span class="screen-reader-text"> "%s"</span>';
			break;
		case 'Home':
			$translation = 'Trang chủ';
			break;
		case 'View your shopping cart':
			$translation = 'Xem giỏ hàng của bạn';
			break;
		case 'Your cart':
			$translation = 'Giỏ hàng của bạn';
			break;
		case 'Your cart is empty now.':
			$translation = 'Hiện tại giỏ hàng của bạn đang trống.';
			break;
	}

	return $translation;
}

add_filter( 'gettext', 'hocwp_theme_translation_gettext', 10, 2 );

function hocwp_theme_translation_gettext_with_context( $translation, $text, $context, $domain = 'default' ) {
	switch ( $text ) {
		case 'Search for:':
			$translation = 'Tìm kiếm cho:';
			break;
		case 'Search &hellip;':
			$translation = 'Từ khóa&hellip;';
			break;
		case 'Search':
			$translation = 'Tìm kiếm';
			break;
		case 'Page %s':
			$translation = 'Trang %s';
			break;
		case 'Page':
			$translation = 'Trang';
			break;
	}

	return $translation;
}

add_filter( 'gettext_with_context', 'hocwp_theme_translation_gettext_with_context', 10, 3 );

function hocwp_theme_translation_ngettext( $translation, $single, $plural, $number, $domain = 'default' ) {
	$translations = get_translations_for_domain( $domain );
	$translation  = $translations->translate_plural( $single, $plural, $number );
	switch ( $translation ) {
		case '%s second':
		case '%s seconds':
			$translation = '%s giây';
			break;
		case '%s min':
		case '%s mins':
		case '%s minute':
		case '%s minutes':
			$translation = '%s phút';
			break;
		case '%s hour':
		case '%s hours':
			$translation = '%s giờ';
			break;
		case '%s day':
		case '%s days':
			$translation = '%s ngày';
			break;
		case '%s week':
		case '%s weeks':
			$translation = '%s tuần';
			break;
		case '%s month':
		case '%s months':
			$translation = '%s tháng';
			break;
		case '%s year':
		case '%s years':
			$translation = '%s năm';
			break;
		case 'Categories:':
		case 'Category:':
			$translation = 'Chuyên mục:';
			break;
		case 'Tags:':
		case 'Tag:':
			$translation = 'Thẻ:';
			break;
		case 'Page %s':
			$translation = 'Trang %s';
			break;
		case 'Page':
			$translation = 'Trang';
			break;
		default:
			$translation = apply_filters( 'hocwp_theme_translation_ngettext', $translation, $single, $plural, $number, $domain );
	}

	return $translation;
}

add_filter( 'ngettext', 'hocwp_theme_translation_ngettext', 10, 4 );

function hocwp_theme_translation_ngettext_with_context( $translation, $single, $plural, $number, $context, $domain = 'default' ) {
	$translations = get_translations_for_domain( $domain );
	$translation  = $translations->translate_plural( $single, $plural, $number );
	switch ( $translation ) {
		case '%d item':
		case '%d items':
			if('Shopping cart item number' == $context) {
				$translation = '%d sản phẩm';
			}
			break;
	}

	return $translation;
}

add_filter( 'ngettext_with_context', 'hocwp_theme_translation_ngettext_with_context', 10, 6 );

function hocwp_theme_translation_gettext_woocommerce( $translation, $text ) {
	switch ( $text ) {
		case 'SKU:':
			$translation = 'Mã sản phẩm:';
			break;
		case 'View Cart':
			$translation = 'Xem giỏ hàng';
			break;
		case 'Order Received':
			$translation = 'Đặt hàng thành công';
			break;
		case 'Thank you. Your order has been received.':
			$translation = 'Xin cảm ơn, đơn hàng của bạn đã được lưu vào hệ thống.';
			break;
		case 'Order Number:':
			$translation = 'Mã đơn hàng:';
			break;
		case 'Date:':
			$translation = 'Ngày:';
			break;
		case 'Payment Method:':
			$translation = 'Phương thức thanh toán:';
			break;
		case 'Our Bank Details':
			$translation = 'Thông tin chuyển khoản';
			break;
		case 'Order Details':
			$translation = 'Chi tiết đơn hàng';
			break;
		case 'Products':
		case 'Product':
			$translation = 'Sản phẩm';
			break;
		case 'Total:':
			$translation = 'Tổng cộng:';
			break;
		case 'Totals':
		case 'Total':
			$translation = 'Tổng';
			break;
		case 'Price':
			$translation = 'Giá';
			break;
		case 'Quantity':
			$translation = 'Số lượng';
			break;
		case 'Coupon code':
			$translation = 'Mã giảm giá';
			break;
		case 'Apply Coupon':
			$translation = 'Áp dụng mã giảm giá';
			break;
		case 'Coupon has been removed.':
			$translation = 'Mã giảm giá đã được xóa.';
			break;
		case 'Please enter a coupon code.':
			$translation = 'Xin vui lòng nhập mã giảm giá.';
			break;
		case 'Cart Totals':
			$translation = 'Tổng cộng giỏ hàng';
			break;
		case 'Update Cart':
			$translation = 'Cập nhật giỏ hàng';
			break;
		case 'Proceed to Checkout':
			$translation = 'Tiến hành thanh toán';
			break;
		case 'Place order':
			$translation = 'Đặt hàng';
			break;
		case 'Your order':
			$translation = 'Đơn hàng của bạn';
			break;
		case 'Postcode / ZIP':
			$translation = 'Mã bưu chính';
			break;
		case 'Town / City':
			$translation = 'Tỉnh / Thành phố';
			break;
		case 'State / County':
			$translation = 'Quận / Huyện';
			break;
		case 'Address':
			$translation = 'Địa chỉ';
			break;
		case 'Save Address':
			$translation = 'Lưu địa chỉ';
			break;
		case 'Edit Address':
			$translation = 'Chỉnh sửa địa chỉ';
			break;
		case 'My Address':
			$translation = 'Địa chỉ của tôi';
			break;
		case 'The following addresses will be used on the checkout page by default.':
			$translation = 'Địa chỉ phía bên dưới mặc định sẽ được áp dụng khi thanh toán.';
			break;
		case 'Order':
			$translation = 'Đơn hàng';
			break;
		case 'Date':
			$translation = 'Ngày';
			break;
		case 'Status':
			$translation = 'Trạng thái';
			break;
		case 'View':
			$translation = 'Xem';
			break;
		case 'Edit':
			$translation = 'Chỉnh sửa';
			break;
		case 'On Hold':
			$translation = 'Đang chờ xử lý';
			break;
		case 'Recent Orders':
			$translation = 'Đơn hàng gần đây';
			break;
		case 'Hello <strong>%1$s</strong> (not %1$s? <a href="%2$s">Sign out</a>).':
			$translation = 'Xin chào <strong>%1$s</strong> (không phải %1$s? <a href="%2$s">Thoát</a>).';
			break;
		case 'From your account dashboard you can view your recent orders, manage your shipping and billing addresses and <a href="%s">edit your password and account details</a>.':
			$translation = 'Bạn có thể xem thông tin lịch sử các đơn hàng gần đây, quản lý địa chỉ thanh toán, địa chỉ giao nhận hàng và <a href="%s">chỉnh sửa thông tin tài khoản</a> trên trang này.';
			break;
		case 'Country':
			$translation = 'Quốc gia';
			break;
		case 'Have a coupon?':
			$translation = 'Có mã giảm giá?';
			break;
		case 'Click here to enter your code':
			$translation = 'Nhấn vào đây để nhập mã của bạn';
			break;
		case 'Subtotal':
			$translation = 'Tạm tính';
			break;
		case 'Subtotal:':
			$translation = 'Tạm tính:';
			break;
		case 'Shipping:':
			$translation = 'Phí vận chuyển:';
			break;
		case 'Customer details':
		case 'Customer Details':
			$translation = 'Thông tin khách hàng';
			break;
		case 'Note:':
			$translation = 'Ghi chú:';
			break;
		case 'Company Name':
			$translation = 'Tên công ty';
			break;
		case 'Email Address':
			$translation = 'Địa chỉ email';
			break;
		case 'Phone':
			$translation = 'Điện thoại';
			break;
		case 'Tel:':
		case 'Telephone:':
			$translation = 'Điện thoại:';
			break;
		case 'Additional Information':
			$translation = 'Thông tin tùy chọn';
			break;
		case 'First Name':
			$translation = 'Tên';
			break;
		case 'Last Name':
			$translation = 'Họ';
			break;
		case 'Order Notes':
			$translation = 'Ghi chú đơn hàng';
			break;
		case 'Billing Details':
			$translation = 'Thông tin thanh toán';
			break;
		case 'Billing address':
		case 'Billing Address':
			$translation = 'Địa chỉ thanh toán';
			break;
		case 'Cart':
			$translation = 'Giỏ hàng';
			break;
		case 'Your cart is currently empty.':
			$translation = 'Hiện tại giỏ hàng của bạn đang trống.';
			break;
		case 'Return To Shop':
			$translation = 'Quay lại gian hàng';
			break;
		case 'Cart updated.':
			$translation = 'Giỏ hàng đã được cập nhật.';
			break;
		case '%s removed. %sUndo?%s':
			$translation = '%s đã được xóa. %sHoàn tác?%s';
			break;
		case '%s removed.':
			$translation = '%s đã được xóa.';
			break;
		case 'Coupon "%s" does not exist!':
			$translation = 'Mã giảm giá "%s" không tồn tại!';
			break;
		case 'Coupon does not exist!':
			$translation = 'Mã giảm giá không tồn tại!';
			break;
		case 'This coupon has expired.':
			$translation = 'Mã giảm giá đã hết hạn.';
			break;
		case 'Coupon code applied successfully.':
			$translation = 'Mã giảm giá đã được áp dụng thành công.';
			break;
		case 'Coupon code already applied!':
			$translation = 'Mã giảm giá đã được áp dụng.';
			break;
		case 'Coupon:':
			$translation = 'Mã giảm giá:';
			break;
		case '[Remove]':
			$translation = '[Xóa]';
			break;
		case 'There are no reviews yet.':
			$translation = 'Hiện chưa có nhận xét nào.';
			break;
		case 'Be the first to review &ldquo;%s&rdquo;':
			$translation = 'Hãy trở thành người đầu tiên gửi nhận xét cho &ldquo;%s&rdquo;';
			break;
		case 'Reviews':
			$translation = 'Nhận xét';
			break;
		case 'Reviews (%d)':
			$translation = 'Nhận xét (%d)';
			break;
		case 'Description':
			$translation = 'Mô tả';
			break;
		case 'Product Description':
			$translation = 'Mô tả sản phẩm';
			break;
		case 'Related Products':
			$translation = 'Sản phẩm liên quan';
			break;
		case 'Submit':
			$translation = 'Gửi';
			break;
		case 'Your Review':
			$translation = 'Nhận xét của bạn';
			break;
		case 'Your Rating':
			$translation = 'Đánh giá của bạn';
			break;
		case 'Add a review':
			$translation = 'Thêm nhận xét';
			break;
		case 'Rated %d out of 5':
		case 'Rated %s out of 5':
			$translation = 'Được đánh giá %s trên tổng số 5';
			break;
		case 'Rate&hellip;':
			$translation = 'Đánh giá&hellip;';
			break;
		case 'Perfect':
			$translation = 'Hoàn hảo';
			break;
		case 'Good':
			$translation = 'Tốt';
			break;
		case 'Average':
			$translation = 'Trung bình';
			break;
		case 'Not that bad':
			$translation = 'Không tệ';
			break;
		case 'Very Poor':
			$translation = 'Rất tệ';
			break;
		case 'Choose an option':
			$translation = 'Chọn tùy chọn';
			break;
		case 'Clear':
			$translation = 'Xóa';
			break;
		case 'Name':
			$translation = 'Tên';
			break;
		case 'Create an account?':
			$translation = 'Tạo tài khoản?';
			break;
		case 'Returning customer?':
			$translation = 'Đã có tài khoản?';
			break;
		case 'Click here to login':
			$translation = 'Nhấn vào đây để đăng nhập';
			break;
		case 'If you have shopped with us before, please enter your details in the boxes below. If you are a new customer, please proceed to the Billing &amp; Shipping section.':
		case 'If you have shopped with us before, please enter your details in the boxes below. If you are a new customer, please proceed to the Billing & Shipping section.':
			$translation = 'Nếu bạn đã mua hàng trước đó, xin vui lòng nhập thông tin của bạn vào ô bên dưới. Nếu bạn lần đầu tiên mua hàng, xin vui lòng điền thông tin của bạn phía bên dưới.';
			break;
		case 'Username or email address':
		case 'Username or email':
			$translation = 'Tên tài khoản hoặc email';
			break;
		case 'Password':
			$translation = 'Mật khẩu';
			break;
		case 'Remember me':
			$translation = 'Nhớ đăng nhập';
			break;
		case 'Login':
			$translation = 'Đăng nhập';
			break;
		case 'Lost your password?':
			$translation = 'Đã quên mật khẩu?';
			break;
		case 'Lost Password':
			$translation = 'Quên mật khẩu';
			break;
		case 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.':
			$translation = 'Bạn đã quên mật khẩu? Xin vui lòng nhập địa chỉ email hoặc tên tài khoản. Bạn sẽ nhận thông tin để tạo mật khẩu mới thông qua địa chỉ email đã đăng ký.';
			break;
		case 'Reset Password':
			$translation = 'Khôi phục mật khẩu';
			break;
		case 'Enter a username or e-mail address.':
			$translation = 'Nhập tên tài khoản hoặc địa chỉ email.';
			break;
		case 'Invalid username or e-mail.':
			$translation = 'Tên tài khoản hoặc địa chỉ email không đúng.';
			break;
		case 'Username is required.':
			$translation = 'Tên tài khoản là bắt buộc.';
			break;
		case 'ERROR':
		case 'Error':
			$translation = 'Lỗi';
			break;
		case 'Password is required.':
			$translation = 'Mật khẩu là bắt buộc.';
			break;
		case 'Invalid username.':
			$translation = 'Tên tài khoản không đúng.';
			break;
		case 'A user could not be found with this email address.':
			$translation = 'Không tìm thấy tài khoản với địa chỉ email này.';
			break;
		case 'Register':
			$translation = 'Đăng ký';
			break;
		case 'Email address':
			$translation = 'Địa chỉ email';
			break;
		case 'You are now logged in as <strong>%s</strong>':
			$translation = 'Bạn đang đăng nhập với tên tài khoản <strong>%s</strong>';
			break;
		case 'Pay for order';
		case 'Pay for Order':
			$translation = 'Thanh toán cho đơn hàng';
			break;
		case 'Qty':
			$translation = 'Số lượng';
			break;
		case 'This order&rsquo;s status is &ldquo;%s&rdquo;&mdash;it cannot be paid for. Please contact us if you need assistance.':
			$translation = 'Không thể thanh toán cho đơn hàng với trạng thái &ldquo;%s&rdquo;. Xin vui lòng liên hệ với chúng tôi nếu bạn cần sự trợ giúp.';
			break;
		case 'Select product options before adding this product to your cart.':
			$translation = 'Lựa chọn tùy chọn của sản phẩm trước khi thêm vào giỏ hàng.';
			break;
		case 'You have received an order from %s.':
			$translation = 'Bạn vừa nhận được đơn hàng từ %s.';
			break;
		case 'You have received an order from %s. The order is as follows:':
			$translation = 'Bạn vừa nhận được đơn hàng từ %s. Thông tin chi tiết như bên dưới:';
			break;
		case 'Discount:':
			$translation = 'Chiết khấu:';
			break;
		case 'Account Number':
			$translation = 'Số tài khoản';
			break;
		case 'Sort Code':
			$translation = 'Mã số';
			break;
		case 'Calculate Shipping':
			$translation = 'Tính phí vận chuyển';
			break;
		case 'Update Totals':
			$translation = 'Cập nhật chi phí';
			break;
		case 'Select a country&hellip;':
		case 'Select a country...':
			$translation = 'Chọn quốc gia...';
			break;
		case 'State / county':
			$translation = 'Tỉnh / Thành Phố';
			break;
		case 'Default sorting':
			$translation = 'Sắp xếp mặc định';
			break;
		case 'Sort by popularity':
			$translation = 'Sắp xếp theo độ phổ biến';
			break;
		case 'Sort by average rating':
			$translation = 'Sắp xếp theo đánh giá trung bình';
			break;
		case 'Sort by newness':
			$translation = 'Sắp xếp theo mới nhất';
			break;
		case 'Sort by price: low to high':
			$translation = 'Sắp xếp theo giá thấp đến cao';
			break;
		case 'Sort by price: high to low':
			$translation = 'Sắp xếp theo giá cao đến thấp';
			break;
		case 'Price:':
			$translation = 'Giá:';
			break;
		case 'Filter':
			$translation = 'Lọc';
			break;
		case 'Hello %s%s%s (not %2$s? %sSign out%s)':
			$translation = 'Xin chào %s%s%s (không phải %2$s? %sĐăng xuất%s)';
			break;
		case 'From your account dashboard you can view your %1$srecent orders%2$s, manage your %3$sshipping and billing addresses%2$s and %4$sedit your password and account details%2$s.':
			$translation = 'Bạn có thể xem thông tin %1$scác đơn hàng gần đây%2$s, quản lý %3$sđịa chỉ nhận hàng và địa chỉ thanh toán%2$s, chỉnh sửa %4$smật khẩu và thông tin tài khoản%2$s.';
			break;
		case 'No order has been made yet.':
			$translation = 'Chưa có đơn hàng nào được tạo.';
			break;
		case 'Go Shop':
			$translation = 'Đến trang mua hàng';
			break;
		case 'Dashboard':
			$translation = 'Bảng điều khiển';
			break;
		case 'Orders':
			$translation = 'Đơn hàng';
			break;
		case 'Downloads':
			$translation = 'Tải về';
			break;
		case 'Addresses':
			$translation = 'Địa chỉ';
			break;
		case 'Account Details';
			$translation = 'Thông tin tài khoản';
			break;
		case 'Logout':
			$translation = 'Thoát';
			break;
		case 'No downloads available yet.':
			$translation = 'Không có tập tin để tải về.';
			break;
		case 'Shipping Address':
			$translation = 'Địa chỉ nhận hàng';
			break;
		case 'You have not set up this type of address yet.':
			$translation = 'Bạn chưa thiết lập loại địa chỉ này.';
			break;
		case 'First name':
			$translation = 'Tên';
			break;
		case 'Last name':
			$translation = 'Họ';
			break;
		case 'Password Change':
			$translation = 'Đổi mật khẩu';
			break;
		case 'Current Password (leave blank to leave unchanged)':
			$translation = 'Mật khẩu hiện tại (để trống nếu bạn không muốn thay đổi)';
			break;
		case 'New Password (leave blank to leave unchanged)':
			$translation = 'Mật khẩu mới (để trống nếu bạn không muốn thay đổi)';
			break;
		case 'Confirm New Password':
			$translation = 'Nhập lại mật khẩu mới';
			break;
		case 'Save changes':
			$translation = 'Lưu thay đổi';
			break;
		case 'No products were found matching your selection.':
			$translation = 'Không tìm thấy sản phẩm.';
			break;
		case 'Out of stock':
			$translation = 'Hết hàng';
			break;
		case '%s in stock':
			$translation = 'Còn %s sản phẩm';
			break;
		case 'In stock':
			$translation = 'Còn hàng';
			break;
		case 'Only %s left in stock':
			$translation = 'Chỉ còn %s sản phẩm trong kho';
			break;
		case '(also available on backorder)':
			$translation = '(bạn cũng có thể đặt hàng trước)';
			break;
		case 'Có thể đặt hàng trước':
			$translation = '';
			break;
		case 'Please select a rating':
			$translation = 'Xin vui lòng chọn đánh giá';
			break;
		case 'Back to cart page':
			$translation = 'Cập nhật giỏ hàng';
			break;
		case 'Buy now':
			$translation = 'Mua nhanh';
			break;
		case 'Fast order, without adding products to cart.':
			$translation = 'Mua hàng nhanh không cần thêm sản phẩm vào giỏ hàng.';
			break;
		case 'Fast order':
			$translation = 'Mua hàng nhanh';
			break;
		case 'Put order':
			$translation = 'Đặt hàng';
			break;
		case 'Checkout is not available whilst your cart is empty.':
			$translation = 'Bạn không thể thanh toán khi giỏ hàng đang trống.';
			break;
		case 'Home':
			$translation = 'Trang chủ';
			break;
		case 'Sale!':
			$translation = 'Giảm giá!';
			break;
		case 'Add to cart':
			$translation = 'Thêm vào giỏ hàng';
			break;
	}

	return $translation;
}

function hocwp_theme_translation_gettext_with_context_woocommerce( $translation, $text, $context, $domain = 'default' ) {
	switch ( $text ) {
		case 'Notes about your order, e.g. special notes for delivery.':
			$translation = 'Mô tả về đơn hàng của bạn, ví dụ như ghi chú thông tin giao và nhận hàng.';
			break;
		case 'Street address':
			$translation = 'Địa chỉ nhà';
			break;
		case 'Apartment, suite, unit etc. (optional)':
			$translation = 'Địa chỉ cụ thể, ví dụ căn hộ, số phòng,...';
			break;
		case 'Qty':
			$translation = 'Số lượng';
			break;
		case '%s is a required field.':
			$translation = '%s mà mục bắt buộc.';
			break;
		case 'Billing %s':
			$translation = 'Địa chỉ đơn hàng %s';
			break;
		case 'Shipping %s':
			$translation = 'Địa chỉ nhận hàng %s';
			break;
		case 'Home':
			$translation = 'Trang chủ';
			break;
	}

	return $translation;
}

function hocwp_theme_translation_ngettext_woocommerce( $translation, $single, $plural, $number, $domain = 'default' ) {
	$translations = get_translations_for_domain( $domain );
	$translation  = $translations->translate_plural( $single, $plural, $number );
	switch ( $translation ) {
		case '%s has been added to your cart.':
			$translation = '%s đã được thêm vào giỏ hàng thành công.';
			break;
		case '%s reviews for %s%s%s':
		case '%s review for %s%s%s':
			$translation = '%s nhận xét cho %s%s%s';
			break;
		case '%s review for %s':
		case '%s reviews for %s':
			$translation = '%s nhận xét cho %s';
			break;
		case '%s customer reviews':
		case '%s customer review':
			$translation = '%s nhận xét';
			break;
		case '%s for %s items';
		case '%s for %s item':
			$translation = '%s cho %s sản phẩm';
			break;
		case 'Shipping':
			$translation = 'Phí vận chuyển';
			break;
		case 'Home':
			$translation = 'Trang chủ';
			break;
	}

	return $translation;
}

if ( hocwp_wc_installed() || current_theme_supports( 'hocwp-shop' ) ) {
	add_filter( 'gettext', 'hocwp_theme_translation_gettext_woocommerce', 11, 2 );
	add_filter( 'gettext_with_context', 'hocwp_theme_translation_gettext_with_context_woocommerce', 11, 3 );
	add_filter( 'hocwp_theme_translation_ngettext', 'hocwp_theme_translation_ngettext_woocommerce', 10, 4 );
}

function hocwp_theme_translation_comment_list_class( $classes ) {
	$classes[] = 'custom';

	return $classes;
}

add_filter( 'hocwp_comment_list_class', 'hocwp_theme_translation_comment_list_class' );