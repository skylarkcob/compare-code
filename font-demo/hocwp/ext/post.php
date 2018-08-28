<?php
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}

function hocwp_post_trending_table_init() {
	global $wpdb;
	$table_name = $wpdb->prefix . HOCWP_TRENDING_TABLE;
	$sql        = "ID bigint(20) unsigned NOT NULL auto_increment,
        post_id bigint(20) unsigned NOT NULL default '0',
        post_date datetime NOT NULL default '0000-00-00 00:00:00',
        post_type varchar(20) NOT NULL default 'post',
        action varchar(20) NOT NULL default '',
        PRIMARY KEY (ID),
        KEY post_id (post_id)";

	hocwp_create_database_table( $table_name, $sql );
}

function hocwp_insert_trending( $args = array() ) {
	if ( ! is_array( $args ) && is_numeric( $args ) ) {
		$args = array( 'post_id' => $args );
	}
	$post_id = hocwp_get_value_by_key( $args, 'post_id' );
	if ( hocwp_id_number_valid( $post_id ) ) {
		global $wpdb;
		$datetime = hocwp_get_current_datetime_mysql();
		$post     = get_post( $post_id );
		$action   = hocwp_get_value_by_key( $args, 'action', 'view' );
		if ( empty( $action ) ) {
			$action = 'view';
		}
		$table_name = $wpdb->prefix . HOCWP_TRENDING_TABLE;
		if ( ! hocwp_is_table_exists( $table_name ) ) {
			return;
		}
		$trending_day = absint( apply_filters( 'hocwp_trending_interval', 7 ) );
		$sql          = "DELETE FROM $table_name WHERE UNIX_TIMESTAMP(post_date) < UNIX_TIMESTAMP(DATE_SUB('$datetime', INTERVAL $trending_day DAY))";
		$wpdb->query( $sql );
		$sql = "INSERT INTO $table_name (post_id, post_date, post_type, action)";
		$sql .= " VALUES ('$post_id', '$datetime', '$post->post_type', '$action')";
		$wpdb->query( $sql );
	}
}

function hocwp_get_all_trending( $post_type = null ) {
	global $wpdb;
	$table_name = $wpdb->prefix . HOCWP_TRENDING_TABLE;
	$sql        = "SELECT post_id, COUNT(post_id) as count FROM $table_name";
	if ( null != $post_type ) {
		if ( is_array( $post_type ) ) {
			$sql .= " WHERE";
			foreach ( $post_type as $type ) {
				$sql .= " post_type = '$type' OR";
			}
			$sql = trim( $sql, ' OR' );
		} elseif ( ! empty( $post_type ) ) {
			$sql .= " WHERE post_type = '$post_type'";
		}
	}
	$sql .= " GROUP BY post_id ORDER BY count DESC";
	$result = $wpdb->get_results( $sql );

	return $result;
}

function hocwp_get_all_treding_post_ids( $post_type = null ) {
	$trends   = hocwp_get_all_trending( $post_type );
	$post_ids = array();
	if ( hocwp_array_has_value( $trends ) ) {
		foreach ( $trends as $trend ) {
			$post_ids[] = $trend->post_id;
		}
	}

	return $post_ids;
}

function hocwp_post_class( $classes ) {
	$classes[] = 'hocwp-post';
	$classes[] = 'hentry';
	$classes[] = 'entry';

	return $classes;
}

add_filter( 'post_class', 'hocwp_post_class' );

function hocwp_return_post( $post_or_id = null, $output = OBJECT ) {
	$output = strtoupper( $output );
	if ( is_a( $post_or_id, 'WP_Post' ) ) {
		$post = $post_or_id;
	} elseif ( hocwp_id_number_valid( $post_or_id ) ) {
		$post = get_post( $post_or_id );
	} else {
		$post = get_post( get_the_ID() );
	}
	if ( ! is_a( $post, 'WP_Post' ) ) {
		return new WP_Error();
	}
	if ( OBJECT == $output ) {
		return $post;
	} elseif ( 'ID' == $output ) {
		return $post->ID;
	}

	return $post->ID;
}

function hocwp_excerpt_more( $more ) {
	$read_more_text = apply_filters( 'hocwp_read_more_text', __( 'Continue reading', 'hocwp-theme' ) );
	$read_more_text = apply_filters( 'hocwp_excerpt_more_text', $read_more_text );
	if ( ! empty( $read_more_text ) ) {
		$link = sprintf( '<a href="%1$s" class="more-link">%2$s</a>',
			esc_url( get_permalink( get_the_ID() ) ),
			sprintf( $read_more_text . '%s', '<span class="screen-reader-text">' . get_the_title( get_the_ID() ) . '</span>' )
		);
		$link = apply_filters( 'hocwp_excerpt_continue_reading_link', $link );
		$more = $link;
		$more = '&hellip; ' . $more;
	}

	return apply_filters( 'hocwp_excerpt_more', $more );
}

add_filter( 'excerpt_more', 'hocwp_excerpt_more' );

function hocwp_post_change_content_url( $old_url, $new_url ) {
	global $wpdb;
	$sql = "UPDATE $wpdb->posts SET post_content = (REPLACE (post_content, '$old_url', '$new_url'))";

	return $wpdb->query( $sql );
}

function hocwp_get_post_views( $post_id = null ) {
	$post_id = hocwp_return_post( $post_id, 'id' );
	$result  = get_post_meta( $post_id, 'views', true );
	$result  = absint( $result );
	if ( is_single() && $result < 1 ) {
		$result = 1;
		update_post_meta( $post_id, 'views', 1 );
	}

	return $result;
}

function hocwp_get_post_temperature( $post_id = null ) {
	$post_id = hocwp_return_post( $post_id, 'id' );
	$result  = hocwp_get_post_meta( 'temperature', $post_id );
	$result  = absint( $result );

	return $result;
}

function hocwp_update_post_temperature( $type, $post_id = null ) {
	if ( hocwp_id_number_valid( $post_id ) ) {
		$temperature = hocwp_get_post_temperature( $post_id );
		$max         = 10;
		if ( $temperature < 10 ) {
			$max = 30;
		} elseif ( $temperature < 20 ) {
			$max = 20;
		} elseif ( $temperature > 100 ) {
			$max = 5;
		}
		$step = absint( apply_filters( 'hocwp_temperature_step', rand( 1, $max ) ) );
		if ( 'up' == $type ) {
			$temperature += $step;
			do_action( 'hocwp_add_trending_post', $post_id, 'temperature' );
		} else {
			$temperature -= $step;
		}
		$temperature = absint( $temperature );
		update_post_meta( $post_id, 'temperature', $temperature );
	}
}

function hocwp_get_post_likes( $post_id = null ) {
	$post_id = hocwp_return_post( $post_id, 'id' );
	$result  = get_post_meta( $post_id, 'likes', true );
	$result  = absint( $result );

	return $result;
}

function hocwp_get_post_dislikes( $post_id = null ) {
	$post_id = hocwp_return_post( $post_id, 'id' );
	$result  = get_post_meta( $post_id, 'dislikes', true );
	$result  = absint( $result );

	return $result;
}

function hocwp_get_post_thumbnail_url( $post_id = '', $size = 'full' ) {
	$result = '';
	if ( empty( $post_id ) ) {
		$post_id = get_the_ID();
	}
	if ( has_post_thumbnail( $post_id ) ) {
		$thumbnail_id = get_post_thumbnail_id( $post_id );
		if ( hocwp_media_file_exists( $thumbnail_id ) ) {
			$image_attributes = wp_get_attachment_image_src( $thumbnail_id, $size );
			if ( $image_attributes ) {
				$result = $image_attributes[0];
			}
		}
	}
	if ( empty( $result ) ) {
		$result = get_post_meta( $post_id, 'thumbnail_url', true );
	}
	$result = apply_filters( 'hocwp_post_thumbnail_pre_from_content', $result, $post_id, $size );
	if ( empty( $result ) ) {
		$post = get_post( $post_id );
		if ( hocwp_object_valid( $post ) ) {
			$result = hocwp_get_first_image_source( $post->post_content );
		}
	}
	$result = apply_filters( 'hocwp_post_pre_post_thumbnail', $result, $post_id );
	if ( empty( $result ) ) {
		$thumbnail = hocwp_option_get_value( 'writing', 'default_post_thumbnail' );
		$thumbnail = hocwp_sanitize_media_value( $thumbnail );
		$result    = $thumbnail['url'];
	}
	if ( empty( $result ) ) {
		$no_thumbnail = HOCWP_URL . '/images/no-thumbnail.png';
		$no_thumbnail = apply_filters( 'hocwp_no_thumbnail_url', $no_thumbnail );
		$result       = $no_thumbnail;
	}
	$result = apply_filters( 'hocwp_post_thumbnail', $result, $post_id );

	return $result;
}

function hocwp_post_thumbnail_large_if_not_default( $result, $post_id ) {
	if ( empty( $result ) ) {
		$result = get_post_meta( $post_id, 'large_thumbnail', true );
		$result = hocwp_sanitize_media_value( $result );
		$result = $result['url'];
		if ( empty( $result ) ) {
			$gallery = get_post_gallery_images( $post_id );
			if ( hocwp_array_has_value( $gallery ) ) {
				$result = array_shift( $gallery );
			}
		}
	}

	return $result;
}

add_filter( 'hocwp_post_thumbnail_pre_from_content', 'hocwp_post_thumbnail_large_if_not_default', 10, 2 );

function hocwp_post_trending_track( $post_id, $action ) {
	if ( hocwp_id_number_valid( $post_id ) ) {
		hocwp_insert_trending( array( 'post_id' => $post_id, 'action' => $action ) );
	}
}

add_action( 'hocwp_add_trending_post', 'hocwp_post_trending_track', 10, 2 );

function hocwp_post_pre_comment_approved( $approved, $commentdata ) {
	$post_id = hocwp_get_value_by_key( $commentdata, 'comment_post_ID' );
	if ( hocwp_id_number_valid( $post_id ) ) {
		do_action( 'hocwp_add_trending_post', $post_id, 'comment' );
	}

	return $approved;
}

add_filter( 'pre_comment_approved', 'hocwp_post_pre_comment_approved', 10, 2 );

function hocwp_post_thumbnail( $args = array() ) {
	$post_id = isset( $args['post_id'] ) ? $args['post_id'] : '';
	if ( empty( $post_id ) ) {
		$post_id = get_the_ID();
	}
	if ( post_password_required( $post_id ) || is_attachment() ) {
		return;
	}
	$args['post_id'] = $post_id;
	$transient_name  = hocwp_build_transient_name( 'hocwp_cache_post_thumbnail_%s', $args );
	if ( false === ( $html = get_transient( $transient_name ) ) ) {
		$cache         = hocwp_get_value_by_key( $args, 'cache', HOUR_IN_SECONDS );
		$thumbnail_url = hocwp_get_value_by_key( $args, 'thumbnail_url' );
		if ( empty( $thumbnail_url ) ) {
			$large_size = hocwp_get_value_by_key( $args, 'large_size' );
			if ( $large_size ) {
				$thumbnail_url = get_post_meta( $post_id, 'large_thumbnail', true );
				$thumbnail_url = hocwp_sanitize_media_value( $thumbnail_url );
				$thumbnail_url = $thumbnail_url['url'];
				if ( empty( $thumbnail_url ) ) {
					$thumbnail_url = hocwp_get_post_thumbnail_url( $post_id );
				}
			} else {
				$thumbnail_url = hocwp_get_post_thumbnail_url( $post_id );
			}
		}
		if ( empty( $thumbnail_url ) ) {
			return;
		}
		$bfi_thumb = isset( $args['bfi_thumb'] ) ? $args['bfi_thumb'] : true;
		$bfi_thumb = apply_filters( 'hocwp_use_bfi_thumb', $bfi_thumb, $post_id );
		$size      = hocwp_sanitize_size( $args );
		$width     = $size[0];
		$height    = $size[1];
		$enlarge   = apply_filters( 'hocwp_enlarge_post_thumbnail_on_mobile', false );
		if ( $enlarge && wp_is_mobile() ) {
			$ratio = 600 / $width;
			$ratio = round( $ratio );
			if ( $ratio > 1 ) {
				$width *= $ratio;
				$height *= $ratio;
			}
		}
		$original = $thumbnail_url;
		if ( $bfi_thumb ) {
			$params = isset( $args['params'] ) ? $args['params'] : array();
			if ( is_numeric( $width ) && $width > 0 ) {
				$params['width'] = $width;
			}
			if ( is_numeric( $height ) && $height > 0 ) {
				$params['height'] = $height;
			}
			$bfi_url = apply_filters( 'hocwp_pre_bfi_thumb', '', $thumbnail_url, $params );
			if ( empty( $bfi_url ) ) {
				if ( $width > 0 || $height > 0 ) {
					$bfi_url = bfi_thumb( $thumbnail_url, $params );
				}
			}
			if ( ! empty( $bfi_url ) ) {
				$thumbnail_url = $bfi_url;
			}
		}
		$img = new HOCWP_HTML( 'img' );
		if ( is_numeric( $width ) && $width > 0 ) {
			$img->set_attribute( 'width', $size[0] );
		}
		if ( is_numeric( $height ) && $height > 0 ) {
			$img->set_attribute( 'height', $size[1] );
		}
		$img->set_attribute( 'data-original', $original );

		$lazyload = hocwp_get_value_by_key( $args, 'lazyload', false );


		$img->set_attribute( 'alt', get_the_title( $post_id ) );
		$img->set_class( 'attachment-post-thumbnail wp-post-image img-responsive' );
		$img->set_attribute( 'src', $thumbnail_url );
		$centered = (bool) hocwp_get_value_by_key( $args, 'centered', false );
		if ( $centered ) {
			$img->add_class( 'centered' );
		}
		$bk_img = '';
		if ( (bool) $lazyload ) {
			$img->set_wrap_tag( 'noscript' );
			$bk_img = $img->build();
			$img->set_wrap_tag( '' );
			$loading_icon = hocwp_get_value_by_key( $args, 'loading_icon' );
			if ( ! hocwp_is_image( $loading_icon ) ) {
				$loading_icon = hocwp_get_image_url( 'transparent.gif' );
			}
			$img->set_image_src( $loading_icon );
			$img->set_attribute( 'data-original', $thumbnail_url );
			$img->add_class( 'lazyload' );
		}
		$only_image = hocwp_get_value_by_key( $args, 'only_image' );
		if ( (bool) $only_image ) {
			$html = $img->build();
			if ( (bool) $lazyload ) {
				$html = $bk_img;
			}
		} else {
			$before      = hocwp_get_value_by_key( $args, 'before' );
			$after       = hocwp_get_value_by_key( $args, 'after' );
			$permalink   = hocwp_get_value_by_key( $args, 'permalink', get_permalink( $post_id ) );
			$loop        = isset( $args['loop'] ) ? $args['loop'] : true;
			$custom_html = isset( $args['custom_html'] ) ? $args['custom_html'] : '';
			$icon_video  = hocwp_get_value_by_key( $args, 'icon_video' );
			if ( true === $icon_video ) {
				$icon_video = '<i class="fa fa-play-circle-o" aria-hidden="true"></i>';
			}
			$fancybox = (bool) hocwp_get_value_by_key( $args, 'fancybox' );
			$a        = new HOCWP_HTML( 'a' );
			$a->set_href( $permalink );
			if ( ! empty( $icon_video ) && is_string( $icon_video ) && empty( $custom_html ) ) {
				$a->set_text( $icon_video );
				$custom_html = $a->build();
			}
			$icon_image = hocwp_get_value_by_key( $args, 'icon_image' );
			if ( true === $icon_image ) {
				$icon_image = '<i class="fa fa-camera" aria-hidden="true"></i>';
			}
			if ( ! empty( $icon_image ) && is_string( $icon_image ) && empty( $custom_html ) ) {
				$a->set_text( $icon_image );
				$custom_html = $a->build();
			}
			$cover  = hocwp_get_value_by_key( $args, 'cover' );
			$schema = '';
			if ( current_theme_supports( 'hocwp-schema' ) ) {
				ob_start();
				?>
				<meta itemprop="url" content="<?php echo $thumbnail_url; ?>">
				<meta itemprop="width" content="<?php echo $width; ?>">
				<meta itemprop="height" content="<?php echo $height; ?>">
				<?php
				$schema = ob_get_clean();
			}
			$html = $before;
			if ( is_singular() && ! $loop ) {
				ob_start();
				?>
				<div <?php hocwp_attr( 'entry-thumbnail', 'thumbnail-single', array( 'class' => 'post-thumbnail entry-thumb' ) ); ?>>
					<?php
					$img->output();
					if ( (bool) $lazyload ) {
						echo $bk_img;
					}
					echo $custom_html;
					echo $schema;
					?>
				</div>
				<?php
				$html = ob_get_clean();
			} else {
				if ( ! empty( $custom_html ) ) {
					$html .= '<div class="thumbnail-wrap">';
				}
				$class = 'post-thumbnail-loop entry-thumb post-thumbnail';
				$atts  = '';
				if ( $fancybox ) {
					hocwp_add_string_with_space_before( $class, 'fancybox' );
					$atts = ' data-fancybox-group="gallery"';
					$atts .= ' rel="gallery"';
					$permalink = $original;
				}
				$attr = $atts;
				$atts = array(
					'class' => $class,
					'href'  => $permalink
				);
				ob_start();
				?>
				<a <?php hocwp_attr( 'entry-thumbnail', 'thumbnail-link', $atts, $attr ); ?>>
					<?php
					$img->output();
					if ( (bool) $lazyload ) {
						echo $bk_img;
					}
					if ( $cover ) {
						echo '<span class="cover"></span>';
					}
					echo $schema;
					?>
				</a>
				<?php
				$html .= ob_get_clean();
				$html .= $custom_html;
				if ( ! empty( $custom_html ) ) {
					$html .= '</div>';
				}
			}
			$html .= $after;
		}
		if ( ! empty( $html ) ) {
			set_transient( $transient_name, $html, $cache );
		}
	}
	echo $html;
}

function hocwp_post_type_no_featured_field() {
	return apply_filters( 'hocwp_post_type_no_featured_field', array( 'page' ) );
}

function hocwp_get_pages( $args = array() ) {
	return get_pages( $args );
}

function hocwp_get_child_pages( $parent, $args = array() ) {
	if ( hocwp_id_number_valid( $parent ) ) {
		$parent_id = $parent;
	} else {
		$parent_id = $parent->ID;
	}
	$args['child_of'] = $parent_id;

	return hocwp_get_pages( $args );
}

function hocwp_get_pages_by_template( $template_name, $args = array() ) {
	$args['meta_key']   = '_wp_page_template';
	$args['meta_value'] = $template_name;
	$result             = hocwp_get_pages( $args );
	$output             = strtoupper( hocwp_get_value_by_key( $args, 'output' ) );
	if ( OBJECT === $output && hocwp_array_has_value( $result ) ) {
		$result = array_shift( $result );
	}

	return $result;
}

function hocwp_get_page_by_template( $template_name ) {
	return hocwp_get_pages_by_template( $template_name, array( 'output' => 'object' ) );
}

function hocwp_article_before( $post_class = '', $tag = 'article' ) {
	$article = '<' . $tag . ' ';
	ob_start();
	post_class( $post_class );
	$article .= ob_get_clean();
	$article .= ' data-id="' . get_the_ID() . '"';
	if ( current_theme_supports( 'hocwp-schema' ) ) {
		ob_start();
		hocwp_attr( 'post' );
		$article .= ob_get_clean();
	}
	$article .= '>';
	$article = apply_filters( 'hocwp_article_before', $article );
	echo $article;
}

function hocwp_article_after( $tag = 'article' ) {
	echo '</' . $tag . '>';
}

function hocwp_post_title_link( $args = array() ) {
	$title     = hocwp_get_value_by_key( $args, 'title' );
	$permalink = hocwp_get_value_by_key( $args, 'permalink', get_permalink() );
	if ( empty( $title ) ) {
		the_title( sprintf( '<h2 class="entry-title post-title" itemprop="headline"><a href="%s" rel="bookmark">', esc_url( $permalink ) ), '</a></h2>' );
	} else {
		$title = sprintf( '<h2 class="entry-title post-title" itemprop="headline"><a href="%s" rel="bookmark">', esc_url( $permalink ) ) . $title . '</a></h2>';
		echo $title;
	}
}

function hocwp_post_link_only( WP_Post $post, $list = false ) {
	if ( hocwp_is_post( $post ) ) {
		$a = new HOCWP_HTML( 'a' );
		$a->set_href( get_permalink( $post ) );
		$a->set_text( $post->post_title );
		$a->add_class( 'post-link' );
		$a->add_class( 'link-only' );
		if ( $list ) {
			$li = new HOCWP_HTML( 'li' );
			$li->set_class( get_post_class( '', $post->ID ) );
			$li->set_text( $a );
			$li->output();
		} else {
			$a->output();
		}
	}
}

function hocwp_post_title_single( $args = array() ) {
	$class = hocwp_get_value_by_key( $args, 'class' );
	hocwp_add_string_with_space_before( $class, 'entry-title post-title' );
	$tag = hocwp_get_value_by_key( $args, 'tag', 'h1' );
	the_title( '<' . $tag . ' class="' . $class . '" itemprop="headline">', '</' . $tag . '>' );
}

function hocwp_article_header( $args = array() ) {
	$loop       = isset( $args['loop'] ) ? $args['loop'] : false;
	$entry_meta = isset( $args['entry_meta'] ) ? $args['entry_meta'] : true;
	?>
	<header class="entry-header">
		<?php
		if ( ! $loop && ( is_single() || is_singular() ) ) {
			hocwp_post_title_single();
		} else {
			hocwp_post_title_link();
		}
		if ( ! is_page() && $entry_meta ) {
			?>
			<div class="entry-meta">
				<?php hocwp_entry_meta(); ?>
			</div><!-- .entry-meta -->
			<?php
		}
		?>
	</header><!-- .entry-header -->
	<?php
}

function hocwp_article_content() {
	?>
	<div class="entry-content">
		<?php the_content(); ?>
	</div>
	<?php
}

function hocwp_article_footer( $args = array() ) {
	$entry_meta = isset( $args['entry_meta'] ) ? $args['entry_meta'] : true;
	?>
	<footer class="entry-footer">
		<?php if ( $entry_meta ) : ?>
			<div class="entry-meta">
				<?php hocwp_entry_meta(); ?>
				<?php edit_post_link( __( 'Edit', 'hocwp-theme' ), '<span class="edit-link">', '</span>' ); ?>
			</div>
		<?php endif; ?>
	</footer><!-- .entry-footer -->
	<?php
}

function hocwp_post_author_box() {
	if ( ( is_single() || is_singular() ) && get_the_author_meta( 'description' ) ) {
		get_template_part( 'hocwp/theme/biography' );
	}
}

function hocwp_post_get_taxonomies( $object, $output = 'names' ) {
	return get_object_taxonomies( $object, $output );
}

function hocwp_post_get_top_parent_terms( $post, $taxonomy = 'any' ) {
	$result     = array();
	$taxonomies = array( $taxonomy );
	if ( 'any' === $taxonomy ) {
		$taxonomies = hocwp_post_get_taxonomies( $post );
	}
	foreach ( $taxonomies as $tax ) {
		$terms = wp_get_post_terms( $post->ID, $tax );
		foreach ( $terms as $term ) {
			if ( $term->parent != 0 ) {
				$term = hocwp_term_get_top_most_parent( $term );
			}
			$result[] = $term;
		}
	}

	return $result;
}

function hocwp_post_get_first_term( $post_id = null, $taxonomy = 'category' ) {
	$post_id = hocwp_return_post( $post_id, 'id' );
	$terms   = wp_get_post_terms( $post_id, $taxonomy );
	if ( hocwp_array_has_value( $terms ) ) {
		$terms = current( $terms );
	}

	return $terms;
}

function hocwp_insert_post( $args = array() ) {
	$post_title   = '';
	$post_content = '';
	$post_status  = 'pending';
	$post_type    = 'post';
	$post_author  = 1;
	$first_admin  = hocwp_get_first_admin();
	if ( $first_admin ) {
		$post_author = $first_admin->ID;
	}
	$defaults           = array(
		'post_title'            => $post_title,
		'post_content'          => $post_content,
		'post_status'           => $post_status,
		'post_type'             => $post_type,
		'post_author'           => $post_author,
		'ping_status'           => get_option( 'default_ping_status' ),
		'post_parent'           => 0,
		'menu_order'            => 0,
		'to_ping'               => '',
		'pinged'                => '',
		'post_password'         => '',
		'guid'                  => '',
		'post_content_filtered' => '',
		'post_excerpt'          => '',
		'import_id'             => 0
	);
	$args               = wp_parse_args( $args, $defaults );
	$args['post_title'] = wp_strip_all_tags( $args['post_title'] );
	$post_id            = wp_insert_post( $args );

	return $post_id;
}

function hocwp_get_post_by_meta( $key, $value, $args = array() ) {
	$defaults = array(
		'post_type'      => 'any',
		'posts_per_page' => - 1,
		'meta_key'       => $key,
		'meta_value'     => $value
	);
	$args     = wp_parse_args( $args, $defaults );

	return hocwp_query( $args );
}

function hocwp_get_post_by_column( $column_name, $column_value, $output = 'OBJECT', $args = array() ) {
	global $wpdb;
	$post_type  = hocwp_get_value_by_key( $args, 'post_type' );
	$post_types = hocwp_sanitize_array( $post_type );
	$output     = strtoupper( $output );
	$sql        = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE $column_name = %s", $column_value );
	$count_type = 0;
	foreach ( $post_types as $post_type ) {
		if ( 0 == $count_type ) {
			$sql .= " AND post_type = '$post_type'";
		} else {
			$sql .= " OR post_type = '$post_type'";
		}
		$count_type ++;
	}
	$post_id = $wpdb->get_var( $sql );
	$result  = '';
	switch ( $output ) {
		case OBJECT:
			if ( hocwp_id_number_valid( $post_id ) ) {
				$result = get_post( $post_id );
			}
			break;
		default:
			$result = $post_id;
	}

	return $result;
}

function hocwp_find_post( $data, $post_type = 'post' ) {
	$result = null;
	if ( ! empty( $data ) ) {
		if ( hocwp_id_number_valid( $data ) ) {
			$temp = get_post( $data );
			if ( hocwp_is_post( $temp ) && $temp->post_type == $post_type ) {
				$result = $temp;
			}
		} else {
			$result = hocwp_get_post_by_slug( $data, $post_type );
		}
	}

	return $result;
}

function hocwp_get_post_id_by_slug( $slug ) {
	return hocwp_get_post_by_column( 'post_name', $slug, 'post_id' );
}

function hocwp_get_post_permalink_by_slug( $slug, $default = 'home' ) {
	$post = hocwp_get_post_by_slug( $slug );
	if ( is_a( $post, 'WP_Post' ) ) {
		return get_permalink( $post );
	}
	if ( 'home' == $default ) {
		$default = get_home_url();
	}

	return $default;
}

function hocwp_get_post_by_slug( $slug, $post_type = 'post' ) {
	$args  = array(
		'name'           => $slug,
		'post_type'      => $post_type,
		'posts_per_page' => 1
	);
	$query = hocwp_query( $args );

	return array_shift( $query->posts );
}

function hocwp_get_page_by_slug( $slug ) {
	return hocwp_get_post_by_slug( $slug, 'page' );
}

function hocwp_is_post( $post ) {
	return is_a( $post, 'WP_Post' );
}

function hocwp_get_author_posts_url() {
	global $authordata;
	if ( ! hocwp_object_valid( $authordata ) ) {
		return '';
	}

	return get_author_posts_url( $authordata->ID, $authordata->user_nicename );
}

function hocwp_get_post_meta( $meta_key, $post_id = null ) {
	$post_id = hocwp_return_post( $post_id, 'id' );

	return get_post_meta( $post_id, $meta_key, true );
}

function hocwp_get_post_comment_count( $post_id = null, $status = 'approved' ) {
	$post_id  = hocwp_return_post( $post_id, 'id' );
	$comments = get_comment_count( $post_id );

	return hocwp_get_value_by_key( $comments, $status );
}

function hocwp_get_post_parents( $post_id ) {
	$parents = get_post_ancestors( $post_id );
	$parents = array_reverse( $parents );

	return $parents;
}

function hocwp_get_post_first_parent( $post_id ) {
	$parents = hocwp_get_post_parents( $post_id );
	if ( hocwp_array_has_value( $parents ) ) {
		$post_id = array_shift( $parents );

		return get_post( $post_id );
	}

	return null;
}

function hocwp_vote_post_ajax_callback() {
	$result  = array(
		'success' => false
	);
	$post_id = isset( $_POST['post_id'] ) ? $_POST['post_id'] : '';
	$post_id = absint( $post_id );
	if ( $post_id > 0 ) {
		$type         = isset( $_POST['type'] ) ? $_POST['type'] : hocwp_get_method_value( 'vote_type' );
		$session_name = 'hocwp_vote_' . $type . '_post_' . $post_id;
		if ( ! isset( $_SESSION[ $session_name ] ) || 1 != $_SESSION[ $session_name ] ) {
			$value = isset( $_POST['value'] ) ? $_POST['value'] : '';
			$value = absint( $value );
			$value ++;
			if ( 'up' == $type || 'like' == $type ) {
				update_post_meta( $post_id, 'likes', $value );
				do_action( 'hocwp_add_trending_post', $post_id, 'like' );
			} elseif ( 'down' == $type || 'dislike' == $type ) {
				update_post_meta( $post_id, 'dislikes', $value );
			}
			$result['value']           = $value;
			$result['type']            = $type;
			$result['post_id']         = $post_id;
			$result['value_html']      = number_format( $value );
			$_SESSION[ $session_name ] = 1;
			$result['success']         = true;
		}
	}
	echo json_encode( $result );
	die();
}

add_action( 'wp_ajax_hocwp_vote_post', 'hocwp_vote_post_ajax_callback' );
add_action( 'wp_ajax_nopriv_hocwp_vote_post', 'hocwp_vote_post_ajax_callback' );

function hocwp_favorite_post_ajax_callback() {
	$result  = array(
		'html_data' => '',
		'success'   => false,
		'remove'    => false
	);
	$post_id = hocwp_get_method_value( 'post_id' );
	if ( hocwp_id_number_valid( $post_id ) && is_user_logged_in() ) {
		$user = wp_get_current_user();
		$type = hocwp_get_method_value( 'type' );
		if ( empty( $type ) ) {
			$type = 'favorite';
		}
		$action = hocwp_get_method_value( 'data_action' );
		if ( empty( $action ) ) {
			$action = 'do';
		}
		if ( 'favorite' == $type ) {
			$favorites = get_user_meta( $user->ID, 'favorite_posts', true );
			if ( ! is_array( $favorites ) ) {
				$favorites = array();
			}
			if ( ! in_array( $post_id, $favorites ) ) {
				$favorites[] = $post_id;
			} else {
				unset( $favorites[ array_search( $post_id, $favorites ) ] );
				$result['remove'] = true;
			}
			$updated = update_user_meta( $user->ID, 'favorite_posts', $favorites );
			if ( $updated ) {
				$result['success'] = true;
				$saved             = false;
				if ( ! $result['remove'] ) {
					$saved = true;
				}
				$params              = array(
					'post_id' => $post_id,
					'saved'   => $saved,
					'echo'    => false
				);
				$text                = hocwp_favorite_post_button_text( $params );
				$result['html_data'] = $text;
			}
		} elseif ( 'save' == $type ) {
			$result['success'] = hocwp_update_user_saved_posts( $user->ID, $post_id );
			$saved             = false;
			if ( 'do' == $action ) {
				$saved = true;
			}
			$params              = array(
				'post_id' => $post_id,
				'saved'   => $saved,
				'echo'    => false
			);
			$text                = hocwp_save_post_button_text( $params );
			$result['html_data'] = $text;
		}
		if ( 'undo' == $action ) {
			$result['remove'] = true;
		}
	}
	wp_send_json( $result );
}

add_action( 'wp_ajax_hocwp_favorite_post', 'hocwp_favorite_post_ajax_callback' );
add_action( 'wp_ajax_nopriv_hocwp_favorite_post', 'hocwp_favorite_post_ajax_callback' );
add_action( 'wp_ajax_hocwp_save_post', 'hocwp_favorite_post_ajax_callback' );
add_action( 'wp_ajax_nopriv_hocwp_save_post', 'hocwp_favorite_post_ajax_callback' );

function hocwp_update_post_temperature_ajax_callback() {
	$result  = array();
	$post_id = hocwp_get_method_value( 'post_id' );
	if ( hocwp_id_number_valid( $post_id ) ) {
		$type = hocwp_get_method_value( 'type' );
		hocwp_update_post_temperature( $type, $post_id );
	}
	wp_send_json( $result );
}

add_action( 'wp_ajax_hocwp_update_post_temperature', 'hocwp_update_post_temperature_ajax_callback' );
add_action( 'wp_ajax_nopriv_hocwp_update_post_temperature', 'hocwp_update_post_temperature_ajax_callback' );

function hocwp_notification_posts_ajax_callback() {
	$lists = get_option( 'hocwp_notification_posts' );
	$lists = hocwp_to_array( $lists );
	if ( hocwp_array_has_value( $lists ) ) {
		$query = hocwp_get_post_by_meta( 'subscriber_verified', 1, array( 'post_type' => 'hocwp_subscriber' ) );
		if ( $query->have_posts() ) {
			$subscribers    = $query->posts;
			$date           = hocwp_get_current_date();
			$transient_name = hocwp_build_transient_name( 'hocwp_notification_posts_table_%s', $date );
			if ( false === ( $table_content = get_transient( $transient_name ) ) ) {
				$table = new HOCWP_HTML( 'table' );
				$table->set_attribute( 'align', 'center' );
				$table->set_attribute( 'width', '100%' );
				$table->set_attribute( 'cellspacing', 0 );
				$table->set_attribute( 'border', 0 );
				$table->set_attribute( 'cellpadding', 0 );
				$table->set_attribute( 'bgcolor', '#ffffff' );
				$table_content = '';
				foreach ( $lists as $post_id ) {
					$obj       = get_post( $post_id );
					$post_type = get_post_type_object( $obj->post_type );
					if ( ! is_object( $post_type ) || ! $post_type->public ) {
						continue;
					}
					$notified = (bool) hocwp_get_post_meta( 'hocwp_notification_posts_sent', $post_id );
					if ( $notified ) {
						continue;
					}
					$inner_table = $table;
					$tbody       = new HOCWP_HTML( 'tbody' );
					$tr          = new HOCWP_HTML( 'tr' );
					$td          = new HOCWP_HTML( 'td' );
					$td->set_attribute( 'style', 'font-size:1px;line-height:1px' );
					$tr->set_text( $td );
					$tr2 = new HOCWP_HTML( 'tr' );
					$td2 = new HOCWP_HTML( 'td' );
					$td2->set_attribute( 'style', 'border-bottom:2px dotted #808285;padding-bottom:12px' );
					$a = new HOCWP_HTML( 'a' );
					$a->set_attribute( 'target', '_blank' );
					$a->set_href( get_permalink( $obj ) );
					$a->set_attribute( 'style', 'font-family: Helvetica,arial,sans-serif; font-size: 20px; color: rgb(22, 63, 144); text-align: left; font-weight: 500; text-decoration: none; line-height: 27px;' );
					$a->set_text( get_the_title( $obj ) );
					$p       = new HOCWP_HTML( 'p' );
					$excerpt = get_the_excerpt( $obj );
					if ( empty( $excerpt ) ) {
						$excerpt = $obj->post_content;
						$excerpt = strip_tags( $excerpt );
						$excerpt = apply_filters( 'excerpt_length', $excerpt, 150 );
					}
					$p->set_text( $excerpt );
					$td2->set_text( $a->build() . $p->build() );
					$tr2->set_text( $td2 );
					$tbody->set_text( $tr->build() . $tr2->build() );
					$inner_table->set_text( $tbody );
					$table_content .= $inner_table->build();
					update_post_meta( $post_id, 'hocwp_notification_posts_sent', 1 );
				}
				if ( ! empty( $table_content ) ) {
					$tbody = new HOCWP_HTML( 'tbody' );
					$tr    = new HOCWP_HTML( 'tr' );
					$td    = new HOCWP_HTML( 'td' );
					$td->set_text( $table_content );
					$tr->set_text( $td );
					$tbody->set_text( $tr );
					$table->set_text( $tbody );
					$table_content = $table->build();
				}
			}
			if ( ! empty( $table_content ) ) {
				foreach ( $subscribers as $subscriber ) {
					$email = hocwp_get_post_meta( 'subscriber_email', $subscriber->ID );
					if ( is_email( $email ) ) {
						$transient_name = 'hocwp_notification_posts_to_user_' . md5( $email );
						$transient_name = hocwp_build_transient_name( $transient_name . '_%s', $table_content );
						if ( false === get_transient( $transient_name ) ) {
							$subject = '[' . get_bloginfo( 'name' ) . '] New content updated on ' . hocwp_get_current_date( hocwp_get_date_format() );
							$message = $table_content;
							$message = hocwp_mail_unsubscribe_link_footer( $message, $email );
							$sent    = hocwp_send_html_mail( $email, $subject, $message );
							if ( $sent ) {
								set_transient( $transient_name, 1, DAY_IN_SECONDS );
							}
						}
					}
				}
			}
		}
	}
	exit;
}

add_action( 'wp_ajax_hocwp_notification_posts', 'hocwp_notification_posts_ajax_callback' );
add_action( 'wp_ajax_nopriv_hocwp_notification_posts', 'hocwp_notification_posts_ajax_callback' );

function hocwp_generate_slider_sortable_item_ajax_callback() {
	$result      = array();
	$max_item_id = hocwp_get_method_value( 'max_item_id' );
	$max_item_id = absint( $max_item_id );
	$media_url   = hocwp_get_method_value( 'media_url' );
	$media_id    = hocwp_get_method_value( 'media_id' );
	$media_id    = absint( $media_id );
	$item_html   = '<li data-item="' . $max_item_id . '">';
	$item_html .= '<img class="item-image" src="' . $media_url . '">';
	$item_html .= '<div class="item-info">';
	$item_html .= '<input type="text" placeholder="' . __( 'Title', 'hocwp-theme' ) . '" value="" class="item-title" name="slider_items[items][' . $max_item_id . '][title]">';
	$item_html .= '<input type="url" placeholder="' . __( 'Link for this item', 'hocwp-theme' ) . '" value="" class="item-link" name="slider_items[items][' . $max_item_id . '][link]">';
	$item_html .= '<textarea class="item-description" name="slider_items[items][' . $max_item_id . '][description]"></textarea>';
	$item_html .= '</div>';
	ob_start();
	?>
	<div class="clear"></div>
	<div class="advance">
		<div class="dashicons dashicons-editor-expand"></div>
		<div class="box-content">
			<div class="settings">
				<div class="col-left col50 hocwp-col">
					<?php
					$field_args = array(
						'name'  => 'slider_items[items][' . $max_item_id . '][background_color]',
						'label' => __( 'Background Color', 'hocwp-theme' ),
						'value' => ''
					);
					hocwp_field_color_picker( $field_args );
					?>
				</div>
				<div class="col-right col50 hocwp-col">

				</div>
			</div>
		</div>
	</div>
	<?php
	$item_html .= ob_get_clean();
	$item_html .= '<input type="hidden" class="item-image-url" name="slider_items[items][' . $max_item_id . '][image_url]" value="' . $media_url . '">';
	$item_html .= '<input type="hidden" class="item-image-id" name="slider_items[items][' . $max_item_id . '][image_id]" value="' . $media_id . '">';
	$item_html .= '<span title="' . __( 'Delete this item', 'hocwp-theme' ) . '" class="item-icon icon-delete icon-sortable-ui"></span>';
	$item_html .= '<span title="' . __( 'Re-order this item', 'hocwp-theme' ) . '" class="item-icon icon-drag icon-sortable-ui"></span>';
	$item_html .= '<span title="' . __( 'Add child item', 'hocwp-theme' ) . '" class="item-icon icon-add icon-sortable-ui"></span>';
	$item_html .= '</li>';
	$result['html_data'] = $item_html;
	wp_send_json( $result );
}

add_action( 'wp_ajax_hocwp_generate_slider_sortable_item', 'hocwp_generate_slider_sortable_item_ajax_callback' );

function hocwp_remove_slider_item_ajax_callback() {
	$result  = array();
	$item_id = isset( $_POST['item_id'] ) ? $_POST['item_id'] : 0;
	if ( $item_id > 0 ) {
		$post_id = isset( $_POST['post_id'] ) ? $_POST['post_id'] : 0;
		if ( $post_id > 0 ) {
			$value      = hocwp_get_slider_items( $post_id, true );
			$list_items = $value['items'];
			$item_order = $value['order'];
			unset( $list_items[ $item_id ] );
			$value['items'] = $list_items;
			$item_order     = hocwp_remove_array_item_by_value( $item_order, array( $item_id ) );
			$value['order'] = $item_order;
			update_post_meta( $post_id, 'slider_items', $value );
		}
	}
	wp_send_json( $result );
}

add_action( 'wp_ajax_hocwp_remove_slider_item', 'hocwp_remove_slider_item_ajax_callback' );