=== Template Usage Inspector ===
Contributors: jack89ita
Tags: templates, developer tools, template management, debugging, qa
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Instantly see which pages a template powers, so every change is intentional — never accidental.

== Description ==

When you modify a WordPress template, you might be affecting dozens of pages without even knowing it. **Template Usage Inspector** gives you full visibility: instantly see which pages, posts and custom post types use each template — before you change a single line of code.

Designed for WordPress developers and agencies working on complex sites, it turns template management from a guessing game into a controlled, auditable process.

[View on GitHub](https://github.com/Jack89ita/template-usage-inspector)

= What you can do =

* **See every page affected by any template** — at a glance, sorted by usage count so the most impactful templates are always first
* **Expand any template row** to inspect the individual posts and pages assigned to it
* **Open all impacted pages in one click** — choose between frontend preview or backend editor
* **Spot orphaned templates** — files missing from the theme are automatically flagged, so you can clean up safely
* **Export a full usage report as CSV** for QA workflows and documentation
* **Filter by language** when using WPML or Polylang on multilingual sites
* **Search templates instantly** with the live search bar

= Built for real projects =

Template Usage Inspector is read-only and completely safe to use in production. It uses optimised database queries with WordPress transient caching to minimise performance impact, even on large sites with hundreds of pages and multiple custom post types.

No settings to configure. Install, activate, and find it under **Tools → Template Usage Inspector**.

= Who is this for? =

* **Developers** maintaining or refactoring WordPress themes
* **Agencies** managing client sites with complex template structures
* **QA teams** who need to verify which pages are affected before deploying changes

== Installation ==

1. Upload the `template-usage-inspector` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Navigate to **Tools → Template Usage Inspector**

That's it — no configuration needed.

== Frequently Asked Questions ==

= Does this plugin modify my database or content? =

No. Template Usage Inspector is entirely read-only. It only reads existing information about templates and posts. It never writes, modifies or deletes any content or database structures.

= Is it safe to use on a live production site? =

Yes. The plugin is read-only and uses optimised queries with transient caching to minimise any performance impact. It is designed to be safe and lightweight in production environments.

= Does it work with custom post types? =

Yes. The plugin supports pages, posts, and all registered custom post types. Internal WordPress types such as revisions and navigation menu items are automatically excluded.

= What happens if a template file has been deleted from the theme? =

If a template is still assigned to pages in the database but the corresponding file no longer exists in the theme, the plugin will still show it in the list and flag it as missing. This makes it easy to identify orphaned template assignments that may need cleaning up.

= Does it work with multilingual plugins? =

Yes. The plugin integrates with both **WPML** and **Polylang**. When either plugin is active, a language filter appears on the dashboard so you can inspect template usage per language.

= Can I export the data? =

Yes. Each template row includes a CSV export button that downloads a report of all affected pages and posts. Useful for QA handoffs and documentation.

= Where do I find the plugin after activation? =

Navigate to **Tools → Template Usage Inspector** in your WordPress admin.

= Does it work with multisite? =

The plugin works on individual sites within a multisite network. It is not network-activated and reads data for the current site only.

== Screenshots ==

1. Dashboard overview showing template summary cards and the full template table with usage counts
2. Expandable template rows revealing the individual pages and posts assigned to each template
3. One-click QA actions to open all affected pages in frontend or backend
4. CSV export and language filter for multilingual sites

== Changelog ==

= 1.0.0 =
* Initial release
* Template usage dashboard with summary cards (total templates, used, unused, total pages impacted)
* Expandable template rows showing individual affected posts and pages
* One-click open all pages in frontend or backend
* Visual usage indicator with animated progress bars
* Template file last-modified date indicator
* Orphaned template detection (missing theme files flagged automatically)
* CSV export per template
* Live search bar
* Language filter for WPML and Polylang
* Optimised queries with transient caching
* Supports pages, posts and custom post types

== Upgrade Notice ==

= 1.0.0 =
Initial release of Template Usage Inspector.
