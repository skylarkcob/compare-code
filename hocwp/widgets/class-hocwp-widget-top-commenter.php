<?php
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}

class HOCWP_Widget_Top_Commenter extends WP_Widget {
	public $args = array();
	public $admin_args;
	public $instance;

	private function get_defaults() {
		$defaults = array(
			'number'           => 5,
			'time'             => 'week',
			'times'            => array(
				'today' => __( 'Today', 'hocwp-theme' ),
				'week'  => __( 'This week', 'hocwp-theme' ),
				'month' => __( 'This month', 'hocwp-theme' ),
				'year'  => __( 'This year', 'hocwp-theme' ),
				'all'   => __( 'All time', 'hocwp-theme' )
			),
			'show_count'       => true,
			'link_author_name' => true,
			'none_text'        => __( 'There is no commenter in this list.', 'hocwp-theme' )
		);
		$defaults = apply_filters( 'hocwp_widget_top_commenter_defaults', $defaults, $this );
		$args     = apply_filters( 'hocwp_widget_top_commenter_args', array(), $this );
		$args     = wp_parse_args( $args, $defaults );

		return $args;
	}

	public function __construct() {
		$this->args       = $this->get_defaults();
		$this->admin_args = array(
			'id'          => 'hocwp_widget_top_commenter',
			'name'        => 'HOCWP Top Commenter',
			'class'       => 'hocwp-top-commenter-widget',
			'description' => __( 'Get top commenters on your site.', 'hocwp-theme' ),
			'width'       => 400
		);
		$this->admin_args = apply_filters( 'hocwp_widget_top_commenter_admin_args', $this->admin_args, $this );
		parent::__construct( $this->admin_args['id'], $this->admin_args['name'],
			array(
				'classname'   => $this->admin_args['class'],
				'description' => $this->admin_args['description'],
			),
			array(
				'width' => $this->admin_args['width']
			)
		);
	}

	public function widget( $args, $instance ) {
		$this->instance   = $instance;
		$number           = hocwp_get_value_by_key( $instance, 'number', hocwp_get_value_by_key( $this->args, 'number' ) );
		$time             = hocwp_get_value_by_key( $instance, 'time', hocwp_get_value_by_key( $this->args, 'time' ) );
		$exclude_users    = hocwp_get_value_by_key( $instance, 'exclude_users' );
		$exclude_users    = hocwp_json_string_to_array( $exclude_users );
		$show_count       = hocwp_get_value_by_key( $instance, 'show_count', hocwp_get_value_by_key( $this->args, 'show_count' ) );
		$link_author_name = hocwp_get_value_by_key( $instance, 'link_author_name', hocwp_get_value_by_key( $this->args, 'link_author_name' ) );
		$none_text        = hocwp_get_value_by_key( $instance, 'none_text', hocwp_get_value_by_key( $this->args, 'none_text' ) );

		hocwp_widget_before( $args, $instance );
		$condition = '';
		if ( hocwp_array_has_value( $exclude_users ) ) {
			$not_in = array();
			foreach ( $exclude_users as $data ) {
				$uid = hocwp_get_value_by_key( $data, 'value' );
				if ( hocwp_id_number_valid( $uid ) ) {
					$not_in[] = $uid;
				}
			}
			if ( hocwp_array_has_value( $not_in ) ) {
				$condition = 'AND user_id NOT IN (' . implode( ', ', $not_in ) . ')';
			}
		}
		$commenters = hocwp_get_top_commenters( $number, $time, $condition );
		ob_start();
		if ( ! hocwp_array_has_value( $commenters ) ) {
			echo wpautop( $none_text );
		} else {
			?>
			<ol class="list-commenters">
				<?php
				foreach ( $commenters as $commenter ) {
					$url     = $commenter->comment_author_url;
					$author  = $commenter->comment_author;
					$count   = absint( $commenter->comments_count );
					$email   = $commenter->comment_author_email;
					$user_id = 0;
					if ( ! empty( $commenter->user_id ) ) {
						$user_id = $commenter->user_id;
					}
					if ( (bool) $show_count ) {
						$author .= " ($count)";
					}
					if ( empty( $url ) || 'http://' == $url || ! (bool) $link_author_name ) {
						$url = $author;
					} else {
						$url = "<a href='$url' rel='external nofollow' class='url'>$author</a>";
					}
					?>
					<li class="commenter"><?php echo $url; ?></li>
					<?php
				}
				?>
			</ol>
			<?php
		}
		$widget_html = ob_get_clean();
		$widget_html = apply_filters( 'hocwp_widget_top_commenter_html', $widget_html, $args, $instance, $this );
		echo $widget_html;
		hocwp_widget_after( $args, $instance );
	}

	public function form( $instance ) {
		$this->instance   = $instance;
		$title            = hocwp_get_value_by_key( $instance, 'title' );
		$number           = hocwp_get_value_by_key( $instance, 'number', hocwp_get_value_by_key( $this->args, 'number' ) );
		$time             = hocwp_get_value_by_key( $instance, 'time', hocwp_get_value_by_key( $this->args, 'time' ) );
		$exclude_users    = hocwp_get_value_by_key( $instance, 'exclude_users' );
		$users            = hocwp_json_string_to_array( $exclude_users );
		$show_count       = hocwp_get_value_by_key( $instance, 'show_count', hocwp_get_value_by_key( $this->args, 'show_count' ) );
		$link_author_name = hocwp_get_value_by_key( $instance, 'link_author_name', hocwp_get_value_by_key( $this->args, 'link_author_name' ) );
		$none_text        = hocwp_get_value_by_key( $instance, 'none_text', hocwp_get_value_by_key( $this->args, 'none_text' ) );

		hocwp_field_widget_before( $this->admin_args['class'] );
		hocwp_widget_field_title( $this->get_field_id( 'title' ), $this->get_field_name( 'title' ), $title );

		$args = array(
			'id'    => $this->get_field_id( 'number' ),
			'name'  => $this->get_field_name( 'number' ),
			'value' => $number,
			'label' => __( 'Number:', 'hocwp-theme' )
		);
		hocwp_widget_field( 'hocwp_field_input_number', $args );

		$lists      = $this->args['times'];
		$all_option = '';
		foreach ( $lists as $key => $lvalue ) {
			$all_option .= hocwp_field_get_option( array( 'value' => $key, 'text' => $lvalue, 'selected' => $time ) );
		}
		$args = array(
			'id'         => $this->get_field_id( 'time' ),
			'name'       => $this->get_field_name( 'time' ),
			'all_option' => $all_option,
			'value'      => $time,
			'label'      => __( 'Time:', 'hocwp-theme' ),
			'multiple'   => true
		);
		hocwp_widget_field( 'hocwp_field_select', $args );

		$lists      = get_users();
		$all_option = '';
		foreach ( $lists as $lvalue ) {
			$selected = '';
			foreach ( $users as $data ) {
				$user_name = hocwp_get_value_by_key( $data, 'value' );
				if ( $lvalue->ID == $user_name ) {
					$selected = $user_name;
				}
			}
			$all_option .= hocwp_field_get_option( array(
				'value'    => $lvalue->ID,
				'text'     => $lvalue->display_name,
				'selected' => $selected
			) );
		}
		$args = array(
			'id'          => $this->get_field_id( 'exclude_users' ),
			'name'        => $this->get_field_name( 'exclude_users' ),
			'all_option'  => $all_option,
			'value'       => $exclude_users,
			'label'       => __( 'Exclude users:', 'hocwp-theme' ),
			'placeholder' => __( 'Choose user', 'hocwp-theme' ),
			'multiple'    => true
		);
		hocwp_widget_field( 'hocwp_field_select_chosen', $args );

		$args = array(
			'id'    => $this->get_field_id( 'show_count' ),
			'name'  => $this->get_field_name( 'show_count' ),
			'value' => $show_count,
			'label' => __( 'Show count', 'hocwp-theme' )
		);
		hocwp_widget_field( 'hocwp_field_input_checkbox', $args );

		$args = array(
			'id'    => $this->get_field_id( 'link_author_name' ),
			'name'  => $this->get_field_name( 'link_author_name' ),
			'value' => $link_author_name,
			'label' => __( 'Link author name', 'hocwp-theme' )
		);
		hocwp_widget_field( 'hocwp_field_input_checkbox', $args );

		$args = array(
			'id'    => $this->get_field_id( 'none_text' ),
			'name'  => $this->get_field_name( 'none_text' ),
			'value' => $none_text,
			'label' => __( 'No commenter text:', 'hocwp-theme' )
		);
		hocwp_widget_field( 'hocwp_field_input', $args );

		hocwp_field_widget_after();
	}

	public function update( $new_instance, $old_instance ) {
		hocwp_delete_transient( 'hocwp_top_commenters' );
		$instance                     = $old_instance;
		$instance['title']            = strip_tags( hocwp_get_value_by_key( $new_instance, 'title' ) );
		$instance['number']           = hocwp_get_value_by_key( $new_instance, 'number', hocwp_get_value_by_key( $this->args, 'number' ) );
		$instance['time']             = hocwp_get_value_by_key( $new_instance, 'time', hocwp_get_value_by_key( $this->args, 'time' ) );
		$instance['exclude_users']    = hocwp_get_value_by_key( $new_instance, 'exclude_users' );
		$instance['show_count']       = hocwp_checkbox_post_data_value( $new_instance, 'show_count' );
		$instance['link_author_name'] = hocwp_checkbox_post_data_value( $new_instance, 'link_author_name' );
		$instance['none_text']        = hocwp_get_value_by_key( $new_instance, 'none_text', hocwp_get_value_by_key( $this->args, 'none_text' ) );

		return $instance;
	}
}