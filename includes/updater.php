<?php
/**
 * Slate Admin: updates from GitHub releases.
 *
 * The plugin header declares "Update URI: https://github.com/<owner>/<repo>",
 * so WordPress never looks for this plugin on wordpress.org and instead asks
 * the update_plugins_github.com filter below. That filter reads the latest
 * public release from the GitHub API and offers its slate-admin.zip asset as
 * the update package. WordPress then shows the update on the Plugins and
 * Updates screens and, when enabled, installs it on its own.
 *
 * No token is needed: the repository is public and the API allows 60
 * unauthenticated requests per hour per server, far more than one cached
 * check every few hours uses.
 *
 * Filters
 *   slate_admin_update_repo   string  "owner/repo" to update from (forks).
 * Constants
 *   SLATE_ADMIN_DISABLE_UPDATES  true turns update checks off entirely.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const SLATE_ADMIN_RELEASE_CACHE = 'slate_admin_latest_release';

function slate_admin_update_repo() {
	return apply_filters( 'slate_admin_update_repo', 'Priyantan-C/slate-admin' );
}

function slate_admin_updates_enabled() {
	return ! ( defined( 'SLATE_ADMIN_DISABLE_UPDATES' ) && SLATE_ADMIN_DISABLE_UPDATES );
}

/**
 * Latest release as a small array, or null. Cached for six hours, and for
 * thirty minutes after a failure so an outage does not slow every request.
 * "Check again" on the Updates screen clears the cache.
 */
function slate_admin_latest_release( $force = false ) {
	$cached = get_site_transient( SLATE_ADMIN_RELEASE_CACHE );
	if ( ! $force && is_array( $cached ) ) {
		return empty( $cached['version'] ) ? null : $cached;
	}

	$repo     = slate_admin_update_repo();
	$response = wp_remote_get(
		'https://api.github.com/repos/' . $repo . '/releases/latest',
		array(
			'timeout' => 10,
			'headers' => array(
				'Accept'     => 'application/vnd.github+json',
				'User-Agent' => 'slate-admin/' . SLATE_ADMIN_VERSION . '; ' . home_url( '/' ),
			),
		)
	);

	$release = null;
	if ( ! is_wp_error( $response ) && 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
		$data    = json_decode( wp_remote_retrieve_body( $response ), true );
		$release = slate_admin_parse_release( is_array( $data ) ? $data : array(), $repo );
	}

	set_site_transient(
		SLATE_ADMIN_RELEASE_CACHE,
		$release ? $release : array( 'version' => '' ),
		$release ? 6 * HOUR_IN_SECONDS : 30 * MINUTE_IN_SECONDS
	);

	return $release;
}

/**
 * Pull what WordPress needs out of a GitHub release. Only a slate-admin.zip
 * asset served from this repository's own release downloads is accepted.
 */
function slate_admin_parse_release( array $data, $repo ) {
	if ( empty( $data['tag_name'] ) || ! empty( $data['draft'] ) || ! empty( $data['prerelease'] ) ) {
		return null;
	}

	$version = ltrim( (string) $data['tag_name'], 'vV' );
	if ( ! preg_match( '/^\d+(\.\d+){1,3}$/', $version ) ) {
		return null;
	}

	$prefix  = 'https://github.com/' . $repo . '/releases/download/';
	$package = '';
	foreach ( isset( $data['assets'] ) && is_array( $data['assets'] ) ? $data['assets'] : array() as $asset ) {
		$url = isset( $asset['browser_download_url'] ) ? (string) $asset['browser_download_url'] : '';
		if ( isset( $asset['name'] ) && 'slate-admin.zip' === $asset['name'] && 0 === strpos( $url, $prefix ) ) {
			$package = $url;
			break;
		}
	}
	if ( '' === $package ) {
		return null;
	}

	return array(
		'version'   => $version,
		'package'   => $package,
		'url'       => isset( $data['html_url'] ) ? esc_url_raw( $data['html_url'] ) : 'https://github.com/' . $repo,
		'notes'     => isset( $data['body'] ) ? (string) $data['body'] : '',
		'published' => isset( $data['published_at'] ) ? (string) $data['published_at'] : '',
	);
}

/** Answer WordPress's update check for this plugin. */
add_filter(
	'update_plugins_github.com',
	function ( $update, $plugin_data, $plugin_file ) {
		if ( plugin_basename( SLATE_ADMIN_FILE ) !== $plugin_file || ! slate_admin_updates_enabled() ) {
			return $update;
		}
		$release = slate_admin_latest_release();
		if ( ! $release ) {
			return $update;
		}
		return array(
			'slug'         => 'slate-admin',
			'version'      => $release['version'],
			'url'          => $release['url'],
			'package'      => $release['package'],
			'requires'     => '6.4',
			'requires_php' => '7.4',
			'tested'       => get_bloginfo( 'version' ),
		);
	},
	10,
	3
);

/** "Check again" on the Updates screen also refreshes the cached release. */
add_action(
	'load-update-core.php',
	function () {
		if ( isset( $_GET['force-check'] ) && current_user_can( 'update_plugins' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			delete_site_transient( SLATE_ADMIN_RELEASE_CACHE );
		}
	}
);

/** After an update, forget the cached release so the next check is fresh. */
add_action(
	'upgrader_process_complete',
	function ( $upgrader, $extra ) {
		$plugins = isset( $extra['plugins'] ) ? (array) $extra['plugins'] : array();
		if ( isset( $extra['type'] ) && 'plugin' === $extra['type'] && in_array( plugin_basename( SLATE_ADMIN_FILE ), $plugins, true ) ) {
			delete_site_transient( SLATE_ADMIN_RELEASE_CACHE );
		}
	},
	10,
	2
);

/** The "View details" window on the Plugins and Updates screens. */
add_filter(
	'plugins_api',
	function ( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || ! isset( $args->slug ) || 'slate-admin' !== $args->slug ) {
			return $result;
		}
		$release = slate_admin_latest_release();
		$repo    = slate_admin_update_repo();

		return (object) array(
			'name'          => 'Slate Admin',
			'slug'          => 'slate-admin',
			'version'       => $release ? $release['version'] : SLATE_ADMIN_VERSION,
			'author'        => 'SynerByte Tech',
			'homepage'      => 'https://github.com/' . $repo,
			'requires'      => '6.4',
			'requires_php'  => '7.4',
			'tested'        => get_bloginfo( 'version' ),
			'last_updated'  => $release ? $release['published'] : '',
			'download_link' => $release ? $release['package'] : '',
			'sections'      => array(
				'description' => '<p>' . esc_html__( 'A lightweight, modern skin for the WordPress admin and login page. Styling only: light neutral surfaces, hairline cards, one accent colour, optional dark mode. Nothing is loaded on the public site.', 'slate-admin' ) . '</p>',
				'changelog'   => slate_admin_notes_html( $release ? $release['notes'] : '' ),
			),
		);
	},
	10,
	3
);

/** Release notes are short Markdown-style lists; render them as plain HTML. */
function slate_admin_notes_html( $notes ) {
	$html  = '';
	$items = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $notes ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		if ( preg_match( '/^[*-]\s+(.*)$/', $line, $m ) ) {
			$items[] = '<li>' . esc_html( $m[1] ) . '</li>';
			continue;
		}
		if ( $items ) {
			$html .= '<ul>' . implode( '', $items ) . '</ul>';
			$items = array();
		}
		$html .= '<p>' . esc_html( $line ) . '</p>';
	}
	if ( $items ) {
		$html .= '<ul>' . implode( '', $items ) . '</ul>';
	}
	return '' !== $html ? $html : '<p>' . esc_html__( 'See the release page on GitHub.', 'slate-admin' ) . '</p>';
}

/** Install new versions automatically unless the site owner turned it off. */
add_filter(
	'auto_update_plugin',
	function ( $update, $item ) {
		if ( isset( $item->plugin ) && plugin_basename( SLATE_ADMIN_FILE ) === $item->plugin && slate_admin_updates_enabled() ) {
			return (bool) slate_admin_opt( 'auto_update' );
		}
		return $update;
	},
	10,
	2
);
