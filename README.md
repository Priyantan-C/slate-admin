# Slate Admin

A lightweight, modern skin for the WordPress admin and login page. Styling only: it changes how the admin looks and leaves how it works alone.

![Posts list with status pills and the accent-tinted sidebar](.github/screenshots/posts.png)

## Why

Most admin themes ship settings panels, icon packs and JavaScript frameworks. The look itself is almost entirely CSS, so this plugin is a few stylesheets and under 1 KB of script, with nothing loaded on the public site.

## What you get

- **Clean surfaces.** Light neutral page, hairline cards and tables, one accent colour.
- **Sidebar.** Compact rows, a soft accent tint on the open section, chevrons on sections with sub-items, even spacing in fly-out menus.
- **Colour with meaning.** Tinted pills for Draft, Pending, Scheduled and Private. Dots for inactive or outdated plugins. Amber update counts. Published content and active plugins stay plain, so long lists stay calm.
- **List tables.** Status filters as a segmented control, categories and tags as soft chips.
- **Media.** Library grid and list, attachment details, uploader and the "Add media" dialog, including inside the block editor.
- **Login.** Restyled login, lost password and reset screens.
- **Dark mode.** A site default plus a per-user toggle in the toolbar (Light, Dark, System).
- **Block editor.** Picks up the accent colour. The editor canvas is left alone.
- **Wide screens.** Content can be centred and capped at 1440px so tables stay readable on large monitors.
- **Readable accents.** Accent text and button labels are contrast-checked against whatever brand colour you pick.

| Media Library | Attachment details |
| --- | --- |
| ![Media Library grid](.github/screenshots/media-library.png) | ![Attachment details dialog](.github/screenshots/attachment-details.png) |

| Plugins | Dark mode |
| --- | --- |
| ![Plugins list with inactive plugins muted](.github/screenshots/plugins.png) | ![Posts list in dark mode](.github/screenshots/posts-dark.png) |

<img src=".github/screenshots/login.png" alt="Login screen" width="520">

## Install

1. Download `slate-admin.zip` from the latest [release](../../releases).
2. In WordPress, go to **Plugins > Add Plugin > Upload Plugin**, choose the zip, then activate.
3. Open **Settings > Slate Admin**.

Use the zip attached to a release. The "Source code" zip that GitHub generates unpacks to a versioned folder name, which WordPress would treat as a different plugin on each update.

## Settings

| Setting | What it does |
| --- | --- |
| Accent colour | Primary buttons, links, focus rings and the open sidebar section |
| Default appearance | Light, Dark, or follow the device. Applies to the login page and to users who have not chosen their own |
| Content width | Centred up to 1440px, or full width |

## Agency defaults

To set the same accent on every client site without opening the settings screen, add this to an mu-plugin:

```php
add_filter( 'slate_admin_defaults', function ( $d ) {
    $d['accent'] = '#0f766e';
    return $d;
} );
```

Anything saved on the settings screen overrides these defaults for that site.

## What it deliberately does not do

Slate Admin only changes appearance. These are left to WordPress or to a utility plugin such as [Admin and Site Enhancements (ASE)](https://wordpress.org/plugins/admin-site-enhancements/), and Slate styles whatever they produce:

| Job | ASE feature |
| --- | --- |
| Removing toolbar items such as the WordPress logo | Clean Up Admin Bar |
| Admin footer text | Custom Admin Footer Text |
| Login logo and its link | Site Identity on Login Page |
| Sidebar width | Wider Admin Menu |

## Compatibility

- WordPress 6.4 or later, PHP 7.4 or later. Tested on WordPress 6.8 and 7.1.
- Tested alongside ASE 9.2 and Autoptimize.
- The skin pins the core colour scheme to the neutral base and hides the per-user colour scheme picker, because the toolbar toggle replaces it.
- Screens from plugins that hardcode light colours (page builders, some shop screens) can look patchy in dark mode. Light is the default for that reason.

## Files

```
slate-admin.php     Plugin bootstrap, settings screen, accent and contrast logic
uninstall.php       Removes the stored option and per-user appearance choice
assets/admin.css    Admin skin
assets/media.css    Media skin, loaded only on media screens
assets/login.css    Login screens
assets/admin.js     Appearance toggle in the toolbar
readme.txt          WordPress-format readme and changelog
```

## Credits

The design tokens follow the neutral, card-based language of Cloudflare's open source [Kumo](https://github.com/cloudflare/kumo) design system (MIT licensed). No third-party logos, fonts or assets are included.

## Licence

GPL-2.0-or-later, the same as WordPress.
