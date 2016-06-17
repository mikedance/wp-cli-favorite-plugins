<?php

if ( !defined( 'WP_CLI' ) ) return;

class MD_CLI_Plugin_Favorites extends WP_CLI_Command {

	/**
	 * List plugins a user has favorited in the WordPress.org plugins directory.
	 *
	 * ## OPTIONS
	 *
	 * <user>
	 * : The username of the wordpress.org account whose favorite plugins you are listing.
	 *
	 * [--slug]
	 * : Only return plugin slugs. Can be combined with `wp plugin install` (see examples).
	 *
	 * [--verbose]
	 * : Display more information about the plugins.
	 *
	 * ## EXAMPLES
	 *
	 *     wp plugin favorites matt
	 *     wp plugin favorites matt --verbose
	 *     wp plugin favorites matt --slug | xargs wp plugin install --activate
	 *     wp plugin favorites matt --slug | grep -vwE "(hello-dolly|bbpress)" | xargs wp plugin install --activate
	 *
	 * @synopsis <user> [--slug] [--verbose]
	 */
	public function __invoke( $args, $assoc_args ) {

		// prepare variables
		list( $user ) = $args;
		extract( $assoc_args = wp_parse_args( $assoc_args, array(
			'slug'    => false,
			'verbose' => false
		) ) );

		// get access to plugins_api
		require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

		// query wordpress.org
		$api = plugins_api( 'query_plugins', array(
			'user'   => $user,
			'fields' => array(
				'last_updated'    => true,
				'active_installs' => true
			)
		) );

		WP_CLI::log( print_r( $api, false ) );

		// only return slug?
		if ( $slug ) {
			foreach( $api->plugins as $plugin ) {
				WP_CLI::log( $plugin->slug );
			}
			return;
		}

		// get table columns
		$props = array(
			'name',
			'last_updated',
			'rating',
			'num_ratings',
			'active_installs'
		);
		if ( $verbose ) {
			$props = array_merge( $props, array(
				'author',
				'version',
				'requires',
				'tested',
				'short_description'
			) );
		}

		// pull object properties into an array
		$plugins = array();
		foreach( $api->plugins as $plugin ) {
			$args = array();
			foreach( $props as $prop ) {
				$args[$prop] = '';

				if ( isset( $plugin->{$prop} ) ) {
					$args[$prop] = $plugin->{$prop};

					// clean up some fields for output
					switch( $prop ) {
						case 'rating' :
							$args[$prop] = ( ( (int) $args['rating'] / 100 ) * 5 ) . '/5';
							break;
						case 'author' :
							$args[$prop] = strip_tags( $args['author'] );
							break;
						case 'last_updated' :
							$args[$prop] = date( 'Y-m-d', strtotime( $args['last_updated'] ) );
							break;
						case 'active_installs' :
							$args[$prop] = number_format( $args['active_installs'] );
							break;
					}
				}
			}

			$plugins[$plugin->slug] = $args;
		}

		if ( !$plugins ) {
			WP_CLI::log( 'No favorite plugins found.' );
			return;
		}

		// output as list table
		$formatter = new \WP_CLI\Formatter( $assoc_args, $props, 'plugin' );
		$formatter->display_items( $plugins );

	}

}
WP_CLI::add_command( 'plugin favorites', 'MD_CLI_Plugin_Favorites' );