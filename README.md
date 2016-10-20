# wp-cli-favorite-plugins

A WP-CLI extension to list favorited plugins from a WordPress.org user account. This can be paired with existing commands to batch download and activate these plugins in bulk, essentially allowing you to build your own WordPress plugin install profiles.

## Usage

`wp plugin favorites <user> [--verbose]`

This will display a list of the given user's favorite plugins. By default, only the plugin slugs will be returned.

Combine this with other WP-CLI commands using `xargs`. For example, `wp plugin favorites <user> | xargs wp plugin install --activate` will download, install, and activate all of the given user's plugins.

## Options

`<user>`

The username of the WordPress.org account. Required.

`--verbose`

Add this flag to return additional information about each plugin:

* Name
* Author
* Last updated date and current version
* Star rating (out of 5) and the amount of ratings given
* Approximate active installs
* WordPress version required and version tested up to

## Advanced

If you want to exclude certain plugins from being returned, you can include a `grep` command. For example, `wp plugin favorites <user> | grep -vwE "(hello-dolly|akismet)"` will exclude Hello Dolly and Akismet.

You can then chain `wp plugin install` to that. For example, `wp plugin favorites <user> | grep -vwE "(hello-dolly|akismet)" | xargs wp plugin install` would exclude those plugins from being installed.

## Examples

List all of Matt Mullenweg's favorite plugins:

`wp plugin favorites matt`

Find out more information about those plugins:

`wp plugin favorites matt --verbose`

Download and activate all of those plugins:

`wp plugin favorites matt | xargs wp plugin install --activate`

Download all of those plugins except for BBPress and HyperDB:

`wp plugin favorites matt | grep -vwE "(bbpress|hyperdb)" | xargs wp plugin install --activate`
