<?php

/**
 * Twitter shortcode
 */
abstract class ctTwitterShortcodeBase extends ctShortcode {
	/**
	 * Returns name
	 * @return string|void
	 */
	public function getName() {
		return 'Twitter';
	}

	/**
	 * Shortcode name
	 * @return string
	 */
	public function getShortcodeName() {
		return 'twitter';
	}

	/**
	 * returns the follow link
	 *
	 * @param $user
	 *
	 * @return string
	 */
	protected function getFollowLink( $user ) {
		return "http://twitter.com/" . $user;
	}

	/**
	 * gets twitter news
	 *
	 * @param $user
	 * @param $limit
	 *
	 * @return stdClass[]
	 */
	protected function getTweets( $attributes ) {
		extract( $attributes );

		$tweets = array();
		$user   = str_replace( ' OR ', '%20OR%20', $user );



		if (function_exists('ct_get_context_option')){
			$token        = $token ? $token : ct_get_context_option( 'general_twit_token', '' );
			$token_secret = $token_secret ? $token_secret : ct_get_context_option( 'general_twit_token_secret', '' );
			$key          = $key ? $key : ct_get_context_option( 'general_twit_customer_key', '' );
			$secret       = $secret ? $secret : ct_get_context_option( 'general_twit_customer_secret', '' );
		}else{
			$token        = $token ? $token : ct_get_option( 'general_twit_token', '' );
			$token_secret = $token_secret ? $token_secret : ct_get_option( 'general_twit_token_secret', '' );
			$key          = $key ? $key : ct_get_option( 'general_twit_customer_key', '' );
			$secret       = $secret ? $secret : ct_get_option( 'general_twit_customer_secret', '' );
		}

		$json         = $this->fetchFromTwitter( $token,
			$token_secret,
			$key,
			$secret,
			'statuses/user_timeline.json?screen_name=' . $user . '&count=' . $limit . '&include_entities=true&include_rts=true',
			$cache );
		if ( $json ) {
			//errors - I guess it's auth error
			if ( isset( $json['errors'] ) ) {
				//display error message on WP_DEBUG
				$tweet          = new stdClass();
				$tweet->content = $json['errors'][0]['message'];
				$tweet->user    = '';
				$tweet->updated = time();

				return array( $tweet );
			}
			foreach ( $json as $tweetInfo ) {
				$content = $tweetInfo['text'];

				// parse URLs
				if ( $parseurl != 'plain' && isset( $tweetInfo['entities']['urls'] ) ) {
					foreach ( $tweetInfo['entities']['urls'] as $url ) {
						$orgLink     = $url['url'];
						$displayLink = $parseurl == 'display' ? $url['display_url'] : $orgLink;
						$content     = str_replace( $orgLink,
							'<a target="_blank" href="' . $orgLink . '">' . $displayLink . '</a>',
							$content );
					}
				}


				//parse media
				if ( isset( $tweetInfo['entities']['media'] ) ) {
					foreach ( $tweetInfo['entities']['media'] as $url ) {
						$orgLink     = $url['url'];
						$displayLink = $parsemedia == 'expanded' ? $url['expanded_url'] : ( $parsemedia == 'display' ? $url['display_url'] : $orgLink );
						if ( $parsemedia != 'plain' ) {
							$content = str_replace( $orgLink,
								'<a target="_blank" href="' . $orgLink . '">' . $displayLink . '</a>',
								$content );
						}

						//embed images
						if ( isset( $img ) && isset( $imgsize ) && $img == 'yes' && $url['type'] == 'photo' ) {
							$content .= '<br><a target="_blank" href="' . $orgLink . '"><img src="' . $url['media_url'] . ':' . $imgsize . '"></img></a>';
						}
					}
				}

				// parse @id
				if ( $parseid == 'yes' ) {
					$content = preg_replace( '/@(\w+)/',
						'@<a target="_blank" href="http://twitter.com/$1" class="at">$1</a>',
						$content );
				}

				// parse #hashtag
				if ( $parsehashtag == 'yes' ) {
					$content = preg_replace( '/\s#(\w+)/',
						' <a target="_blank" href="http://twitter.com/#!/search?q=%23$1" class="hashtag">#$1</a>',
						$content );
				}

				//max length of the content
				$content = (string) $content;
				if ( is_numeric( $maxlength ) && strlen( $content ) > $maxlength ) {
					$content = $this->truncate( $content, $maxlength, '...' );
				}

				$tweet          = new stdClass();
				$tweet->content = (string) $content;
				$tweet->user    = (string) $user;
				$tweet->updated = (int) strtotime( $tweetInfo['created_at'] );
				array_push( $tweets, $tweet );
				unset( $feed, $xml, $result, $tweet );
			}
		}

		return $tweets;
	}

	/**
	 * counts time ago
	 *
	 * @param $time
	 *
	 * @return string
	 */
	protected function ago( $time ) {
		$periods = array( "second", "minute", "hour", "day", "week", "month", "year", "decade" );
		$lengths = array( "60", "60", "24", "7", "4.35", "12", "10" );

		$now = time();

		$difference = $now - $time;

		for ( $j = 0; $difference >= $lengths[ $j ] && $j < count( $lengths ) - 1; $j ++ ) {
			$difference /= $lengths[ $j ];
		}

		$difference = round( $difference );

		if ( $difference != 1 ) {
			$periods[ $j ] .= "s";
		}

		$difference = $difference < 0 ? 0 : $difference;

		return $difference . " " . $periods[ $j ] . ' ' . __( 'ago', 'ct_theme' );
	}

	/**
	 * cuts the content
	 *
	 * @param $text
	 * @param $length
	 * @param string $suffix
	 * @param bool $isHTML
	 *
	 * @return mixed
	 */
	protected function truncate( $text, $length, $suffix = '&hellip;', $isHTML = true ) {
		$i          = 0;
		$simpleTags = array(
			'br'    => true,
			'hr'    => true,
			'input' => true,
			'image' => true,
			'link'  => true,
			'meta'  => true
		);
		$tags       = array();
		if ( $isHTML ) {
			preg_match_all( '/<[^>]+>([^<]*)/', $text, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER );
			foreach ( $m as $o ) {
				if ( $o[0][1] - $i >= $length ) {
					break;
				}
				$t = substr( strtok( $o[0][0], " \t\n\r\0\x0B>" ), 1 );
				// test if the tag is unpaired, then we mustn't save them
				if ( $t[0] != '/' && ( ! isset( $simpleTags[ $t ] ) ) ) {
					$tags[] = $t;
				} elseif ( end( $tags ) == substr( $t, 1 ) ) {
					array_pop( $tags );
				}
				$i += $o[1][1] - $o[0][1];
			}
		}

		// output without closing tags
		$output = substr( $text, 0, $length = min( strlen( $text ), $length + $i ) );
		// closing tags
		$output2 = ( count( $tags = array_reverse( $tags ) ) ? '</' . implode( '></', $tags ) . '>' : '' );

		// Find last space or HTML tag (solving problem with last space in HTML tag eg. <span class="new">)
		$array = preg_split( '/<.*>| /', $output, - 1, PREG_SPLIT_OFFSET_CAPTURE );
		$a     = end( $array );
		$pos   = (int) end( $a );
		// Append closing tags to output
		$output .= $output2;

		// Get everything until last space
		$one = substr( $output, 0, $pos );
		// Get the rest
		$two = substr( $output, $pos, ( strlen( $output ) - $pos ) );
		// Extract all tags from the last bit
		preg_match_all( '/<(.*?)>/s', $two, $tags );
		// Add suffix if needed
		if ( strlen( $text ) > $length ) {
			$one .= $suffix;
		}
		// Re-attach tags
		$output = $one . implode( $tags[0] );

		//added to remove  unnecessary closure
		$output = str_replace( '</!-->', '', $output );

		return $output;
	}

	/**
	 * Code below from http://stackoverflow.com/questions/12916539/simplest-php-example-retrieving-user-timeline-with-twitter-api-version-1-1 by Rivers
	 * with a few modfications by Mike Rogers to support variables in the URL nicely
	 */

	protected function buildBaseString( $baseURI, $method, $params ) {
		$r = array();
		ksort( $params );
		foreach ( $params as $key => $value ) {
			$r[] = "$key=" . rawurlencode( $value );
		}

		return $method . "&" . rawurlencode( $baseURI ) . '&' . rawurlencode( implode( '&', $r ) );
	}

	protected function buildAuthorizationHeader( $oauth ) {
		$r      = 'Authorization: OAuth ';
		$values = array();
		foreach ( $oauth as $key => $value ) {
			$values[] = "$key=\"" . rawurlencode( $value ) . "\"";
		}
		$r .= implode( ', ', $values );

		return $r;
	}

	/**
	 * Fetch data from Twitter
	 *
	 * @param $at
	 * @param $ats
	 * @param $cl
	 * @param $cs
	 * @param $url
	 *
	 * @return array
	 */

	protected function fetchFromTwitter( $at, $ats, $cl, $cs, $url, $cacheTime ) {
// The tokens, keys and secrets from the app you created at https://dev.twitter.com/apps
		$config   = array(
			'oauth_access_token'        => $at,
			'oauth_access_token_secret' => $ats,
			'consumer_key'              => $cl,
			'consumer_secret'           => $cs,
			'use_whitelist'             => false, // If you want to only allow some requests to use this script.
			'base_url'                  => 'https://api.twitter.com/1.1/'
		);
		$cacheKey = 'ct_twitter_' . md5( $at . $ats . $cl . $cs . $url );

		$result = array();
		if ( $cacheTime ) {
			$result = get_transient( $cacheKey );
		}

		if ( ! $result ) {

// Figure out the URL parmaters
			$url_parts = parse_url( $url );
			parse_str( @$url_parts['query'], $url_arguments );

			$full_url = $config['base_url'] . $url; // Url with the query on it.
			$base_url = $config['base_url'] . $url_parts['path']; // Url without the query.


// Set up the oauth Authorization array
			$oauth = array(
				'oauth_consumer_key'     => $config['consumer_key'],
				'oauth_nonce'            => time(),
				'oauth_signature_method' => 'HMAC-SHA1',
				'oauth_token'            => $config['oauth_access_token'],
				'oauth_timestamp'        => time(),
				'oauth_version'          => '1.0'
			);

			$base_info                = $this->buildBaseString( $base_url,
				'GET',
				array_merge( $oauth, $url_arguments ) );
			$composite_key            = rawurlencode( $config['consumer_secret'] ) . '&' . rawurlencode( $config['oauth_access_token_secret'] );
			$oauth_signature          = base64_encode( hash_hmac( 'sha1', $base_info, $composite_key, true ) );
			$oauth['oauth_signature'] = $oauth_signature;

// Make Requests
			$header  = array(
				$this->buildAuthorizationHeader( $oauth ),
				'Expect:'
			);
			$options = array(
				CURLOPT_HTTPHEADER     => $header,
				//CURLOPT_POSTFIELDS => $postfields,
				CURLOPT_HEADER         => false,
				CURLOPT_URL            => $full_url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false
			);

			$feed = curl_init();
			curl_setopt_array( $feed, $options );
			$result = curl_exec( $feed );
		}

		if ( $cacheTime && $result ) {
			set_transient( $cacheKey, $result, $cacheTime );
		}

		return $result ? json_decode( $result, true ) : false;
	}

	/**
	 * Returns config
	 * @return null
	 */
	public function getAttributes() {
		return array(
			'user'         => array(
				'label'   => __( 'username', 'ct_theme' ),
				'default' => '',
				'type'    => 'input',
				'help'    => __( "Twitter username", 'ct_theme' )
			),
			'key'          => array(
				'label'   => __( 'customer key', 'ct_theme' ),
				'default' => (function_exists('ct_get_context_option')?ct_get_context_option( 'general_twit_customer_key', '' ):ct_get_option( 'general_twit_customer_key', '' )),
				'type'    => 'input',
				'help'    => __( "Customer key", 'ct_theme' )
			),
			'secret'       => array(
				'label'   => __( 'customer secret', 'ct_theme' ),
				'default' => (function_exists('ct_get_context_option')?ct_get_context_option( 'general_twit_customer_secret', '' ):ct_get_option( 'general_twit_customer_secret', '' )),
				'type'    => 'input',
				'help'    => __( "Customer secret", 'ct_theme' )
			),
			'token'        => array(
				'label'   => __( 'token', 'ct_theme' ),
				'default' => (function_exists('ct_get_context_option')?ct_get_context_option( 'general_twit_token', '' ):ct_get_option( 'general_twit_token', '' )),
				'type'    => 'input',
				'help'    => __( "Access token", 'ct_theme' )
			),
			'token_secret' => array(
				'label'   => __( 'token secret', 'ct_theme' ),
				'default' => (function_exists('ct_get_context_option')?ct_get_context_option( 'general_twit_token_secret', '' ):ct_get_option( 'general_twit_token_secret', '' )),
				'type'    => 'input',
				'help'    => __( "Access token secret", 'ct_theme' )
			),
			'limit'        => array(
				'label'   => __( 'limit', 'ct_theme' ),
				'default' => '2',
				'type'    => 'input',
				'help'    => __( "Limit news", 'ct_theme' )
			),
			'button'       => array(
				'label'   => __( "follow us button", 'ct_theme' ),
				'default' => __( 'Follow us', 'ct_theme' ),
				'type'    => 'input',
				'help'    => __("Follow us button label. Leave blank to hide it",'ct_theme'),
				'ct_theme'
			),
			'newwindow'    => array(
				'label'   => __( "new window?", 'ct_theme' ),
				'default' => 'false',
				'type'    => 'checkbox',
				'help'    => __("Open in new window follow us button?",'ct_theme'),
				'ct_theme'
			),
			'parseurl'     => array(
				'label'   => __( 'parse url', 'ct_theme' ),
				'default' => 'short',
				'type'    => 'select',
				'choices' => array(
					'plain'   => __( 'plain text', 'ct_theme' ),
					'short'   => __( 'short link', 'ct_theme' ),
					'display' => __( 'display link', 'ct_theme' )
				),
				'help'    => __( "You can display links from the content as plain text, short html links or full html links",
					'ct_theme' )
			),
			'parsemedia'   => array(
				'label'   => __( 'parse media', 'ct_theme' ),
				'default' => 'short',
				'type'    => 'select',
				'choices' => array(
					'plain'    => __( 'plain text', 'ct_theme' ),
					'short'    => __( 'short link', 'ct_theme' ),
					'display'  => __( 'display link', 'ct_theme' ),
					'expanded' => __( 'expanded link', 'ct_theme' )
				),
				'help'    => __( "You can display media links from the content as plain text or 3 types of html links",
					'ct_theme' )
			),
			'parseid'      => array(
				'label'   => __( 'parse user id?', 'ct_theme' ),
				'default' => 'yes',
				'type'    => 'select',
				'choices' => array(
					'yes' => __( 'yes', 'ct_theme' ),
					'no'  => __( 'no', 'ct_theme' )
				),
				'help'    => __( "Display user @ids as plain text or links", 'ct_theme' )
			),
			'parsehashtag' => array(
				'label'   => __( 'parse hashtag?', 'ct_theme' ),
				'default' => 'yes',
				'type'    => 'select',
				'choices' => array(
					'yes' => __( 'yes', 'ct_theme' ),
					'no'  => __( 'no', 'ct_theme' )
				),
				'help'    => __( "Display #hashtags as plain text or links", 'ct_theme' )
			),
			'img'          => array(
				'label'   => __( 'embed images?', 'ct_theme' ),
				'default' => 'no',
				'type'    => 'select',
				'choices' => array(
					'yes' => __( 'yes', 'ct_theme' ),
					'no'  => __( 'no', 'ct_theme' )
				),
				'help'    => __( "Embed images into posts content?", 'ct_theme' )
			),
			'imgsize'      => array(
				'label'   => __( 'size of embeded images?', 'ct_theme' ),
				'default' => 'thumb',
				'type'    => 'select',
				'choices' => array(
					'thumb'  => __( 'thumb', 'ct_theme' ),
					'small'  => __( 'small', 'ct_theme' ),
					'medium' => __( 'medium', 'ct_theme' ),
					'large'  => __( 'large', 'ct_theme' )
				),
				'help'    => __( "Embedded image size", 'ct_theme' )
			),
			'maxlength'    => array(
				'label'   => __( 'tweet length limit', 'ct_theme' ),
				'default' => '',
				'type'    => 'input',
				'help'    => __( "Max length of the tweet", 'ct_theme' )
			),
			'cache'        => array(
				'label'   => __( 'cache results for X seconds', 'ct_theme' ),
				'default' => '0',
				'type'    => 'input',
				'help'    => __( "Cache Twitter feeds for better performance ex. 900 = 15 minutes. 0 - disabled",
					'ct_theme' )
			)
		);
	}
}