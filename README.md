# wp-cli-favorite-plugins

A WP-CLI extension to list favorited plugins from a WordPress.org user account. This can be paired with existing WP-CLI commands to batch download and activate these plugins in bulk, essentially allowing you to build your own plugin install profiles.

## Usage

`wp plugin favorites <user> [--slug] [--verbose]`

By default, this will display a human-readable list of any user's favorite plugins, including information about when the plugin was last updated, its active installs, and its star rating.

## Options

`<user>` - The username of the WordPress.org account. This is required.

`--slug` - Add this flag to only return plugin slugs. This is useful when combined with other commands (see Advanced section below).

`--verbose` - Add this flag to return additional information about the plugins: their author, current version, WordPress version requirement, WordPress version tested up to, and short description.

## Advanced

You can combine this command with other WP-CLI plugin commands with the help of the `--slug` flag. For example, `wp plugin favorites <user> --slug | xargs wp plugin install` will automatically install all of the plugins in the given user's favorites list. Likewise, you can add the `--activate` flag to `wp plugin install` to activate them all as well.

When batch-downloading in this manner, if you want to exclude certain plugins from the list, you can include a `grep` command. `wp plugin favorites <user> --slug | grep -vwE "(hello-dolly|akismet)" | xargs wp plugin install --activate` will download and activate all of the given user's favorite plugins except for Hello Dolly and Akismet.

## Examples

List all of my favorite plugins:
`wp plugin favorites seventhsteel`

Find out more information about those plugins:
`wp plugin favorites seventhsteel --verbose`

Download and activate all of those plugins:
`wp plugin favorites seventhsteel --slug | xargs wp plugin install --activate`

Download all of those plugins except for Display Widgets and Debug Bar:
`wp plugin favorites matt --slug | grep -vwE "(display-widgets|debug-bar)" | xargs wp plugin install --activate`
