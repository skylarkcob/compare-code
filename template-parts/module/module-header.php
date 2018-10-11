<div class="header-bar">
	<div class="logo-area fl">
		<div class="<?php hocwp_wrap_class(); ?>">
			<?php hocwp_theme_the_logo(); ?>
			<div class="pull-right">
				<?php hocwp_show_ads( 'leaderboard' ); ?>
			</div>
			<div class="search-box fl">
				<form id="siteSearch" class="siteSearch fl floatLeft r5px search-form"
				      action="<?php echo home_url( '/' ); ?>"
				      method="get">
					<fieldset>
						<input id="siteSearchField" class="toggleTitle toggleval search-field toggleValDefault"
						       type="search" placeholder="<?php _e( 'Search for fonts...', 'hocwp-theme' ); ?>"
						       name="s" size="16" maxlength="50" value="">
						<input type="image" src="<?php echo get_template_directory_uri() . '/images/zoom.png'; ?>"
						       alt="<?php _e( 'GO', 'hocwp-theme' ); ?>">
					</fieldset>
				</form>
			</div>
		</div>
	</div>
	<div class="fl head-more">
		<ul class="clearfix">
			<li class="search-button-item">
				<a id="searchIcon" href="#" class="headerButton search xs" aria-describedby="ui-tooltip-0"></a>
			</li>
			<li id="browseFontsButton" class="categories-item">
				<a href="#" class="headerButton categories xs">
					<em><?php _e( 'Font Categories', 'hocwp-theme' ); ?></em>
				</a>
			</li>
			<?php
			if ( is_user_logged_in() ) {
				$user = wp_get_current_user();
				?>
				<li class="profile-item">
					<a class="" href="<?php echo get_edit_profile_url(); ?>">
						<?php echo get_avatar( $user->user_email ); ?>
						<em><?php echo $user->display_name; ?></em>
					</a>
					<ul class="level-2 list-reset user-menu sub-menu" data-test="true" aria-hidden="true" role="menu">
						<li>
							<a href="<?php echo get_edit_profile_url(); ?>"><?php _e( 'Edit Profile', 'hocwp-theme' ); ?></a>
						</li>
						<?php
						if ( function_exists( 'Pixelify' ) ) {
							$dp = Pixelify()->get_dashboard_page();

							if ( $dp instanceof WP_Post ) {
								$per = get_permalink( $dp );
								?>
								<li>
									<a href="<?php echo add_query_arg( 'task', 'products', $per ); ?>"><?php _e( 'Your Products', 'hocwp-theme' ); ?></a>
								</li>
								<li>
									<a href="<?php echo add_query_arg( 'task', 'new-product', $per ); ?>"><?php _e( 'Add New Product', 'hocwp-theme' ); ?></a>
								</li>
								<li>
									<a href="<?php echo add_query_arg( 'task', 'your-downloads', $per ); ?>"><?php _e( 'Your Downloads', 'hocwp-theme' ); ?></a>
								</li>
								<li>
									<a href="<?php echo add_query_arg( 'task', 'collections', $per ); ?>"><?php _e( 'Collections', 'hocwp-theme' ); ?></a>
								</li>
								<li>
									<a href="<?php echo add_query_arg( 'task', 'followed-artists', $per ); ?>"><?php _e( 'Followed Artists', 'hocwp-theme' ); ?></a>
								</li>
								<?php
							}
						}
						?>
						<li>
							<a href="<?php echo wp_logout_url(); ?>"><?php _e( 'Sign out', 'hocwp-theme' ); ?></a>
						</li>


					</ul>
				</li>
				<?php
				if ( function_exists( 'Pixelify' ) ) {
					$dp = Pixelify()->get_dashboard_page();

					if ( $dp instanceof WP_Post ) {
						$url = get_permalink( $dp );
						$url = add_query_arg( 'task', 'collections', $url );
						?>
						<li>
							<a class="headerButton favorites xs"
							   href="<?php echo $url; ?>">
								<em><?php _e( 'Favorites', 'hocwp-theme' ); ?></em>
							</a>
						</li>
						<?php
					}
				}
			} else {
				?>
				<li class="signin-item">
					<a class="headerButton signIn" href="<?php echo wp_login_url(); ?>">
						<em><?php _e( 'Sign in', 'hocwp-theme' ); ?></em>
					</a>
				</li>
				<?php
				$can_regis = get_option( 'users_can_register' );

				if ( 1 == $can_regis ) {
					$url = wp_registration_url();
					?>
					<li class="register-item">
						<a class="headerButton register" href="<?php echo $url; ?>">
							<i class="fa fa-user-plus" aria-hidden="true"></i>
							<em><?php _e( 'Sign up', 'hocwp-theme' ); ?></em>
						</a>
					</li>
					<?php
				}
			}
			?>
		</ul>
	</div>
	<div id="widgetsMenu" class="menu-widgets clear clearfix" style="display: none">
		<div class="col1 col">
			<?php dynamic_sidebar( 'font_categories_1' ); ?>
		</div>
		<div class="col2 col">
			<?php dynamic_sidebar( 'font_categories_2' ); ?>
		</div>
		<div class="col3 col">
			<?php dynamic_sidebar( 'font_categories_3' ); ?>
		</div>
		<div class="col4 col">
			<?php dynamic_sidebar( 'font_categories_4' ); ?>
		</div>
		<div class="col5 col">
			<?php dynamic_sidebar( 'font_categories_5' ); ?>
		</div>
		<div class="col6 col">
			<?php dynamic_sidebar( 'font_categories_6' ); ?>
		</div>
	</div>
	<div class="menu-search-area">
		<div class="<?php hocwp_wrap_class( 'inner' ); ?>">
			<div class="menu-search">
				<div class="menu pull-left primary-menus">
					<?php hocwp_theme_the_menu( array( 'theme_location' => 'primary' ) ); ?>
				</div>
				<div class="pull-right search">
					<?php
					$args = array(
						'search_icon' => false,
						'submit_text' => __( 'Search', 'hocwp-theme' )
					);
					hocwp_search_form( $args );
					?>
				</div>
			</div>
		</div>
	</div>
	<div class="bread-text">
		<div class="<?php hocwp_wrap_class(); ?>">
			<div class="fl">
				<?php
				if ( is_home() ) {
					?>
					<a href="<?php echo home_url( '/' ); ?>"><?php _e( 'Home', 'hocwp-theme' ); ?></a>
					<span class="divider">&gt;</span>
					<span><?php _e( 'New &amp; Fresh Fonts', 'hocwp-theme' ); ?></span>
					<?php
				} else {
					hocwp_breadcrumb();
				}
				?>
			</div>
			<div class="fr breadcrumb-text">
				<?php
				$text = hocwp_theme_get_option( 'breadcrumb_text' );

				if ( ! empty( $text ) ) {
					echo wpautop( $text );
				}
				?>
			</div>
		</div>
	</div>
</div>