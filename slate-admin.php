<?php
/**
 * Plugin Name:       Slate Admin
 * Description:       A lightweight, modern skin for the WordPress admin and login page. Styling only: light neutral surfaces, hairline cards, one accent colour, optional dark mode. Nothing is loaded on the public site.
 * Version:           1.7.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            SynerByte Tech
 * License:           GPL-2.0-or-later
 * Text Domain:       slate-admin
 * Update URI:        https://github.com/Priyantan-C/slate-admin
 *
 * Styling only by design. Toolbar clean-up, footer text, login logo and menu
 * width are left to core or to a utility plugin such as Admin and Site
 * Enhancements.
 *
 * Agency defaults: set the accent once for every site from an mu-plugin.
 *
 *   add_filter( 'slate_admin_defaults', function ( $d ) {
 *       $d['accent'] = '#0f766e';
 *       return $d;
 *   } );
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SLATE_ADMIN_VERSION', '1.7.0' );
define( 'SLATE_ADMIN_FILE', __FILE__ );
define( 'SLATE_ADMIN_OPTION', 'slate_admin' );
define( 'SLATE_ADMIN_USER_META', 'slate_admin_scheme' );

/* -------------------------------------------------------------------------
 * Options
 * ---------------------------------------------------------------------- */

function slate_admin_defaults() {
	$defaults = array(
		'accent' => '#2f5bea',
		'scheme' => 'light', // light | dark | auto.
		'width'  => 'centered', // centered | full.
		'nav'    => 'neutral', // neutral | accent: the current sidebar item.
	);
	return apply_filters( 'slate_admin_defaults', $defaults );
}

/**
 * Ready-made accents shown as swatches next to the colour picker. Agencies
 * can swap in their own with the slate_admin_accent_presets filter.
 */
function slate_admin_accent_presets() {
	$presets = array(
		'#2f5bea' => __( 'Slate blue', 'slate-admin' ),
		'#0a6cff' => __( 'Bright blue', 'slate-admin' ),
		'#4f46e5' => __( 'Indigo', 'slate-admin' ),
		'#7c3aed' => __( 'Violet', 'slate-admin' ),
		'#0f766e' => __( 'Teal', 'slate-admin' ),
		'#15803d' => __( 'Green', 'slate-admin' ),
		'#ea580c' => __( 'Orange', 'slate-admin' ),
		'#e11d48' => __( 'Rose', 'slate-admin' ),
		'#3f3f46' => __( 'Graphite', 'slate-admin' ),
	);
	return apply_filters( 'slate_admin_accent_presets', $presets );
}

function slate_admin_options() {
	static $cache = null;
	if ( null === $cache ) {
		$saved = get_option( SLATE_ADMIN_OPTION, array() );
		$cache = wp_parse_args( is_array( $saved ) ? $saved : array(), slate_admin_defaults() );
	}
	return $cache;
}

function slate_admin_opt( $key ) {
	$o = slate_admin_options();
	return isset( $o[ $key ] ) ? $o[ $key ] : '';
}

function slate_admin_sanitize( $in ) {
	$d   = slate_admin_defaults();
	$in  = is_array( $in ) ? $in : array();
	$out = array();

	$accent        = isset( $in['accent'] ) ? sanitize_hex_color( $in['accent'] ) : '';
	$out['accent'] = $accent ? $accent : $d['accent'];

	$scheme        = isset( $in['scheme'] ) ? $in['scheme'] : '';
	$out['scheme'] = in_array( $scheme, array( 'light', 'dark', 'auto' ), true ) ? $scheme : 'light';

	$width        = isset( $in['width'] ) ? $in['width'] : '';
	$out['width'] = in_array( $width, array( 'centered', 'full' ), true ) ? $width : 'centered';

	$nav        = isset( $in['nav'] ) ? $in['nav'] : '';
	$out['nav'] = in_array( $nav, array( 'neutral', 'accent' ), true ) ? $nav : 'neutral';

	return $out;
}

/* -------------------------------------------------------------------------
 * Colour helpers
 * ---------------------------------------------------------------------- */

/** Black or white, whichever reads better on the accent colour. */
function slate_admin_on_accent( $hex ) {
	// 0.179 is the luminance at which black and white give equal contrast.
	return slate_admin_luminance( slate_admin_rgb( $hex ) ) > 0.179 ? '#0a0a0a' : '#ffffff';
}

/** Hex colour to an array of r, g, b (0 to 255). */
function slate_admin_rgb( $hex ) {
	$hex = ltrim( $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	return array( hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ) );
}

/** WCAG relative luminance of an r, g, b array. */
function slate_admin_luminance( $rgb ) {
	$lum     = 0;
	$weights = array( 0.2126, 0.7152, 0.0722 );
	foreach ( $rgb as $i => $channel ) {
		$c    = $channel / 255;
		$c    = $c <= 0.03928 ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 );
		$lum += $weights[ $i ] * $c;
	}
	return $lum;
}

/**
 * Nudge a colour toward black (-1) or white (1) until it reaches a 4.5:1
 * contrast ratio against the given background. Returns an rgb() string.
 */
function slate_admin_readable( $hex, $against, $direction ) {
	$rgb    = slate_admin_rgb( $hex );
	$bg     = slate_admin_luminance( slate_admin_rgb( $against ) );
	$target = $direction < 0 ? 0 : 255;
	$out    = $rgb;
	for ( $step = 0; $step <= 20; $step++ ) {
		$t   = $step / 20;
		$out = array(
			(int) round( $rgb[0] + ( $target - $rgb[0] ) * $t ),
			(int) round( $rgb[1] + ( $target - $rgb[1] ) * $t ),
			(int) round( $rgb[2] + ( $target - $rgb[2] ) * $t ),
		);
		$fg    = slate_admin_luminance( $out );
		$ratio = ( max( $fg, $bg ) + 0.05 ) / ( min( $fg, $bg ) + 0.05 );
		if ( $ratio >= 4.5 ) {
			break;
		}
	}
	return 'rgb(' . implode( ',', $out ) . ')';
}

/**
 * Accent as CSS variables. Also feeds core's own theme colour variables so
 * the block editor and other component-based screens pick up the same accent.
 */
function slate_admin_inline_vars() {
	$accent = slate_admin_opt( 'accent' );
	$on     = slate_admin_on_accent( $accent );
	$css    = sprintf( ':root{--sa-accent:%1$s;--sa-on-accent:%2$s}', $accent, $on );

	// Accent as text: darken (light theme) or lighten (dark theme) the accent
	// until it clears WCAG AA on the surfaces it sits on, whatever the brand colour.
	$css .= sprintf(
		':root{--sa-link-l:%1$s;--sa-link-d:%2$s}',
		slate_admin_readable( $accent, '#eef0f6', -1 ),
		slate_admin_readable( $accent, '#262b3d', 1 )
	);

	$rgb = slate_admin_rgb( $accent );
	// Mix the accent toward black (negative) or white (positive).
	$mix = function ( $amount ) use ( $rgb ) {
		$out = array();
		foreach ( $rgb as $channel ) {
			$target = $amount < 0 ? 0 : 255;
			$out[]  = (int) round( $channel + ( $target - $channel ) * abs( $amount ) );
		}
		return implode( ',', $out );
	};

	$base   = $mix( 0 );
	$dark10 = $mix( -0.12 );
	$dark20 = $mix( -0.24 );
	$vars   = '';

	// Core theme colour and the component accent built on it.
	foreach ( array( '' => $base, '-darker-10' => $dark10, '-darker-20' => $dark20 ) as $suffix => $value ) {
		$vars .= sprintf(
			'--wp-admin-theme-color%1$s:rgb(%2$s)!important;--wp-admin-theme-color%1$s--rgb:%2$s!important;--wp-components-color-accent%1$s:rgb(%2$s)!important;',
			$suffix,
			$value
		);
	}

	// Design system brand tokens used by the editors in WordPress 7 and later.
	$tokens = array(
		'background-interactive-brand-strong'        => 'rgb(' . $base . ')',
		'background-interactive-brand-strong-active' => 'rgb(' . $dark10 . ')',
		'background-interactive-brand-weak-active'   => 'rgb(' . $mix( 0.86 ) . ')',
		'background-surface-brand'                   => 'rgb(' . $mix( 0.9 ) . ')',
		'background-thumb-brand'                     => 'rgb(' . $dark10 . ')',
		'background-thumb-brand-active'              => 'rgb(' . $dark10 . ')',
		'foreground-interactive-brand'               => 'rgb(' . $dark10 . ')',
		'foreground-interactive-brand-active'        => 'rgb(' . $mix( -0.5 ) . ')',
		'foreground-interactive-brand-strong'        => $on,
		'foreground-interactive-brand-strong-active' => $on,
		'stroke-interactive-brand'                   => 'rgb(' . $dark10 . ')',
		'stroke-interactive-brand-active'            => 'rgb(' . $dark20 . ')',
		'stroke-surface-brand'                       => 'rgb(' . $mix( 0.6 ) . ')',
		'stroke-surface-brand-strong'                => 'rgb(' . $dark10 . ')',
	);
	foreach ( $tokens as $name => $value ) {
		$vars .= '--wpds-color-' . $name . ':' . $value . '!important;';
	}

	// !important because WordPress 7 and later write these variables as inline
	// styles from JavaScript, on <html> and on each editor theme wrapper.
	return $css . 'html:root,body.sa [style*="--wpds-color-"]{' . $vars . '}';
}

/** Scheme for the current request: the user's own choice wins over the site default. */
function slate_admin_scheme() {
	$scheme = slate_admin_opt( 'scheme' );
	if ( is_user_logged_in() ) {
		$mine = get_user_meta( get_current_user_id(), SLATE_ADMIN_USER_META, true );
		if ( in_array( $mine, array( 'light', 'dark', 'auto' ), true ) ) {
			$scheme = $mine;
		}
	}
	return $scheme;
}

/* -------------------------------------------------------------------------
 * Admin skin
 * ---------------------------------------------------------------------- */

/**
 * True on screens where we only restyle the chrome (sidebar and toolbar) and
 * leave the content alone: the block editor and the site editor.
 */
function slate_admin_is_editor_screen() {
	if ( ! function_exists( 'get_current_screen' ) ) {
		return false;
	}
	$screen = get_current_screen();
	if ( ! $screen ) {
		return false;
	}
	if ( method_exists( $screen, 'is_block_editor' ) && $screen->is_block_editor() ) {
		return true;
	}
	return in_array( $screen->base, array( 'site-editor', 'customize' ), true );
}

add_action(
	'admin_enqueue_scripts',
	function () {
		$url = plugin_dir_url( __FILE__ ) . 'assets/';
		wp_enqueue_style( 'slate-admin', $url . 'admin.css', array(), SLATE_ADMIN_VERSION );
		wp_add_inline_style( 'slate-admin', slate_admin_inline_vars() );

		wp_enqueue_script( 'slate-admin', $url . 'admin.js', array(), SLATE_ADMIN_VERSION, true );
		wp_localize_script(
			'slate-admin',
			'slateAdmin',
			array(
				'ajax'   => admin_url( 'admin-ajax.php' ),
				'nonce'  => wp_create_nonce( 'slate_admin_scheme' ),
				'scheme' => slate_admin_scheme(),
				'labels' => array(
					'light' => __( 'Light', 'slate-admin' ),
					'dark'  => __( 'Dark', 'slate-admin' ),
					'auto'  => __( 'System', 'slate-admin' ),
				),
			)
		);
	},
	20
);

/**
 * Media styles live in their own file, loaded only where WordPress loads its
 * media views: the library, the uploader, and any screen that opens the
 * "Add media" dialog.
 */
function slate_admin_enqueue_media_style() {
	if ( ! is_admin() || wp_style_is( 'slate-admin-media', 'enqueued' ) ) {
		return;
	}
	wp_enqueue_style( 'slate-admin-media', plugin_dir_url( __FILE__ ) . 'assets/media.css', array( 'slate-admin' ), SLATE_ADMIN_VERSION );
}
add_action( 'wp_enqueue_media', 'slate_admin_enqueue_media_style' );

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( in_array( $hook, array( 'upload.php', 'media-new.php' ), true ) || ( $screen && 'attachment' === $screen->post_type ) ) {
			slate_admin_enqueue_media_style();
		}
	},
	21
);

add_filter(
	'admin_body_class',
	function ( $classes ) {
		$editor = slate_admin_is_editor_screen();
		// Editors bring their own hardcoded light chrome, so dark mode stays off there.
		$scheme   = $editor ? 'light' : slate_admin_scheme();
		$classes .= ' sa sa-scheme-' . $scheme . ( $editor ? ' sa-editor' : ' sa-ui' );
		$classes .= 'full' === slate_admin_opt( 'width' ) ? ' sa-w-full' : ' sa-w-centered';
		$classes .= 'accent' === slate_admin_opt( 'nav' ) ? ' sa-nav-accent' : '';
		return $classes;
	}
);

/**
 * The skin is written against the default core colour scheme, so pin it and
 * remove the per-user picker (the toolbar toggle replaces it).
 */
add_filter(
	'get_user_option_admin_color',
	function () {
		return 'fresh';
	}
);
add_action(
	'admin_init',
	function () {
		remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );
	}
);

/* Toolbar: the appearance toggle. */
add_action(
	'admin_bar_menu',
	function ( $bar ) {
		if ( ! is_admin() ) {
			return;
		}
		if ( slate_admin_is_editor_screen() ) {
			return;
		}
		$labels = array(
			'light' => __( 'Light', 'slate-admin' ),
			'dark'  => __( 'Dark', 'slate-admin' ),
			'auto'  => __( 'System', 'slate-admin' ),
		);
		$scheme = slate_admin_scheme();
		$bar->add_node(
			array(
				'id'     => 'slate-scheme',
				'parent' => 'top-secondary',
				'title'  => '<span class="ab-icon dashicons dashicons-admin-appearance" aria-hidden="true"></span><span class="ab-label">' . esc_html( $labels[ $scheme ] ) . '</span>',
				'href'   => '#',
				'meta'   => array(
					'title' => __( 'Switch appearance: light, dark or system', 'slate-admin' ),
				),
			)
		);
	},
	999
);

add_action(
	'wp_ajax_slate_admin_scheme',
	function () {
		check_ajax_referer( 'slate_admin_scheme', 'nonce' );
		$scheme = isset( $_POST['scheme'] ) ? sanitize_key( wp_unslash( $_POST['scheme'] ) ) : '';
		if ( ! in_array( $scheme, array( 'light', 'dark', 'auto' ), true ) ) {
			wp_send_json_error( null, 400 );
		}
		update_user_meta( get_current_user_id(), SLATE_ADMIN_USER_META, $scheme );
		wp_send_json_success();
	}
);

/* -------------------------------------------------------------------------
 * Login page
 * ---------------------------------------------------------------------- */

add_action(
	'login_enqueue_scripts',
	function () {
		wp_enqueue_style( 'slate-login', plugin_dir_url( __FILE__ ) . 'assets/login.css', array( 'login' ), SLATE_ADMIN_VERSION );
		wp_add_inline_style( 'slate-login', slate_admin_inline_vars() );
	}
);

add_filter(
	'login_body_class',
	function ( $classes ) {
		$classes[] = 'sa-login';
		$classes[] = 'sa-scheme-' . slate_admin_opt( 'scheme' );
		return $classes;
	}
);

/* -------------------------------------------------------------------------
 * Settings screen
 * ---------------------------------------------------------------------- */

add_action(
	'admin_init',
	function () {
		register_setting(
			'slate_admin',
			SLATE_ADMIN_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => 'slate_admin_sanitize',
			)
		);
	}
);

add_action(
	'admin_menu',
	function () {
		add_options_page(
			__( 'Slate Admin', 'slate-admin' ),
			__( 'Slate Admin', 'slate-admin' ),
			'manage_options',
			'slate-admin',
			'slate_admin_settings_page'
		);
	}
);

add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	function ( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=slate-admin' ) ) . '">' . esc_html__( 'Settings', 'slate-admin' ) . '</a>' );
		return $links;
	}
);

function slate_admin_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$o    = slate_admin_options();
	$name = SLATE_ADMIN_OPTION;
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Slate Admin', 'slate-admin' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'slate_admin' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="sa-accent"><?php esc_html_e( 'Accent colour', 'slate-admin' ); ?></label></th>
					<td>
						<div class="sa-accent-field">
							<input type="color" id="sa-accent" name="<?php echo esc_attr( $name ); ?>[accent]" value="<?php echo esc_attr( $o['accent'] ); ?>">
							<div class="sa-swatches" role="group" aria-label="<?php esc_attr_e( 'Preset accents', 'slate-admin' ); ?>">
								<?php foreach ( slate_admin_accent_presets() as $hex => $label ) : ?>
									<?php $hex = sanitize_hex_color( $hex ); ?>
									<?php if ( $hex ) : ?>
										<button type="button" class="sa-swatch" data-color="<?php echo esc_attr( $hex ); ?>" style="--sa-swatch:<?php echo esc_attr( $hex ); ?>" title="<?php echo esc_attr( $label ); ?>" aria-label="<?php echo esc_attr( $label ); ?>" aria-pressed="<?php echo strtolower( $hex ) === strtolower( $o['accent'] ) ? 'true' : 'false'; ?>"></button>
									<?php endif; ?>
								<?php endforeach; ?>
							</div>
						</div>
						<p class="description"><?php esc_html_e( 'Used for primary buttons, checkboxes, focus rings and update counts. Pick a preset or any colour.', 'slate-admin' ); ?></p>
						<script>
						( function () {
							var input = document.getElementById( 'sa-accent' );
							var swatches = document.querySelectorAll( '.sa-swatch' );
							function sync() {
								swatches.forEach( function ( b ) {
									b.setAttribute( 'aria-pressed', b.dataset.color.toLowerCase() === input.value.toLowerCase() ? 'true' : 'false' );
								} );
							}
							swatches.forEach( function ( b ) {
								b.addEventListener( 'click', function () {
									input.value = b.dataset.color;
									sync();
								} );
							} );
							input.addEventListener( 'input', sync );
						} )();
						</script>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="sa-nav"><?php esc_html_e( 'Sidebar highlight', 'slate-admin' ); ?></label></th>
					<td>
						<select id="sa-nav" name="<?php echo esc_attr( $name ); ?>[nav]">
							<option value="neutral" <?php selected( $o['nav'], 'neutral' ); ?>><?php esc_html_e( 'Neutral grey', 'slate-admin' ); ?></option>
							<option value="accent" <?php selected( $o['nav'], 'accent' ); ?>><?php esc_html_e( 'Accent colour', 'slate-admin' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'How the current item in the sidebar is marked. Neutral keeps the accent for buttons and actions.', 'slate-admin' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="sa-scheme"><?php esc_html_e( 'Default appearance', 'slate-admin' ); ?></label></th>
					<td>
						<select id="sa-scheme" name="<?php echo esc_attr( $name ); ?>[scheme]">
							<option value="light" <?php selected( $o['scheme'], 'light' ); ?>><?php esc_html_e( 'Light', 'slate-admin' ); ?></option>
							<option value="dark" <?php selected( $o['scheme'], 'dark' ); ?>><?php esc_html_e( 'Dark', 'slate-admin' ); ?></option>
							<option value="auto" <?php selected( $o['scheme'], 'auto' ); ?>><?php esc_html_e( 'Follow the device setting', 'slate-admin' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Applies to the login page and to users who have not picked their own from the toolbar. Screens from plugins that hardcode light colours can look patchy in dark mode.', 'slate-admin' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="sa-width"><?php esc_html_e( 'Content width', 'slate-admin' ); ?></label></th>
					<td>
						<select id="sa-width" name="<?php echo esc_attr( $name ); ?>[width]">
							<option value="centered" <?php selected( $o['width'], 'centered' ); ?>><?php esc_html_e( 'Centred, up to 1440px', 'slate-admin' ); ?></option>
							<option value="full" <?php selected( $o['width'], 'full' ); ?>><?php esc_html_e( 'Full width', 'slate-admin' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Only matters on very wide monitors, where full-width tables and forms become hard to scan.', 'slate-admin' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

require_once __DIR__ . '/includes/updater.php';
