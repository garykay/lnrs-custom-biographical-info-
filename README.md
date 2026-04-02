# LNRS Custom Biographical Info

A WordPress plugin that replaces the default plain-text Biographical Info field in user profiles with a fully-featured TinyMCE rich-text editor. Authors can now write formatted bios that include headings, lists, bold/italic text, inline images, links, and more — all stored and rendered as clean HTML.

---

## Features

- Adds a rich-text (TinyMCE/wp_editor) biographical info field to the WordPress user profile and user-edit admin screens.
- Hides the default plain-text Biographical Info textarea to avoid duplicate fields.
- Sanitizes saved content using `wp_kses()` for standard users, and preserves unfiltered HTML for users with the `unfiltered_html` capability.
- Automatically overrides `get_the_author_description`, `get_user_metadata`, and `author_bio` filters so the rich bio is returned wherever a theme or plugin reads the user description.
- Disables WordPress automatic paragraph formatting (`wpautop`) on the bio field to prevent double-wrapping of editor-generated markup.

---

## Requirements

- **WordPress:** 5.0 or higher (TinyMCE / `wp_editor` based)
- **PHP:** 7.4 or higher

---

## Installation

1. Download or clone this repository.
2. Copy the `lnrs-custom-biographical-info.php` file (or the entire folder) into your WordPress installation's `/wp-content/plugins/` directory.
3. Log in to the WordPress admin and navigate to **Plugins → Installed Plugins**.
4. Locate **LNRS Custom Biographical Info** and click **Activate**.

---

## Usage

### Editing a Bio

1. In the WordPress admin, go to **Users → Profile** (your own profile) or **Users → All Users → Edit** (another user's profile).
2. Scroll down to the **Biographical Info (Rich Content)** section.
3. Use the TinyMCE editor to write and format the bio. The toolbar supports headings, bold, italic, underline, bullet/numbered lists, blockquotes, alignment, links, and more.
4. Click **Update Profile** to save.

### Displaying a Bio in Themes

The plugin hooks into WordPress's standard description filters, so any theme that calls `get_the_author_meta('description')`, `get_the_author_description()`, or the `author_bio` filter will automatically receive the rich HTML content. No theme changes are typically required.

For manual output in a template, retrieve the stored HTML directly:

```php
$bio = get_user_meta( get_the_author_meta('ID'), 'lnrs_gutenberg_bio', true );
if ( $bio ) {
    echo wp_kses_post( $bio );
}
```

---

## File Structure

```
lnrs-custom-biographical-info-/
└── lnrs-custom-biographical-info.php   # Main plugin file
```

---

## Contributing

1. Fork the repository and create a feature branch from `main`.
2. Make your changes with clear, descriptive commit messages.
3. Open a pull request against `main` describing what was changed and why.

Bug reports and feature requests are welcome via [GitHub Issues](../../issues).

---

## License

This project is released under the [GPL-2.0-or-later](https://www.gnu.org/licenses/gpl-2.0.html) license, consistent with the WordPress ecosystem.
