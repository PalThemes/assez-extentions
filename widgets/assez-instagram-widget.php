<?php
/**
 *
 * Instagram Widget
 * Since 1.0.0
*/

class assez_instagram_widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'assez-instagram',
			__( '[Assez] Instagram', 'assez' ),
			array( 'classname' => 'assez-instagram', 'description' => __( 'Display Instagram Feed', 'assez' ), )
		);
	}

	function widget( $args, $instance ) {
		extract( $args );
		
		$title		= empty( $instance['title'] ) ? '' : apply_filters( 'widget_title', $instance['title'] );
		$username	= empty( $instance['username'] ) ? '' : $instance['username'];
		$col		= empty( $instance['col'] ) ? 3 : $instance['col'];
		$limit		= empty( $instance['number'] ) ? 9 : $instance['number'];
		$size		= empty( $instance['size'] ) ? 'large' : $instance['size'];
		$target		= empty( $instance['target'] ) ? '_self' : $instance['target'];
		$link		= empty( $instance['link'] ) ? '' : $instance['link'];
		$show_likes_comments	= isset( $instance['show_likes_comments'] )? $instance['show_likes_comments'] : 0;

		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}
		if ( '' != $username ) {
			echo '<div class="assez-instagram-body">';

			$media_array = $this->scrape_instagram( $username, $limit );

			if ( is_wp_error( $media_array ) ) {

				echo wp_kses_post( $media_array->get_error_message() );

			} else {

				// filter for images only?
				if ( $images_only = apply_filters( 'assez_images_only', FALSE ) ) {
					$media_array = array_filter( $media_array, array( $this, 'images_only' ) );
				}

				// filters for custom classes
				$ulclass	= apply_filters( 'assez_list_class', 'instagram-pics instagram-size-' . $size );
				$liclass	= apply_filters( 'assez_item_class', 'col-' . $col );
				$aclass		= apply_filters( 'assez_a_class', '' );
				$imgclass	= apply_filters( 'assez_img_class', 'lazy' );

				?>
                <ul class="<?php echo esc_attr( $ulclass ); ?>"><?php
				foreach ( $media_array as $item ) {
					if ( $show_likes_comments ) {
						$likes_comments = '<span class="likes-count">' . esc_html( number_format( $item['likes'] ) ) . '</span>';
						$likes_comments .= '<span class="comments-count">' . esc_html( number_format( $item['comments'] ) ) . '</span>';
					}
					echo '
					<li class="'. esc_attr( $liclass ) .'">
						<a href="'. esc_url( $item['link'] ) .'" target="'. esc_attr( $target ) .'"  class="'. esc_attr( $aclass ) .'">
							' . $likes_comments . '
							<img src="'. esc_url( $item[$size] ) .'"  alt="'. esc_attr( $item['description'] ) .'" title="'. esc_attr( $item['description'] ).'"  class="'. esc_attr( $imgclass ) .'"/>
						</a>
					</li>';
				}
				?>
                </ul>
                <div class="clearfix"></div>
				<?php
			}
			$linkclass		= apply_filters( 'assez_link_class', 'clearfix' );
			$buttonclass	= apply_filters( 'assez_button_class', 'follow-button' );
	
			if ( '' != $link ) {
				?><a class="<?php echo esc_attr( $buttonclass ); ?>" href="//instagram.com/<?php echo esc_attr( trim( $username ) ); ?>" rel="me" target="<?php echo esc_attr( $target ); ?>"><?php echo wp_kses_post( $link ); ?></a><?php
			}			
			echo '</div>'; // assez-instagram-body
		} // endif '' != $username

		do_action( 'assez_after_widget', $instance );

		echo $args['after_widget'];
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => __( 'Instagram', 'assez' ), 'username' => '', 'size' => 'large', 'link' => __( 'Follow Me!', 'assez' ), 'number' => 9, 'col' => 3, 'target' => '_self' ) );
		$title		= isset($instance['title'])?$instance['title']:NULL;
		$username	= isset($instance['username'])?$instance['username']:NULL;
		$number		= isset($instance['number'])?absint( $instance['number'] ):NULL;
		$col		= isset($instance['col'])?absint( $instance['col'] ):NULL;
		$size		= isset($instance['size'])?$instance['size']:NULL;
		$target		= isset($instance['target'])?$instance['target']:NULL;
		$link		= isset($instance['link'])?$instance['link']:NULL;
		$show_likes_comments	= isset($instance[ 'show_likes_comments' ])?esc_attr( $instance[ 'show_likes_comments' ] ):0;
		?>
		<p>
        	<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'assez' ); ?>: 
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
            </label>
        </p>
		<p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'username' ) ); ?>"><?php esc_html_e( 'Username', 'assez' ); ?>: 
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'username' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'username' ) ); ?>" type="text" value="<?php echo esc_attr( $username ); ?>" />
            </label>
        </p>
		<p>
        	<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Number of photos', 'assez' ); ?>: 
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>" />
            </label>
        </p>
        <p>
        	<label for="<?php echo esc_attr( $this->get_field_id( 'col' ) ); ?>"><?php esc_html_e( 'Columns', 'assez' ); ?>:</label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'col' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'col' ) ); ?>" class="widefat">
				<option value="2" <?php selected( 2, $col ) ?>><?php esc_html_e( '2', 'assez' ); ?></option>
                <option value="3" <?php selected( 3, $col ) ?>><?php esc_html_e( '3', 'assez' ); ?></option>
                <option value="4" <?php selected( 4, $col ) ?>><?php esc_html_e( '4', 'assez' ); ?></option>
                <option value="5" <?php selected( 5, $col ) ?>><?php esc_html_e( '5', 'assez' ); ?></option>
                <option value="6" <?php selected( 6, $col ) ?>><?php esc_html_e( '6', 'assez' ); ?></option>
                <option value="7" <?php selected( 7, $col ) ?>><?php esc_html_e( '7', 'assez' ); ?></option>
                <option value="8" <?php selected( 8, $col ) ?>><?php esc_html_e( '8', 'assez' ); ?></option>
			</select>
		</p>
		<p>
        	<label for="<?php echo esc_attr( $this->get_field_id( 'size' ) ); ?>"><?php esc_html_e( 'Photo size', 'assez' ); ?>:</label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'size' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'size' ) ); ?>" class="widefat">
				<option value="thumbnail" <?php selected( 'thumbnail', $size ) ?>><?php esc_html_e( 'Thumbnail', 'assez' ); ?></option>
				<option value="small" <?php selected( 'small', $size ) ?>><?php esc_html_e( 'Small', 'assez' ); ?></option>
				<option value="large" <?php selected( 'large', $size ) ?>><?php esc_html_e( 'Large', 'assez' ); ?></option>
				<option value="original" <?php selected( 'original', $size ) ?>><?php esc_html_e( 'Original', 'assez' ); ?></option>
			</select>
		</p>
		<p>
        	<label for="<?php echo esc_attr( $this->get_field_id( 'target' ) ); ?>"><?php esc_html_e( 'Open links in', 'assez' ); ?>:</label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'target' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'target' ) ); ?>" class="widefat">
				<option value="_self" <?php selected( '_self', $target ) ?>><?php esc_html_e( 'Current window (_self)', 'assez' ); ?></option>
				<option value="_blank" <?php selected( '_blank', $target ) ?>><?php esc_html_e( 'New window (_blank)', 'assez' ); ?></option>
			</select>
		</p>
		<p>
        	<label for="<?php echo esc_attr( $this->get_field_id( 'link' ) ); ?>"><?php esc_html_e( 'Link text', 'assez' ); ?>: 
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'link' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'link' ) ); ?>" type="text" value="<?php echo esc_attr( $link ); ?>" />
            </label>
        </p>
        <p>
        	<input id="<?php echo $this->get_field_id('show_likes_comments'); ?>" name="<?php echo $this->get_field_name('show_likes_comments'); ?>" type="checkbox" value="1" <?php checked( '1', $show_likes_comments ); ?> />
            <label for="<?php echo $this->get_field_id('show_likes_comments'); ?>">
				<?php esc_html_e('Show Likes & Comments Count?', 'assez'); ?>
            </label>
        </p>
		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']		= strip_tags( $new_instance['title'] );
		$instance['username']	= trim( strip_tags( $new_instance['username'] ) );
		$instance['number']		= ! absint( $new_instance['number'] ) ? 9 : $new_instance['number'];
		$instance['col']		= ! absint( $new_instance['col'] ) ? 3 : $new_instance['col'];
		$instance['size']		= ( ( $new_instance['size'] == 'thumbnail' || $new_instance['size'] == 'large' || $new_instance['size'] == 'small' || $new_instance['size'] == 'original' ) ? $new_instance['size'] : 'large' );
		$instance['target']		= ( ( $new_instance['target'] == '_self' || $new_instance['target'] == '_blank' ) ? $new_instance['target'] : '_self' );
		$instance['link']		= strip_tags( $new_instance['link'] );
		$instance['show_likes_comments']	= strip_tags( $new_instance[ 'show_likes_comments' ] );
		return $instance;
	}

	// based on https://gist.github.com/cosmocatalano/4544576
	function scrape_instagram( $username ) {

		$username = strtolower( $username );
		$username = str_replace( '@', '', $username );

		if ( false === ( $instagram = get_transient( 'instagram-a5-'.sanitize_title_with_dashes( $username ) ) ) ) {

			$remote = wp_remote_get( 'http://instagram.com/'.trim( $username ) );

			if ( is_wp_error( $remote ) )
				return new WP_Error( 'site_down', esc_html__( 'Unable to communicate with Instagram.', 'assez' ) );

			if ( 200 != wp_remote_retrieve_response_code( $remote ) )
				return new WP_Error( 'invalid_response', esc_html__( 'Instagram did not return a 200.', 'assez' ) );

			$shards = explode( 'window._sharedData = ', $remote['body'] );
			$insta_json = explode( ';</script>', $shards[1] );
			$insta_array = json_decode( $insta_json[0], TRUE );

			if ( ! $insta_array )
				return new WP_Error( 'bad_json', esc_html__( 'Instagram has returned invalid data.', 'assez' ) );

			if ( isset( $insta_array['entry_data']['ProfilePage'][0]['user']['media']['nodes'] ) ) {
				$images = $insta_array['entry_data']['ProfilePage'][0]['user']['media']['nodes'];
			} else {
				return new WP_Error( 'bad_json_2', esc_html__( 'Instagram has returned invalid data.', 'assez' ) );
			}

			if ( ! is_array( $images ) )
				return new WP_Error( 'bad_array', esc_html__( 'Instagram has returned invalid data.', 'assez' ) );

			$instagram = array();

			foreach ( $images as $image ) {

				$image['thumbnail_src'] = preg_replace( '/^https?\:/i', '', $image['thumbnail_src'] );
				$image['display_src'] = preg_replace( '/^https?\:/i', '', $image['display_src'] );

				// handle both types of CDN url
				if ( (strpos( $image['thumbnail_src'], 's640x640' ) !== false ) ) {
					$image['thumbnail'] = str_replace( 's640x640', 's160x160', $image['thumbnail_src'] );
					$image['small'] = str_replace( 's640x640', 's320x320', $image['thumbnail_src'] );
				} else {
					$urlparts = wp_parse_url( $image['thumbnail_src'] );
					$pathparts = explode( '/', $urlparts['path'] );
					array_splice( $pathparts, 3, 0, array( 's160x160' ) );
					$image['thumbnail'] = '//' . $urlparts['host'] . implode('/', $pathparts);
					$pathparts[3] = 's320x320';
					$image['small'] = '//' . $urlparts['host'] . implode('/', $pathparts);
				}

				$image['large'] = $image['thumbnail_src'];

				if ( $image['is_video'] == true ) {
					$type = 'video';
				} else {
					$type = 'image';
				}

				$caption = __( 'Instagram Image', 'assez' );
				if ( ! empty( $image['caption'] ) ) {
					$caption = $image['caption'];
				}

				$instagram[] = array(
					'description'   => $caption,
					'link'		  	=> trailingslashit( '//instagram.com/p/' . $image['code'] ),
					'time'		  	=> $image['date'],
					'comments'	  	=> $image['comments']['count'],
					'likes'		 	=> $image['likes']['count'],
					'thumbnail'	 	=> $image['thumbnail'],
					'small'			=> $image['small'],
					'large'			=> $image['large'],
					'original'		=> $image['display_src'],
					'type'		  	=> $type
				);
			}

			// do not set an empty transient - should help catch private or empty accounts
			if ( ! empty( $instagram ) ) {
				$instagram = base64_encode( serialize( $instagram ) );
				set_transient( 'instagram-a5-'.sanitize_title_with_dashes( $username ), $instagram, apply_filters( 'null_instagram_cache_time', HOUR_IN_SECONDS*2 ) );
			}
		}

		if ( ! empty( $instagram ) ) {

			return unserialize( base64_decode( $instagram ) );

		} else {

			return new WP_Error( 'no_images', esc_html__( 'Instagram did not return any images.', 'assez' ) );

		}
	}

	function images_only( $media_item ) {

		if ( $media_item['type'] == 'image' )
			return true;

		return false;
	}
}
add_action( 'widgets_init', function() {
	register_widget( 'assez_instagram_widget' );
} );
