=== Slate Admin ===
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.5.0
License: GPLv2 or later

A lightweight, modern skin for the WordPress admin and login page. Styling only.

== What it does ==

* One soft canvas shared by the page, sidebar and toolbar, with white kept for cards, tables and fields so content stands out. One accent colour.
* Layered dashboard widgets and plugin cards, settings screens laid out as cards, faint shadows on raised surfaces.
* Compact sidebar with a soft accent-tinted pill on the open section and chevrons on sections that have sub-items.
* Colour with meaning: tinted pills for Draft, Pending, Scheduled and Private, status dots for inactive or outdated plugins, amber update counts, soft chips for categories and tags.
* Page titles with a full-width divider, and status filters shown as a segmented control.
* Media Library, attachment details, uploader and the "Add media" dialog, including inside the block editor.
* Restyled login, lost password and reset screens.
* Optional dark mode: site default plus a per-user toggle in the toolbar (Light, Dark, System).
* Carries the accent colour into the block editor, and leaves the editor canvas alone.
* Nothing is loaded on the public site. No fonts, no icon packs, no JavaScript framework.

== What it deliberately does not do ==

Slate Admin only changes how things look. These are left to core or to a utility plugin such as Admin and Site Enhancements (ASE):

* Removing toolbar items such as the WordPress logo (ASE: Clean Up Admin Bar)
* Admin footer text (ASE: Custom Admin Footer Text)
* Login logo and its link (ASE: Site Identity on Login Page)
* Sidebar width (ASE: Wider Admin Menu)

Slate styles whatever those features produce.

== Setup ==

1. Upload the zip under Plugins > Add Plugin > Upload Plugin, then activate.
2. Open Settings > Slate Admin to set the accent colour, the default appearance and the content width.

== Agency defaults ==

To set the same accent on every site without visiting the settings screen, put this in an mu-plugin:

    add_filter( 'slate_admin_defaults', function ( $d ) {
        $d['accent'] = '#0f766e';
        return $d;
    } );

Anything saved on the settings screen overrides these defaults for that site.

== Notes ==

* The skin pins the core colour scheme to the neutral base and hides the per-user colour scheme picker, since the toolbar toggle replaces it.
* Screens from plugins that hardcode light colours (page builders, some shop screens) can look patchy in dark mode. Light is the default for that reason.
* Design tokens follow the neutral, card-based language of Cloudflare's open source Kumo design system (MIT licensed). No third-party logos, fonts or assets are included.

== Changelog ==

= 1.5.0 =
* Changed: the page, sidebar and toolbar now share one canvas colour (#fbfbfb, with a matching dark tone). White is reserved for cards, tables and fields.
* Added: three elevation levels. Flat surfaces have a hairline, raised ones (cards, tables, buttons) add a faint shadow, menus and dialogs keep the larger one.
* Added: layered dashboard widgets. The title sits on a recessed layer and the body is a white raised panel inside it.
* Added: the same layering on Add Plugins cards, with the ratings strip on the recessed layer.
* Added: settings as cards on WordPress's own settings and profile screens.
* Build: release zips now ship minified stylesheets. The repository keeps the readable source.

= 1.4.0 =
* Added: media skin in its own stylesheet (assets/media.css), loaded only where WordPress loads its media views.
* Media Library: flat toolbar with a segmented grid and list switch, rounded thumbnails with a clean caption strip, accent ring and tick for selected items, red outline for bulk delete.
* Attachment details: rounded dialog, quiet previous, next and close buttons, labels above fields, preview on a soft panel.
* "Add media" dialog: underline tabs, tinted left menu, styled fields and footer, also when opened over the block editor.
* Uploader: dashed drop zone, slim accent progress bars.
* List view: rounded thumbnails and muted file names.

= 1.3.1 =
* Removed: coloured left edge on notices, pending comments and paused plugins. Tint and dots carry the meaning.

= 1.3.0 =
* Added: semantic colour palette (green, amber, red, blue, purple) for both themes.
* Added: post state pills. Only Draft, Pending, Scheduled, Private and Trash are marked; published content stays plain.
* Added: plugin status dots for the exceptions only (inactive, update waiting, paused). Rows with an update get a soft amber tint.
* Added: category and tag chips in list tables, coloured At a Glance icons, amber update counts, Site Health badges.
* Changed: the open sidebar section uses a soft tint of the accent colour.
* Changed: inactive plugins are white with muted text, so they no longer blend into the table header.
* Changed: accent text and button labels are contrast-checked against the chosen accent, so light brand colours stay readable.
* Fixed: uneven inset in fly-out submenus (core adds a hidden 5px left border).

= 1.2.0 =
* Added: content width setting. Centred up to 1440px by default, so tables and forms stay readable on very wide monitors.
* Fixed: white zebra rows in the dashboard Activity widget in dark mode.
* Fixed: toolbar dropdowns from other plugins (for example the Autoptimize cache meter) showing dark cells on the light bar.
* Changed: plugin list no longer shows an accent bar on every active row, and row lines are even.
* Changed: empty dashboard columns are hidden until a widget is being dragged.
* Changed: the page header line now spans the content area without relying on gutters.

= 1.1.0 =
* Removed: toolbar logo setting, admin footer text, login logo handling, login footer line, fixed sidebar width.
* Changed: whiter surfaces, denser sidebar with chevrons, smaller page titles with a divider, segmented status filters, 8px card radius, command palette shortcut styled as a search pill.

= 1.0.0 =
* First version.
