=== Post Duplicator ===
Contributors: metaphorcreations
Tags: posts, post, duplicate, duplication
Requires at least: 6.6
Requires PHP: 7.4
Tested up to: 6.9.1
Stable tag: 3.0.8
License: GPL2

Creates functionality to duplicate any and all post types, including taxonomies & custom fields. Perfect for developers and content creators.

== Description ==

**Save Time. Work Smarter. Duplicate Any Post Type with Ease.**

Post Duplicator is the ultimate WordPress plugin for quickly creating exact duplicates of any post type in your WordPress site. Whether you're working with standard posts, pages, or custom post types, this plugin makes it effortless to clone content while preserving all taxonomies, custom fields, and metadata.

**Key Features:**

* **Universal Post Type Support** - Works with every post type WordPress supports, including custom post types from your favorite plugins and themes
* **Complete Data Preservation** - Automatically copies all taxonomies, custom fields, metadata, and featured images
* **Bulk Duplication** - Select and duplicate multiple posts at once with individual settings per post
* **Multiple Clones** - Create multiple copies of a single post simultaneously (up to 50 clones)
* **Flexible Duplication Options** - Customize title, slug, status, author, date, post type, and parent for each duplicate
* **Featured Image Management** - Set, replace, or remove featured images directly in the duplication modal
* **Smart Defaults** - Configure default settings that apply to all duplications (draft status, current user as author)
* **Post Types Configuration** - Control which post types can be duplicated and which appear in the "Post Type" dropdown menu
* **Permission Control** - Granular control over who can duplicate posts with role-based permissions
* **Modern Interface** - Beautiful modal interface with live editing and expandable settings
* **Cross-Post Type Duplication** - Convert posts to different post types during duplication
* **Hierarchical Post Support** - Set parent posts for pages and hierarchical custom post types
* **One-Click Operation** - Duplicate posts from the posts list, edit screen, or block editor toolbar

**Perfect For:**

* **Developers** - Quickly generate test content and dummy data for development using bulk or multiple clone features
* **Content Managers** - Create content templates and variations efficiently with one-click duplication
* **Bloggers** - Repurpose successful posts with different angles or formats using multiple clones
* **E-commerce** - Duplicate product variations and bulk duplicate similar listings across categories
* **Content Marketers** - Create A/B testing variations with the multiple clones feature
* **Site Migrations** - Bulk duplicate posts when restructuring or migrating content
* **Multilingual Sites** - Works seamlessly with WPML and Polylang for multilingual content

**What Gets Duplicated:**

* Post content and formatting
* Title and slug (with customizable suffixes or full editing)
* All custom fields and metadata
* Categories, tags, and custom taxonomies
* Featured images (with ability to change or remove)
* Post format and excerpt
* Comment and ping status
* Menu order
* Post parent (for hierarchical post types)

**What Doesn't Get Duplicated:**

* Comments (by design - prevents duplicate comment threads)
* Post ID and GUID (new unique identifiers assigned)

**How to Use:**

1. **Single Duplication**: Hover over any post and click "Duplicate [Post Type]" in the row actions, or click the "Duplicate Post" button in the Gutenberg editor
2. **Multiple Clones**: In the duplication modal, click the copy icon to create multiple copies of a single post (up to 50 clones)
3. **Bulk Duplication**: Select multiple posts using checkboxes, choose "Duplicate" from the Bulk Actions dropdown, then configure each post individually
4. **Customize Settings**: Edit title, slug, status, author, date, post type, featured image, and parent for each duplicate
5. **Configure Defaults**: Go to Settings > Post Duplicator to set default status, author, date, and title/slug suffixes
6. **Configure Post Types**: Go to Settings > Post Duplicator > Post Types to control which post types can be duplicated and which appear in the "Post Type" dropdown menu

**Default Settings:**

The plugin works immediately with sensible defaults:
* Duplicated posts are created as **Drafts** (prevents accidental publishing)
* Title suffix: **"Copy"**
* Slug suffix: **"copy"**
* Author: **Current User**
* Date: **Current Time**

All defaults can be customized in Settings > Post Duplicator.

**Integration:**

Post Duplicator works seamlessly with:
* WPML and Polylang (multilingual support)
* WooCommerce (excludes review counts automatically)
* ACF (Advanced Custom Fields) - preserves all field data
* WP Customer Area (special file duplication support)
* All custom post types and taxonomies

**Security:**

* Users without `publish_posts` capability cannot publish duplicates (forced to Pending)
* Non-authors cannot duplicate unpublished posts from other users
* Granular permission system controls who can duplicate posts
* All data is sanitized and validated before duplication

**Developer-Friendly:**

Includes hooks and filters for easy customization:
* `mtphr_post_duplicator_created` action
* `mtphr_post_duplicator_meta_{$key}_enabled` filter
* `mtphr_post_duplicator_meta_value` filter

== Installation ==

**Method 1: WordPress Admin (Recommended)**

1. Log in to your WordPress admin dashboard
2. Navigate to **Plugins > Add New**
3. Click **Upload Plugin** at the top of the page
4. Choose the `post-duplicator.zip` file
5. Click **Install Now**
6. After installation, click **Activate Plugin**

**Method 2: FTP/File Manager**

1. Extract the `post-duplicator` folder from the zip file
2. Upload the entire `post-duplicator` folder to `/wp-content/plugins/` on your server
3. Log in to your WordPress admin dashboard
4. Navigate to **Plugins**
5. Find "Post Duplicator" in the list and click **Activate**

**After Activation:**

The plugin is ready to use immediately with sensible defaults. You can start duplicating posts right away, or customize settings by going to **Settings > Post Duplicator**.

**Requirements:**

* WordPress 6.4 or higher
* PHP 7.4 or higher

== Frequently Asked Questions ==

= Do I need to configure settings before using the plugin? =

No! Post Duplicator works immediately with sensible defaults. Duplicated posts are created as drafts with the current user as the author. You can start duplicating posts right away.

However, you can customize default settings by going to **Settings > Post Duplicator** to configure:
* Default post status (draft, published, pending, or same as original)
* Default post type (same or convert to another type)
* Default author (current user or original author)
* Default date (current time or duplicate timestamp)
* Title and slug suffixes
* Date offset options

= Where can I find the duplicate option? =

The duplicate option appears in four places:

1. **Posts List (Single)**: Hover over any post in the All Posts (or any post type list) screen and click "Duplicate [Post Type]" in the row actions
2. **Posts List (Bulk)**: Select multiple posts using checkboxes, then choose "Duplicate" from the Bulk Actions dropdown
3. **Gutenberg Editor**: Click the "Duplicate Post" button in the top toolbar when editing a post
4. **Classic Editor**: Find the "Duplicate [Post Type]" button in the Publish meta box

The duplicate option works for all post statuses (published, draft, pending, etc.).

= What information gets duplicated? =

Post Duplicator creates a complete copy including:
* All post content and formatting (including Gutenberg blocks)
* Title and slug (with customizable suffixes or full editing)
* All custom fields and metadata (ACF, meta boxes, etc.)
* Categories, tags, and all custom taxonomies
* Featured images (with ability to change before duplication)
* Post format and excerpt
* Comment and ping status settings
* Menu order
* Post parent (for hierarchical post types)
* Post password (if applicable)

Comments are NOT duplicated (by design), and new unique IDs and GUIDs are assigned to each duplicate.

= Can I customize settings for individual duplications? =

Yes! When the duplication modal opens, you can customize:
* **Full title and slug** - Edit the complete title and slug, not just suffixes
* **Post status** - Draft, published, pending, or same as original
* **Post type** - Convert to a different post type during duplication
* **Post author** - Assign to any user or leave without author
* **Post date** - Use current time, original date, or pick a custom date with the calendar picker
* **Post parent** - Set parent for hierarchical post types (pages, etc.)
* **Featured image** - Change, replace, or remove the featured image
* **Taxonomies** - Select which categories, tags, and custom taxonomies to include
* **Custom meta** - Choose which custom fields to duplicate

For bulk duplications or multiple clones, expand each post item to customize it individually. These customizations only apply to that specific duplication and don't change your default settings.

= Can I duplicate posts to a different post type? =

Yes! You can convert posts to different post types during duplication:

1. Open the duplication modal
2. Click "Customize Settings"
3. Select a different post type from the "Post Type" dropdown
4. The duplicate will be created as the selected type

You can also set a default post type conversion in Settings > Post Duplicator.

= How do I control who can duplicate posts? =

Go to **Settings > Post Duplicator > Permissions** to configure role-based permissions:

* **Duplicate Posts**: Allows users to duplicate their own posts
* **Duplicate Others' Posts**: Allows users to duplicate posts created by other users

You can enable or disable these capabilities for each user role (Administrator, Editor, Author, etc.).

= Does the plugin work with custom post types? =

Yes! Post Duplicator works with all post types, including:
* Standard posts and pages
* Custom post types from themes
* Custom post types from plugins (WooCommerce products, etc.)
* Any registered post type in WordPress

= Will my custom fields be preserved? =

Yes! All custom fields and metadata are automatically copied to the duplicate. This includes:
* ACF (Advanced Custom Fields) data
* Custom meta boxes
* Any post meta stored in the database

= Does it work with multilingual plugins? =

Yes! Post Duplicator integrates seamlessly with:
* **WPML**: Automatically handles multilingual content correctly
* **Polylang**: Excludes translation taxonomies from duplication

= Can I schedule duplicates for future dates? =

Yes! Enable the "Offset Date" option in Settings > Post Duplicator to automatically schedule duplicates:

1. Enable "Offset Date"
2. Set the number of days, hours, minutes, or seconds to offset
3. Choose "Newer" (future) or "Older" (past) direction
4. All duplicates will use this date offset

You can also set a custom date for individual duplications using the date picker in the customization modal.

= What if I don't want comments duplicated? =

Comments are intentionally NOT duplicated. This prevents duplicate comment threads and maintains comment integrity. Each duplicate post starts with zero comments.

= Is there a way to bulk duplicate posts? =

Yes! Version 3.0.0 introduced bulk duplication:

1. Navigate to the posts list (All Posts, All Pages, etc.)
2. Select multiple posts using the checkboxes
3. Choose "Duplicate" from the Bulk Actions dropdown
4. Click Apply
5. Configure each duplicate individually in the modal
6. Click the duplicate button to create all posts

You can also create multiple clones of a single post by clicking the copy icon in the duplication modal.

= How do I create multiple clones of the same post? =

The multiple clones feature lets you create several copies of a single post:

1. Start duplicating any post (from posts list or editor)
2. Click the copy icon button in the modal header to enable multiple clones mode
3. Set the number of clones you want (1-50)
4. Each clone appears as a separate item that you can expand and customize
5. Edit the title, slug, status, author, date, featured image, and other settings for each clone
6. Click the duplicate button to create all clones at once

This is perfect for creating variations of content, A/B testing, or content series with similar structure.

= What's the difference between bulk duplication and multiple clones? =

Both features let you create multiple posts, but they work differently:

**Multiple Clones**:
* Creates multiple copies of a **single post**
* All clones start from the same original content
* Perfect for creating variations of the same post
* Example: Create 5 different versions of a blog post for A/B testing

**Bulk Duplication**:
* Duplicates **multiple different posts** at once
* Each duplicate is based on a different original post
* Perfect for duplicating a batch of posts with similar settings
* Example: Duplicate 10 different product posts from one category to another

Both allow you to customize each duplicate individually before creating them.

= Where are the plugin settings located? =

Go to **Settings > Post Duplicator** in your WordPress admin. You can also access it by clicking the "Settings" link on the Plugins page next to Post Duplicator.

The settings page includes four tabs:
* **General**: Configure duplication mode and post-duplication actions
* **Defaults**: Configure default duplication settings
* **Post Types**: Configure which post types can be duplicated and which appear in the "Post Type" dropdown menu
* **Permissions**: Control who can duplicate posts
* **Advanced**: Settings for duplicating special post statuses

== Screenshots ==

1. Single post duplicate button
2. Duplicate post modal
3. Duplicate post complete
4. Posts list duplicate link
5. Duplicate multiple clones
6. Bulk post duplication
7. Default settings
8. Permission settings
9. Advanced settings

== Changelog ==

= 3.0.8 [2025-02-07] =
* View Post button now uses get_permalink() for reliable URLs on sites with custom permalink structures

= 3.0.7 [2025-02-05] =
* WP Nested Pages integration
* Integration loads moved to plugins_loaded
* Filter for script enqueue on integration screens
* Integration folder structure rules

= 3.0.6 [2025-01-05] =
* Script loading updates and optimization
* Moved user query to API call

= 3.0.5 [2025-01-02] =
* Post meta duplication bug fixes
* Post date offset updates and fixes
* Settings sanitization bug fixes

= 3.0.4 [2025-12-31] =
* **New Feature**: Post Types settings section - Configure which post types can be duplicated and which appear in the "Post Type" dropdown menu
* **Enhancement**: Reusable upgrade notice system - Dismissable admin notices for version upgrades with plugin icon
* **Enhancement**: Upgrade notification for Post Types settings - Users upgrading to 3.0.4+ will see a helpful notice about the new Post Types configuration options

= 3.0.3 [2025-12-22] =
* Added general settings fields
* Added basic post duplication option
* Added after post duplication options for basic duplication
* Resolved html field duplication of ACF Pro fields

= 3.0.2 [2025-12-16] =
* Resolved duplication issue with Elementor & Divi
* Resolved duplciation issue with non REST post types
* Resolved component css issues
* API security updates

= 3.0.1 [2025-12-15] =
* Bug fix on widgets screen

= 3.0.0 [2025-12-14] =
* **New Feature**: Bulk duplication - Select and duplicate multiple posts at once
* **New Feature**: Multiple clones - Create up to 50 copies of a single post simultaneously
* **New Feature**: Featured image management - Set, replace, or remove featured images in the duplication modal
* **New Feature**: Post parent selection - Set parent posts for hierarchical post types
* **New Feature**: Interactive date picker - Calendar interface for setting custom dates
* **Enhancement**: Full title and slug editing - Edit complete title and slug, not just suffixes
* **Enhancement**: Enhanced modal interface - Expandable post items with individual settings
* **Enhancement**: Improved Gutenberg editor integration
* **Enhancement**: Real-time validation and slug sanitization
* Settings updates and performance improvements

= 2.48 [2025-10-04] =
* Mtphr Settings updates
* Bug fix

= 2.47 [2025-04-12] =
* Resolved _load_textdomain_just_in_time warning

= 2.46 [2025-04-07] =
* Resolved issue with WPML duplication

= 2.45 [2025-03-04] =
* Moved settings page to settings menu
* Added settings link to plugins screen

= 2.44 [2025-02-25] =
* Minor bug cleanup

= 2.43 [2025-02-23] =
* Default setting updates
* Default permission updates

= 2.42 [2025-02-16] =
* Added custom permissions and settings

= 2.41 [2025-02-16] =
* Added custom permissions and settings

= 2.40 [2025-02-11] =
* Added code to store current version for future updates
* Added code to resolve potential critical error for post types

= 2.39 [2025-02-10] =
* Bug fix

= 2.38 [2025-02-10] =
* Setting updates
* Removed ajax functionality
* Added WP API functionality
* Additional security updates

= 2.37 [2025-01-08] =
* Security update. Disabled duplicate post ability of non-published posts by non-authors.

= 2.36 [2024-09-02] =
* Security update. Fixed bug that allowed non-author to duplicate post.

= 2.35 [2024-05-14] =
* Allowed for center tag to be duplicated

= 2.34 [2024-04-27] =
* Added mtphr_post_duplicator_meta_{$key}_enabled filter to disable meta from duplicating
* Added mtphr_post_duplicator_meta_value filter to modify meta values before saving
* Disabled WooCommerce review count meta from duplicating

= 2.33 [2024-03-12] =
* Resolved special characters issue in duplicated title

= 2.32 =
* Ensured users without publish_post permissions can not publish posts on duplication

= 2.31 =
* Disabled Polylang post_translations taxonomy from attaching to duplicated posts

= 2.30 =
* Additional fix to issue with unicode characters in Gutenberg blocks

= 2.29 =
* Resolved issue with unicode characters in Gutenberg blocks

= 2.28 =
* Bug fix from last update

= 2.27 =
* Sanitization and validation updates
* Settings page optimization

= 2.26 =
* Removed duplicate functionality from post trash pages
* Database sanitization updates
* Asset loading path updates

= 2.25 =
* Multiple data sanitization updates

= 2.24 =
* Settings sanitization updates

= 2.23 =
* Added setting to limit post duplication to current user
* Added setting to setup duplicated post author to current user
* Set the default setting of duplicated post author to current user

= 2.22 =
* Fixed Gutenburg escaping in returns for ACF blocks

= 2.21 =
* Javascript update for WP 5.5

= 2.20 =
* Added "do_action( 'mtphr_post_duplicator_created', $original_id, $duplicate_id, $settings )" action for custom actions on duplicated post
* Added "mtphr_post_duplicator_action_row_link( $post )" function for custom post action rows
* Separated post duplicated function outsite of ajax call for custom uses
* Removed limitations of backend script to load only on specific pages

= 2.19 =
* Added Duplicate button to published post edit pages

= 2.18 =
* Modified javascript for allow duplication of duplicated page before page reload

= 2.17 =
* XSS vulnerability fix
* Language file updates

= 2.16 =
* Modified how post meta is saved to database
* Modified duplicate slug implementation
* Added file duplication support for the WP Customer Area plugin

= 2.15 =
* Added default value for duplicate post slug
* New setting to append a custom string to the duplicate post title

= 2.14 =
* New setting to append a custom string to the duplicate post slug

= 2.13 =
* Fixed bug due to "wp_old_slug_redirect" function in core

= 2.12 =
* Fixed page reload bug after duplication

= 2.11 =
* Added ability to duplicate posts to other post types

= 2.10 =
* Added page duplication support for the WP Customer Area plugin

= 2.9 =
* Now supports multiple values of a single custom field during duplication

= 2.8 =
* Added German language files
* Added Japanese language files
* Updated settings file for localization

= 2.7 =
* Modified duplicated posts data: post_date_gmt, post_modified, post_modified_gmt

= 2.6 =
* Changed the default published status to Draft

= 2.5 =
* Changed the default post date of duplicated posts to be the current time.

= 2.4 =
* Cleaned up some code.
* Updated localization code and files.

= 2.2 =
* Updated metaboxer code.

= 2.0 =
* Added a settings page to set 'post status' and 'date' of duplicated posts.

= 1.1 =
* Updated filenames and paths so the plugin works.

== Upgrade Notice ==

= 2.2 =
Code updates.

= 2.0 =
Upgrade Post Duplicator to add 'post status' and 'date' options to your duplicated posts.

= 1.1 =
Must upgrade in order for the plugin to work. The file paths where initially wrong as the plugin upload created a different directory name.

== Upgrade Notice ==

View Post button now uses get_permalink() for reliable URLs on sites with custom permalink structures