# Duplicating Posts

This guide explains how to duplicate posts using the Post Duplicator plugin. And stuff.

## Overview

Post Duplicator allows you to create exact copies of any post type, including all content, custom fields, taxonomies, and metadata. This is useful for creating templates, making variations of existing content, or quickly creating similar posts.

## Methods to Duplicate Posts

### Method 1: From the Posts List Screen

1. Navigate to your posts list (e.g., **Posts > All Posts**, **Pages > All Pages**, or any custom post type list)
2. Hover over the post you want to duplicate
3. Click the **"Duplicate [Post Type]"** link that appears in the row actions
   - For example: "Duplicate Post", "Duplicate Page", etc.
4. A modal window will open with duplication options
5. Review and adjust settings if needed (see [The Duplication Modal](#the-duplication-modal) below)
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

### Method 4: Bulk Duplication (Multiple Posts)

You can duplicate multiple posts at once from the posts list screen:

1. Navigate to your posts list (e.g., **Posts > All Posts**, **Pages > All Pages**, or any custom post type list)
2. Select the posts you want to duplicate by checking the boxes next to each post
   - You can select multiple posts across different pages
   - Use the checkbox in the table header to select all posts on the current page
3. In the **Bulk Actions** dropdown at the top of the list, select **"Duplicate [Post Type]"**
   - For example: "Duplicate Posts", "Duplicate Pages", etc.
4. Click the **Apply** button
5. A modal window will open showing all selected posts
6. Configure settings for each post individually, or apply global settings to all posts
7. Click **"Duplicate [Post Type]"** to create duplicates for all selected posts

**Bulk Duplication Tips:**
- Each post in the list can be expanded to configure individual settings
- You can remove posts from the list before duplicating by clicking the delete/remove button
- All duplicates are created sequentially, so larger batches may take longer
- The page will automatically refresh after successful bulk duplication

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

### Multiple Clones Mode

When duplicating a single post, you can create multiple copies at once:

1. Open the duplication modal for a single post
2. Look for the **"Create Multiple Copies"** toggle or option
3. Enable multiple clones mode
4. Set the number of copies you want to create (default is 2)
5. Each clone will appear in the list with its own settings
6. You can customize settings for each clone individually:
   - Edit titles and slugs
   - Change post types, status, author, or date
   - Modify taxonomies and custom meta
7. Remove individual clones by clicking the delete button (minimum 1 clone required)
8. Click **"Duplicate [Post Type]"** to create all clones at once

**Multiple Clones Tips:**
- All clones are created from the same original post
- Each clone can have different settings
- Clones are numbered automatically in titles (e.g., "My Post Copy", "My Post Copy 2", "My Post Copy 3")
- Useful for creating variations or A/B testing content

### Bulk Duplication Modal

When duplicating multiple posts via bulk action, the modal shows:

- **Post List**: All selected posts displayed in a scrollable list
- **Individual Configuration**: Each post can be expanded to configure its own settings
- **Global Settings**: Some settings can be applied to all posts at once
- **Remove Posts**: Delete posts from the list before duplicating if needed
- **Sequential Processing**: Posts are duplicated one after another

### After Duplication

Once the duplicate is created, you'll see:
- A success message
- **View Post** button - Opens the duplicate post on the frontend
- **Edit Post** button - Opens the duplicate post in the WordPress editor

For bulk duplications, you'll see a summary of all created duplicates, and the page will refresh to show the new posts in your list.

## Duplicating Multiple Posts

Post Duplicator offers two powerful ways to create multiple duplicates:

### Option 1: Bulk Duplication (Different Posts)

Duplicate multiple different posts at once:

1. **From Posts List**: Select multiple posts using checkboxes, then use the Bulk Actions dropdown
2. **Configure Each Post**: In the modal, expand each post to set individual settings
3. **Apply to All**: Some settings can be applied globally to all selected posts
4. **Create All**: Click the duplicate button to create all duplicates sequentially

**Best For:**
- Duplicating a batch of posts with similar settings
- Content migrations
- Creating variations of multiple existing posts
- Bulk content updates

### Option 2: Multiple Clones (Same Post)

Create multiple copies of a single post:

1. **Open Duplication Modal**: Start duplicating any single post
2. **Enable Multiple Clones**: Toggle the "Create Multiple Copies" option
3. **Set Count**: Choose how many copies to create (2, 3, 5, 10, etc.)
4. **Customize Each Clone**: Each clone can have unique settings
5. **Create All**: All clones are created from the same original

**Best For:**
- Creating variations of the same content
- A/B testing different versions
- Content series with similar structure
- Template-based content creation

### Comparison

| Feature | Bulk Duplication | Multiple Clones |
|---------|-----------------|----------------|
| **Source** | Multiple different posts | One single post |
| **Use Case** | Different content, similar settings | Same content, different variations |
| **Configuration** | Individual settings per post | Individual settings per clone |
| **Speed** | Sequential (one after another) | Sequential (one after another) |

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
- **Bulk Operations**: Use bulk duplication to efficiently duplicate multiple posts at once - perfect for content migrations or creating multiple variations
- **Multiple Clones**: Create several copies of a single post at once for A/B testing, variations, or content series
- **Testing**: Use duplication to test changes without affecting the original post
- **Scheduling**: Duplicate published posts and schedule them for future publication dates
- **Content Series**: Use multiple clones to create a series of related posts with similar structure but different content
- **Batch Processing**: For large batches, consider duplicating in smaller groups to avoid timeouts

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

**Bulk duplication isn't working:**
- Make sure you've selected at least one post before applying the bulk action
- Check that all selected posts are of the same post type (bulk actions work per post type)
- Verify you have permissions to duplicate all selected posts
- For large batches, try duplicating in smaller groups

**Multiple clones aren't being created:**
- Ensure the "Create Multiple Copies" option is enabled
- Check that the clone count is set to at least 2
- Verify you have permission to create multiple posts





