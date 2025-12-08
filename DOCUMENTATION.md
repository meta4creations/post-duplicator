# Post Duplicator - Complete User Documentation

## Table of Contents
1. [Introduction](#introduction)
2. [Getting Started](#getting-started)
3. [How to Duplicate Posts](#how-to-duplicate-posts)
4. [Default Settings Configuration](#default-settings-configuration)
5. [Customizing Duplication Settings](#customizing-duplication-settings)
6. [Permissions Management](#permissions-management)
7. [Advanced Features](#advanced-features)
8. [Troubleshooting](#troubleshooting)

---

## Introduction

Post Duplicator is a powerful WordPress plugin that allows you to create exact duplicates of any post type in your WordPress site. Whether you're working with standard posts, pages, or custom post types, this plugin makes it easy to clone content while preserving all taxonomies, custom fields, and metadata.

### Key Features

- **Universal Post Type Support**: Duplicate any post type including posts, pages, and custom post types
- **Complete Data Preservation**: Automatically copies all taxonomies, custom fields, and metadata
- **Flexible Duplication Options**: Customize title, slug, status, author, date, and post type for each duplicate
- **Smart Defaults**: Configure default settings that apply to all duplications
- **Permission Control**: Granular control over who can duplicate posts
- **Modern Interface**: Beautiful modal interface with live preview of duplication settings
- **Cross-Post Type Duplication**: Convert posts to different post types during duplication

---

## Getting Started

### Installation

1. **Upload the Plugin**
   - Navigate to your WordPress admin dashboard
   - Go to **Plugins > Add New**
   - Click **Upload Plugin**
   - Choose the `post-duplicator` zip file
   - Click **Install Now**

2. **Activate the Plugin**
   - After installation, click **Activate Plugin**
   - The plugin is now ready to use!

### First Steps

Once activated, Post Duplicator is immediately functional with sensible defaults:
- Duplicated posts are created as **Drafts**
- Title suffix: **"Copy"**
- Slug suffix: **"copy"**
- Author: **Current User**
- Date: **Current Time**

You can start duplicating posts right away, or customize these defaults in the settings page.

---

## How to Duplicate Posts

Post Duplicator provides multiple ways to duplicate posts, making it accessible from wherever you're working in WordPress.

### Method 1: From the Posts List (All Posts Screen)

1. Navigate to **Posts > All Posts** (or any post type list screen)
2. Hover over the post you want to duplicate
3. Click the **"Duplicate [Post Type]"** link that appears in the row actions
4. A modal window will open showing a preview of the duplicate
5. Click **"Duplicate [Post Type]"** to create the duplicate, or click **"Customize Settings"** to modify options

### Method 2: From the Post Edit Screen (Gutenberg/Block Editor)

1. Open any published post in the block editor
2. Look for the **"Duplicate Post"** button in the top toolbar
3. Click the button to open the duplication modal
4. Review the preview and customize if needed
5. Click **"Duplicate [Post Type]"** to create the duplicate

### Method 3: From the Classic Editor

1. Open any published post in the classic editor
2. In the **Publish** meta box, find the **"Duplicate [Post Type]"** button
3. Click the button to duplicate the post immediately using default settings

### What Gets Duplicated?

When you duplicate a post, the following information is copied:

✅ **Post Content**: All text, blocks, and formatting  
✅ **Post Title**: (with optional suffix)  
✅ **Post Slug**: (with optional suffix)  
✅ **Custom Fields**: All post meta and custom field values  
✅ **Taxonomies**: Categories, tags, and custom taxonomies  
✅ **Featured Image**: (if applicable)  
✅ **Post Format**: (if applicable)  

❌ **Comments**: Comments are NOT duplicated (by design)  
❌ **Post ID**: New unique ID is assigned  
❌ **Post GUID**: New unique GUID is assigned  

---

## Default Settings Configuration

Configure default settings that will be applied to all duplications. These settings can be overridden on a per-duplication basis.

### Accessing Settings

1. Go to **Settings > Post Duplicator** in your WordPress admin
2. Or click the **Settings** link on the Plugins page

### Default Settings Explained

#### Post Status
Choose the default status for duplicated posts:
- **Same as original**: Keeps the original post's status
- **Draft**: Creates duplicates as drafts (recommended)
- **Published**: Immediately publishes duplicates
- **Pending**: Creates duplicates pending review

**Recommendation**: Use "Draft" to prevent accidentally publishing duplicates.

#### Post Type
Select the default post type for duplicates:
- **Same**: Duplicates maintain the original post type
- **[Any Post Type]**: Convert all duplicates to a specific post type

**Use Case**: Set to "Page" if you frequently convert posts to pages.

#### Post Author
Choose who should be set as the author of duplicated posts:
- **Current User**: The person duplicating becomes the author (recommended)
- **Original Post Author**: Maintains the original author

**Recommendation**: Use "Current User" for better content management.

#### Post Date
Set the default date/time for duplicated posts:
- **Duplicate Timestamp**: Uses the original post's date
- **Current Time**: Uses the current date/time (recommended)

**Recommendation**: Use "Current Time" to avoid confusion with old dates.

#### Duplicate Title
Enter text to append to the original title. Default: **"Copy"**

**Examples**:
- Input: `"Copy"` → "My Post Title Copy"
- Input: `" - Duplicate"` → "My Post Title - Duplicate"
- Input: `" (v2)"` → "My Post Title (v2)"

#### Duplicate Slug
Enter text to append to the original slug. Default: **"copy"**

**Examples**:
- Input: `"copy"` → "my-post-title-copy"
- Input: `"duplicate"` → "my-post-title-duplicate"
- Input: `"v2"` → "my-post-title-v2"

#### Offset Date
Enable to add or subtract time from the post date.

**When Enabled**:
- **Days**: Number of days to offset
- **Hours**: Number of hours to offset
- **Minutes**: Number of minutes to offset
- **Seconds**: Number of seconds to offset
- **Direction**: 
  - **Newer**: Adds time (future dates)
  - **Older**: Subtracts time (past dates)

**Example**: Set 1 day newer to schedule duplicates for tomorrow.

---

## Customizing Duplication Settings

For each duplication, you can customize settings without changing your defaults.

### Using the Customize Settings Feature

1. When the duplication modal opens, click **"Customize Settings"** (pencil icon)
2. The settings panel will expand, showing all customization options
3. Modify any of the following:

#### Title
- Edit the full title directly
- The slug will auto-update based on the title (unless manually edited)

#### Slug
- Edit the full slug manually
- WordPress-style sanitization is applied automatically

#### Post Status
- Override the default status for this specific duplication
- Options: Same, Draft, Published, Pending

#### Post Type
- Change the post type for this duplication
- Useful for one-off conversions (e.g., Post → Page)

#### Post Author
- Select any user from the dropdown
- Overrides the default author setting

#### Post Date
- Click the calendar icon to open a date/time picker
- Or type a date directly in the field
- Format: "Jan 1, 2025, 12:00 PM"

### Live Preview

The modal shows a real-time preview of:
- **Post Type**: What type the duplicate will be
- **Post Status**: The status it will have
- **Post Date**: When it will be dated
- **Post Author**: Who will be the author
- **Post URL**: The expected permalink

This preview updates automatically as you change settings.

---

## Permissions Management

Control who can duplicate posts with granular permission settings.

### Accessing Permissions

1. Go to **Settings > Post Duplicator**
2. Click on the **Permissions** tab

### Permission Types

For each user role, you can enable or disable:

#### Duplicate Posts
Allows users to duplicate their own posts.

**Example**: Enable for Editors so they can duplicate their own content.

#### Duplicate Others' Posts
Allows users to duplicate posts created by other users.

**Example**: Enable for Administrators to duplicate any content.

### Setting Permissions

1. Find the role you want to configure (Administrator, Editor, Author, etc.)
2. Check the boxes for the capabilities you want to grant
3. Click **Save Changes**

### Default Permissions

- **Administrators**: Full permissions (can duplicate any post)
- **Editors**: Can duplicate their own posts
- **Authors/Contributors**: Can duplicate their own posts
- **Subscribers**: No duplication permissions

### Security Notes

- Users without `publish_posts` capability cannot publish duplicates (forced to Pending)
- Non-authors cannot duplicate unpublished posts from other users (security feature)
- Permissions are checked on every duplication attempt

---

## Advanced Features

### Advanced Duplication Settings

In **Settings > Post Duplicator > Advanced**, control duplication of special post statuses:

#### Draft Posts
Control whether users can duplicate other users' draft posts.

#### Pending Posts
Control whether users can duplicate other users' pending posts.

#### Private Posts
Control whether users can duplicate other users' private posts.

#### Password Protected Posts
Control whether users can duplicate other users' password-protected posts.

#### Future Posts
Control whether users can duplicate other users' scheduled posts.

**Recommendation**: Keep these enabled unless you have specific security requirements.

### Cross-Post Type Duplication

Convert posts to different post types during duplication:

1. Open the duplication modal
2. Click **"Customize Settings"**
3. Select a different post type from the **Post Type** dropdown
4. The duplicate will be created as the selected type

**Use Cases**:
- Convert a Post to a Page
- Convert a Custom Post Type to another Custom Post Type
- Create variations of content in different formats

### Date Offset Feature

Schedule duplicates for specific times:

**Example Scenario**: Create a duplicate scheduled for 1 week from now

1. Enable **Offset Date** in default settings
2. Set **Days**: 7
3. Set **Direction**: Newer
4. All duplicates will be dated 7 days in the future

### Developer Hooks

For developers, Post Duplicator provides several hooks:

#### Actions
```php
// Fires after a post is duplicated
do_action( 'mtphr_post_duplicator_created', $original_id, $duplicate_id, $settings );
```

#### Filters
```php
// Disable specific meta keys from duplicating
add_filter( 'mtphr_post_duplicator_meta_{$key}_enabled', '__return_false' );

// Modify meta values before saving
add_filter( 'mtphr_post_duplicator_meta_value', function( $value, $key, $duplicate_id, $post_type ) {
    // Modify $value
    return $value;
}, 10, 4 );
```

### Integration with Other Plugins

Post Duplicator works seamlessly with:
- **WPML**: Automatically handles multilingual content
- **WooCommerce**: Excludes review counts from duplication
- **Polylang**: Excludes translation taxonomies
- **ACF (Advanced Custom Fields)**: Preserves all ACF field data
- **WP Customer Area**: Special support for file duplication

---

## Troubleshooting

### Common Issues and Solutions

#### "Duplicate" link doesn't appear

**Possible Causes**:
1. **Permissions**: Your user role doesn't have duplication permissions
   - **Solution**: Ask an administrator to grant permissions in Settings > Post Duplicator > Permissions

2. **Post Status**: The post must be published to show the duplicate link
   - **Solution**: Publish the post first, then duplicate it

3. **Post is in Trash**: Duplication is disabled for trashed posts
   - **Solution**: Restore the post from trash first

#### Duplicate doesn't include custom fields

**Possible Causes**:
1. **Filtered Out**: A filter may be disabling the meta key
   - **Solution**: Check if any plugins or themes are filtering `mtphr_post_duplicator_meta_{$key}_enabled`

2. **WooCommerce Review Count**: This is intentionally excluded
   - **Solution**: This is expected behavior

#### Can't change post type during duplication

**Possible Causes**:
1. **Post Type Not Available**: The target post type may not be registered
   - **Solution**: Ensure the target post type is active and registered

2. **Permissions**: You may not have permission to create that post type
   - **Solution**: Check your user capabilities for the target post type

#### Duplicate opens in new tab but I don't want that

**Current Behavior**: In Gutenberg editor, duplicates open in a new tab automatically.

**Workaround**: This is by design to allow you to continue working on the original post. Simply close the new tab if not needed.

#### Date offset not working

**Check**:
1. Is "Offset Date" enabled in default settings?
2. Are the offset values set correctly?
3. Is the direction (newer/older) set correctly?

**Solution**: Verify settings in Settings > Post Duplicator > Defaults

### Getting Help

If you encounter issues not covered here:

1. **Check WordPress Compatibility**: Ensure you're running WordPress 5.0+ and PHP 7.4+
2. **Check Plugin Conflicts**: Temporarily deactivate other plugins to test
3. **Check Theme Conflicts**: Switch to a default theme temporarily
4. **Review Error Logs**: Check your server error logs for PHP errors

### Best Practices

1. **Use Draft as Default**: Prevents accidentally publishing duplicates
2. **Set Current User as Author**: Makes it clear who created the duplicate
3. **Use Descriptive Suffixes**: Make duplicates easy to identify (e.g., "Copy", "Duplicate", "v2")
4. **Review Permissions**: Regularly audit who can duplicate posts
5. **Test Before Bulk Operations**: Test duplication settings before duplicating many posts

---

## Quick Reference

### Keyboard Shortcuts
- None currently (future feature)

### Default Settings Location
**Settings > Post Duplicator**

### Duplication Locations
- Posts list (row actions)
- Post edit screen (toolbar button)
- Classic editor (publish meta box)

### What's Duplicated
✅ Content, Title, Slug, Custom Fields, Taxonomies, Featured Image  
❌ Comments, Post ID, GUID

### Default Values
- Status: Draft
- Author: Current User
- Date: Current Time
- Title Suffix: "Copy"
- Slug Suffix: "copy"

---

*Last Updated: Version 2.48*

