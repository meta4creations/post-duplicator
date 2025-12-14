# Duplicating Posts

This guide explains how to duplicate posts using the Post Duplicator plugin.

## Overview

Post Duplicator allows you to create exact copies of any post type, including all content, custom fields, taxonomies, and metadata. This is useful for creating templates, making variations of existing content, or quickly creating similar posts.

## Methods to Duplicate Posts

### Method 1: From the Posts List Screen

1. Navigate to your posts list (e.g., **Posts > All Posts**, **Pages > All Pages**, or any custom post type list)
2. Hover over the post you want to duplicate
3. Click the **"Duplicate [Post Type]"** link that appears in the row actions
   - For example: "Duplicate Post", "Duplicate Page", etc.
4. A modal window will open with duplication options
5. Review and adjust settings if needed (see [Duplication Settings](#duplication-settings) below)
6. Click the **"Duplicate [Post Type]"** button to create the duplicate

### Method 2: From the Post Edit Screen (Gutenberg)

1. Open the post you want to duplicate in the WordPress block editor
2. Look for the **"Duplicate Post"** button in the Gutenberg toolbar (top of the screen)
3. Click the button to open the duplication modal
4. Review and adjust settings if needed
5. Click the **"Duplicate [Post Type]"** button to create the duplicate

### Method 3: From the Classic Editor

1. Open the post you want to duplicate in the classic editor
2. In the **Publish** meta box (right sidebar), find the **"Duplicate [Post Type]"** button
   - Note: This button only appears for published posts
3. Click the button to open the duplication modal
4. Review and adjust settings if needed
5. Click the **"Duplicate [Post Type]"** button to create the duplicate

## The Duplication Modal

When you click to duplicate a post, a modal window opens with various options. Here's what you'll see:

### Basic Settings

- **Title**: The title for the duplicated post (defaults to original title + "Copy")
- **Slug**: The URL-friendly version of the title (auto-generated from title, but can be edited)
- **Featured Image**: Preview and option to change the featured image
- **Post Type**: Choose the post type for the duplicate (defaults to same type)
- **Post Status**: Choose the status (Draft, Published, Pending, or Same as original)
- **Post Author**: Select who should be listed as the author
- **Post Date**: Set the publication date for the duplicate

### Advanced Options

- **Taxonomies**: Toggle to include or exclude categories, tags, and custom taxonomies
- **Custom Meta**: Toggle to include or exclude custom fields and metadata

### After Duplication

Once the duplicate is created, you'll see:
- A success message
- **View Post** button - Opens the duplicate post on the frontend
- **Edit Post** button - Opens the duplicate post in the WordPress editor

## What Gets Duplicated

The following content is automatically copied to the duplicate:

✅ **Included by Default:**
- Post content and formatting
- Title and slug (with customizable suffixes)
- All custom fields and metadata
- Categories, tags, and custom taxonomies
- Featured images
- Post format
- Post excerpt
- Comment and ping status settings
- Post password (if applicable)
- Menu order
- Post parent (for hierarchical post types)

❌ **Not Duplicated:**
- Comments (by design - prevents duplicate comment threads)
- Post ID and GUID (new unique identifiers are assigned)

## Quick Duplication

For the fastest duplication:
1. Click the duplicate link/button
2. Click **"Duplicate [Post Type]"** immediately (uses default settings)
3. The duplicate will be created as a Draft with "Copy" appended to the title

## Customizing Individual Duplications

You can override the default settings for any individual duplication:

1. Open the duplication modal
2. Adjust any of the settings:
   - Change the title or slug
   - Select a different post type
   - Change the status
   - Set a different author
   - Modify the date
   - Toggle taxonomies or custom meta on/off
3. Click **"Duplicate [Post Type]"** to create with your custom settings

## Permissions

Your ability to duplicate posts depends on your user role and permissions:

- **Duplicate Posts**: Allows you to duplicate your own posts
- **Duplicate Others' Posts**: Allows you to duplicate posts created by other users

If you don't see the duplicate option, contact your site administrator to grant the necessary permissions.

## Tips

- **Creating Templates**: Duplicate a well-structured post to use as a template for future content
- **Making Variations**: Create multiple versions of a post with different titles or content
- **Bulk Operations**: While you can't duplicate multiple posts at once, you can quickly duplicate individual posts one after another
- **Testing**: Use duplication to test changes without affecting the original post
- **Scheduling**: Duplicate published posts and schedule them for future publication dates

## Troubleshooting

**I don't see the duplicate option:**
- Check that you have the necessary permissions (contact your administrator)
- Make sure you're not viewing the Trash page
- Some post types may have restrictions

**The duplicate button doesn't work:**
- Refresh the page and try again
- Check your browser console for JavaScript errors
- Ensure you have JavaScript enabled

**The duplicate isn't created:**
- Check that you have permission to create posts of that type
- Verify your user role has the necessary capabilities
- Contact your site administrator if issues persist





