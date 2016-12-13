<?php
/**
 * Twitter Widget
 * Wordpress 2.8 and above
 * @see http://codex.wordpress.org/Widgets_API#Developing_Widgets
 */
class assez_twitter_widget extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 **/
	function __construct() {
		parent::__construct(
			'assez_twitter_widget',
			__( '[Assez] Twitter Stream', 'assez' ),
			array( 'description' => __( 'Display Twitter Feed', 'assez' ), )
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 *
	 */
	function widget( $args, $instance ) {
		extract( $args );
		
		$title			= apply_filters( 'widget_title', $instance['title'] );
		$username		= $instance['username'];
		$fetch			= $instance['fetch'];
		$key			= $instance['key'];
		$keysecret		= $instance['keysecret'];
		$token			= $instance['token'];
		$tokensecret	= $instance['tokensecret'];
		$show_ava		= isset( $instance['show_ava'] )? $instance['show_ava'] : 0;

		echo $before_widget;
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
?>
	
	<?php 
	$settings = array(
	    'oauth_access_token'		=> $token,
	    'oauth_access_token_secret'	=> $tokensecret,
	    'consumer_key'				=> $key,
	    'consumer_secret'			=> $keysecret
	);
	$url			= 'https://api.twitter.com/1.1/users/lookup.json';
	$getfield		= '?screen_name=' . $username;
	$requestMethod	= 'GET';

	$twitter = new TwitterAPIExchange( $settings );
	$response = $twitter->setGetfield( $getfield )
	    ->buildOauth( $url, $requestMethod )
	    ->performRequest();
	$userdata = json_decode( $response );
	$user = $userdata[0];
	
	$status_url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
	$status_getfield = '?screen_name=' . $username . '&count=' . $fetch;
	$status_requestMethod = 'GET';

	$status_twitter		= new TwitterAPIExchange( $settings );
	$status_response	= $status_twitter->setGetfield( $status_getfield )
	    ->buildOauth( $status_url, $status_requestMethod )
	    ->performRequest();
	$tweets = json_decode( $status_response );
	?>
	<div class="widget-body">
    <?php if ( $user ): ?>
		<?php if ( 1 == $show_ava ) : // show user avatar?>
        <div class="assez-twitter-info" style="background:url(<?php echo $user->profile_banner_url ?>/1500x500)">
            <div class="twitter-avatar">
                <?php if ( $user->profile_image_url ) : ?>
                <a href="https://twitter.com/<?php echo $username ?>" >
                    <img src="<?php echo $user->profile_image_url;?>" alt="<?php echo $username; ?>"/>
                </a>
                <?php endif; ?>
            </div>
            <div class="assez-twitter-name">
                <?php if( $user->name ) echo '<div class="assez-twitter-username"><a href="https://twitter.com/'.$username.'" >'.$user->name.'</a></div>'; ?>
                <a href="https://twitter.com/<?php echo $username; ?>" >@<?php echo $username; ?></a>
            </div>
            <div class="clearfix"></div>
        </div>
        <?php endif; // $show_ava ?>
	<?php endif; // $user ?>
	<?php 
	if( $tweets ) {
		$addclass = '';
		if( $show_ava != 1 ) {
			$addclass = 'twit_noinfo';
		}
		echo '<script type="text/javascript" src="//platform.twitter.com/widgets.js"></script>';
		echo '<div class="assez-twitter-list '. $addclass .'">';
		foreach( $tweets as $tweet ) {
			echo '<div class="assez-tweet-item">'."\n";
			// Echo Tweet
			if ( $tweet->text ) {
				$the_tweet = $tweet->text;

				// i. User_mentions must link to the mentioned user's profile.
				if ( $tweet->entities->user_mentions ) {
					foreach( $tweet->entities->user_mentions as $key => $user_mention ) {
						$the_tweet = preg_replace(
							'/@'.$user_mention->screen_name.'/i',
							'<a href="http://www.twitter.com/'.$user_mention->screen_name.'" target="_blank">@'.$user_mention->screen_name.'</a>',
							$the_tweet);
					}
				}

				// ii. Hashtags must link to a twitter.com search with the hashtag as the query.
				if ( $tweet->entities->hashtags ) {
					foreach( $tweet->entities->hashtags as $key => $hashtag ) {
						$the_tweet = preg_replace(
							'/#'.$hashtag->text.'/i',
							'<a href="https://twitter.com/search?q=%23'.$hashtag->text.'&src=hash" target="_blank">#'.$hashtag->text.'</a>',
							$the_tweet);
					}
				}

				// iii. Links in Tweet text must be displayed using the display_url
				//      field in the URL entities API response, and link to the original t.co url field.
				if ( is_array( $tweet->entities->urls ) ) {
					foreach( $tweet->entities->urls as $key => $link ) {
						$the_tweet = preg_replace(
							'`'.$link->url.'`',
							'<a href="'.$link->url.'" target="_blank">'.$link->url.'</a>',
							$the_tweet);
					}
				}

				echo '<div class="tweet-content">' . $the_tweet . '</div>';


				// === Tweet Actions ===
				//    Reply, Retweet, and Favorite action icons must always be visible for the user to interact with the Tweet. These actions must be implemented using Web Intents or with the authenticated Twitter API.
				//    No other social or 3rd party actions similar to Follow, Reply, Retweet and Favorite may be attached to a Tweet.
				// get the sprite or images from twitter's developers resource and update your stylesheet
				echo '
				<div class="twitter-intents">
					<p><a class="reply" href="https://twitter.com/intent/tweet?in_reply_to=' . $tweet->id_str . '"><i class="ti-share-alt"></i></a></p>
					<p><a class="retweet" href="https://twitter.com/intent/retweet?tweet_id=' . $tweet->id_str . '"><i class="ti-reload"></i></a></p>
					<p><a class="favorite" href="https://twitter.com/intent/favorite?tweet_id=' . $tweet->id_str . '"><i class="ti-heart"></i></a></p>
				</div>';


				// === Tweet Timestamp ===
				//    The Tweet timestamp must always be visible and include the time and date. e.g., “3:00 PM - 31 May 12”.
				// === Tweet Permalink ===
				//    The Tweet timestamp must always be linked to the Tweet permalink.
				echo '
				<p class="timestamp">
					<a href="https://twitter.com/' . $username . '/status/' . $tweet->id_str . '" target="_blank">
						' . date( 'h:i A M d', strtotime( $tweet->created_at . '- 8 hours' ) ) . '
					</a>
				</p><div class="clearfix"></div>';// -8 GMT for Pacific Standard Time;
			}
			echo '</div>';
		} // endforeach; $tweets
		echo '</div>';
	}
	?>
	</div>
<?php

echo $after_widget;
} // end widget()


	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	function update( $new_instance, $old_instance ) {

		// update logic goes here
		$updated_instance = $new_instance;

		$instance[ 'title' ]		= strip_tags( $new_instance[ 'title' ] );
		$instance[ 'username' ]		= strip_tags( $new_instance[ 'username' ] );
		$instance[ 'fetch' ]		= strip_tags( $new_instance[ 'fetch' ] );
		$instance[ 'key' ]			= strip_tags( $new_instance[ 'key' ] );
		$instance[ 'keysecret' ]	= strip_tags( $new_instance[ 'keysecret' ] );
		$instance[ 'token' ]		= strip_tags( $new_instance[ 'token' ] );
		$instance[ 'tokensecret' ]	= strip_tags( $new_instance[ 'tokensecret' ] );
		$instance[ 'show_ava' ]		= strip_tags( $new_instance[ 'show_ava' ] );

		return $updated_instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, 
			array( 
				'title'			=> NULL,
				'username'		=> NULL,
				'fetch'			=> NULL,
				'token'			=> NULL,
				'key'			=> NULL,
				'keysecret'		=> NULL,
				'token'			=> NULL,
				'tokensecret'	=> NULL,
				'show_ava'		=> NULL
			));
		$title			= esc_attr( $instance[ 'title' ] );
		$username		= esc_attr( $instance[ 'username' ] );
		$fetch			= esc_attr( $instance[ 'fetch' ] );
		$key			= esc_attr( $instance[ 'key' ] );
		$keysecret		= esc_attr( $instance[ 'keysecret' ] );
		$token			= esc_attr( $instance[ 'token' ] );
		$tokensecret	= esc_attr( $instance[ 'tokensecret' ] );
		$show_ava		= esc_attr( $instance[ 'show_ava' ] );
		?>
		<p>
        	<input id="<?php echo $this->get_field_id('show_ava'); ?>" name="<?php echo $this->get_field_name('show_ava'); ?>" type="checkbox" value="1" <?php checked( '1', $show_ava ); ?> />
			<label for="<?php echo $this->get_field_id('show_ava'); ?>"><?php _e('Show User\'s Info?', 'assez'); ?></label>
		</p>
		<h4><?php esc_html_e( 'Twitter API Settings', 'assez'); ?></h4>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">
			<?php esc_html_e( 'Title', 'assez') ?>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr($title); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('username'); ?>">
			<?php esc_html_e( 'Username', 'assez'); ?>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('username'); ?>" name="<?php echo $this->get_field_name('username'); ?>" value="<?php echo esc_attr($username); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('fetch'); ?>">
			<?php esc_html_e( 'Items to Fetch', 'assez' ); ?>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('fetch'); ?>" name="<?php echo $this->get_field_name('fetch'); ?>" value="<?php echo esc_attr($fetch); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('key'); ?>">
			<?php esc_html_e( 'Consumer Key (API Key)', 'assez'); ?>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('key'); ?>" name="<?php echo $this->get_field_name('key'); ?>" value="<?php echo esc_attr($key); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('keysecret'); ?>">
			<?php esc_html_e( 'Consumer Secret (API Secret)', 'assez'); ?>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('keysecret'); ?>" name="<?php echo $this->get_field_name('keysecret'); ?>" value="<?php echo esc_attr($keysecret); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('token'); ?>">
			<?php esc_html_e( 'Access Token', 'assez'); ?>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('token'); ?>" name="<?php echo $this->get_field_name('token'); ?>" value="<?php echo esc_attr($token); ?>" />
			</label>
		</p>
		 <p>
			<label for="<?php echo $this->get_field_id( 'tokensecret' ); ?>">
			<?php esc_html_e( 'Access Token Secret', 'assez'); ?>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'tokensecret' ); ?>" name="<?php echo $this->get_field_name('tokensecret'); ?>" value="<?php echo esc_attr( $tokensecret ); ?>" />
			</label>
		</p>
        <p><?php _e( 'These details are available in your <a href="https://dev.twitter.com/apps">Twitter dashboard</a>', 'assez' ); ?></p>
		<?php
	}
}

add_action( 'widgets_init', function(){
     register_widget( 'assez_twitter_widget' );
});