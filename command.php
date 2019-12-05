<?php

if ( !defined( 'WP_CLI' ) || !WP_CLI ) return;

class MD_CLI_Plugin_Favorites extends WP_CLI_Command {

	/**
	 * List plugins a user has favorited in the WordPress.org plugins directory.
	 *
	 * ## OPTIONS
	 *
	 * <user>
	 * : The username of the wordpress.org account whose favorite plugins you are listing.
	 *
	 * [--verbose]
	 * : Display much more information about each plugin.
	 *
	 * ## EXAMPLES
	 *
	 *     wp plugin favorites matt
	 *     wp plugin favorites matt --verbose
	 *     wp plugin favorites matt | xargs wp plugin install --activate
	 *     wp plugin favorites matt | grep -vwE "(hello-dolly|bbpress)" | xargs wp plugin install --activate
	 *
	 * @synopsis <user> [--verbose]
	 */
	public function __invoke( $args, $assoc_args ) {

		// prepare variables
		list( $user ) = $args;
		extract( $assoc_args = wp_parse_args( $assoc_args, array(
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

		$plugins = isset( $api->plugins ) ? $api->plugins : false;

		if ( !$plugins ) {
			WP_CLI::log( 'No favorite plugins found.' );
			return;
		}

		if ( $verbose ) {
			$this->verbose( $plugins );
			return;
		}

		foreach( $plugins as $plugin ) {
			$plugin = (object) $plugin;
			WP_CLI::log( $plugin->slug );
		}

	}

	/**
	 * Output a favorite plugins list verbosely. Display a table with most of the data available
	 * from WordPress.org.
	 *
	 * @param object $plugins Result of the plugins_api() call.
	 */
	private function verbose( $plugins ) {

		$props = array(
			'name'            => 'Name',
			'author'          => 'Author',
			'last_updated'    => 'Updated (Version)',
			'rating'          => 'Rating (#)',
			'active_installs' => 'Active Installs',
			'requires'        => 'Requires/Tested'
		);

		$rows = array();
		foreach( $plugins as $plugin ) {
			$plugin = (object) $plugin;
			$columns = array();
			foreach( $props as $prop => $label ) {

				$columns[$label] = '';

				$plugin->{$prop} = isset( $plugin->{$prop} ) ? $plugin->{$prop} : '--';

				// get the value
				$columns[$label] = $plugin->{$prop};

				// some values need to be cleaned up
				switch( $prop ) {

					// remove html link from author
					case 'author' :
						$columns[$label] = strip_tags( $columns[$label] );
						break;

					// output rating out of 5, and include # of reviews
					case 'rating' :
						$rating = number_format( ( (int) $columns[$label] / 100 ) * 5, 1 );
						$num_ratings = isset( $plugin->num_ratings ) ? $plugin->num_ratings : 0;
						$columns[$label] = $rating . '/5' . ' (' . $num_ratings . ')';
						break;

					// format last_updated date and include version
					case 'last_updated' :
						$version = isset( $plugin->version ) ? ' (' . $plugin->version . ')' : '';
						$columns[$label] = date( 'Y-m-d', strtotime( $columns[$label] ) ) . $version;
						break;

					// add commas to active install count, and right-justify
					case 'active_installs' :
						$count = number_format( $columns[$label] );

						// 15 is width of column
						$extra_space = 15 - strlen( $count );
						$space = '';
						for( $i = 0; $i < $extra_space; $i++ ) {
							$space .= ' ';
						}

						$columns[$label] = $space . $count;
						break;

					// include 'tested up to' with requires
					case 'requires' :
						if ( !$columns[$label] ) $columns[$label] = '--';
						$tested = isset( $plugin->tested ) ? $plugin->tested : '--';
						$columns[$label] .= '/' . $tested;
						break;

				}

			}

			$rows[$plugin->slug] = $columns;
		}

		// output as list table
		$formatter = new \WP_CLI\Formatter( $assoc_args, array_values( $props ), 'plugin' );
		$formatter->display_items( $rows );

	}

}
WP_CLI::add_command( 'plugin favorites', 'MD_CLI_Plugin_Favorites' );
