# Post Duplicator Settings

This guide explains all the settings available in the Post Duplicator plugin. Access these settings by going to **Settings > Post Duplicator** in your WordPress admin.

## Overview

The settings page is divided into three main sections:
1. **Defaults** - Configure default behavior for all duplications
2. **Permissions** - Control who can duplicate posts
3. **Advanced** - Fine-tune duplication behavior for special post types

## Defaults Section

These settings determine the default behavior when duplicating posts. You can override these defaults for individual duplications using the duplication modal.

### Post Status

**Options:**
- **Same as original** - The duplicate will have the same status as the original post
- **Draft** - All duplicates will be created as drafts (recommended default)
- **Published** - All duplicates will be published immediately
- **Pending** - All duplicates will be set to pending review

**Recommendation:** Keep this set to **Draft** to prevent accidentally publishing duplicates.

### Post Type

**Options:**
- **Same** - Duplicate will be the same post type as the original
- **[List of available post types]** - Choose a specific post type to convert duplicates to

**Use Case:** If you want to convert all duplicated posts to a different type (e.g., convert all duplicated Posts to a custom "Article" post type).

### Post Author

**Options:**
- **Current User** - The person duplicating the post becomes the author (recommended)
- **Original Post Author** - The duplicate keeps the original author

**Recommendation:** Use **Current User** so duplicates are attributed to the person creating them.

### Post Date

**Options:**
- **Duplicate Timestamp** - The duplicate will have the same publication date as the original
- **Current Time** - The duplicate will use the current date and time (recommended)

**Recommendation:** Use **Current Time** so duplicates have fresh timestamps.

### Duplicate Title

**Description:** Text that will be appended to the original post title.

**Default:** "Copy"

**Example:** 
- Original: "My Blog Post"
- Duplicate: "My Blog Post Copy"

**Tip:** You can customize this per duplication in the modal, but this sets the default suffix.

### Duplicate Slug

**Description:** Text that will be appended to the original post slug (URL-friendly version).

**Default:** "copy"

**Example:**
- Original slug: "my-blog-post"
- Duplicate slug: "my-blog-post-copy"

**Tip:** Keep this lowercase and URL-friendly. WordPress will automatically format it.

### Offset Date

**Description:** When enabled, allows you to set a time offset for duplicated post dates.

**Default:** Disabled

**How it works:**
1. Check the **Offset Date** checkbox
2. Set the offset amount:
   - **Days** - Number of days to offset
   - **Hours** - Number of hours to offset
   - **Minutes** - Number of minutes to offset
   - **Seconds** - Number of seconds to offset
3. Choose the direction:
   - **Newer** - The duplicate date will be in the future
   - **Older** - The duplicate date will be in the past

**Example:** 
- Original date: January 1, 2024
- Offset: 7 days, direction: Newer
- Duplicate date: January 8, 2024

**Use Case:** Useful for scheduling duplicates at specific intervals or creating posts with historical dates.

## Permissions Section

Control which user roles can duplicate posts and what they can duplicate.

### Role-Based Permissions

For each user role (Administrator, Editor, Author, Contributor, etc.), you can enable:

- **duplicate_posts** - Allows users to duplicate their own posts
- **duplicate_others_posts** - Allows users to duplicate posts created by other users

**How to Configure:**
1. Find the role you want to configure (e.g., "Author Permissions")
2. Check the boxes for the capabilities you want to grant
3. Click **Save Changes**

**Default Behavior:**
- Administrators typically have all permissions by default
- Other roles may need permissions granted manually

**Security Note:** Be careful when granting "duplicate_others_posts" to lower-level roles, as this allows them to copy content created by others.

## Advanced Section

These settings control duplication behavior for special post statuses created by other users.

### Draft Posts

**Question:** Should users be able to duplicate other users' draft posts?

**Options:**
- **Disabled** - Users cannot duplicate drafts created by others
- **Enabled** - Users can duplicate drafts created by others (if they have permission)

**Default:** Enabled

**Use Case:** Disable this if you want to prevent users from seeing or copying unpublished drafts from other authors.

### Pending Posts

**Question:** Should users be able to duplicate other users' pending posts?

**Options:**
- **Disabled** - Users cannot duplicate pending posts created by others
- **Enabled** - Users can duplicate pending posts created by others (if they have permission)

**Default:** Enabled

**Use Case:** Useful in editorial workflows where pending posts are under review.

### Private Posts

**Question:** Should users be able to duplicate other users' private posts?

**Options:**
- **Disabled** - Users cannot duplicate private posts created by others
- **Enabled** - Users can duplicate private posts created by others (if they have permission)

**Default:** Enabled

**Use Case:** Private posts are typically meant to be restricted. Consider disabling this for better privacy.

### Password Protected Posts

**Question:** Should users be able to duplicate other users' password protected posts?

**Options:**
- **Disabled** - Users cannot duplicate password protected posts created by others
- **Enabled** - Users can duplicate password protected posts created by others (if they have permission)

**Default:** Enabled

**Use Case:** Password protected posts are meant to be restricted. Consider disabling this for better security.

### Future Posts (Scheduled Posts)

**Question:** Should users be able to duplicate other users' scheduled/future posts?

**Options:**
- **Disabled** - Users cannot duplicate scheduled posts created by others
- **Enabled** - Users can duplicate scheduled posts created by others (if they have permission)

**Default:** Enabled

**Use Case:** Scheduled posts are often part of content calendars. Enable this if you want to allow copying scheduled content.

## Saving Settings

After making any changes:
1. Scroll to the bottom of the settings page
2. Click **Save Changes**
3. Your settings will be applied immediately

## Best Practices

### Recommended Default Settings

For most sites, we recommend:

- **Post Status:** Draft
- **Post Type:** Same
- **Post Author:** Current User
- **Post Date:** Current Time
- **Duplicate Title:** "Copy"
- **Duplicate Slug:** "copy"
- **Offset Date:** Disabled

### Security Recommendations

1. **Limit Permissions:** Only grant "duplicate_others_posts" to trusted roles (Editors, Administrators)
2. **Private Posts:** Consider disabling duplication of private posts for better privacy
3. **Password Protected:** Consider disabling duplication of password protected posts
4. **Review Advanced Settings:** Regularly review the Advanced section to ensure it matches your site's needs

### Workflow Tips

1. **Editorial Workflows:** If you have a review process, you may want to disable duplication of pending posts
2. **Content Templates:** If you use posts as templates, set default status to Draft so templates aren't accidentally published
3. **Multi-Author Sites:** Use "Current User" for Post Author to ensure proper attribution
4. **Scheduled Content:** Enable duplication of future posts if you want to copy scheduled content calendars

## Understanding Settings vs. Modal Overrides

**Settings Page (Defaults):**
- These are the default values used when duplicating
- They apply to all duplications unless overridden
- Set once and use repeatedly

**Duplication Modal (Overrides):**
- These allow you to customize individual duplications
- Override the defaults for that specific duplication only
- Don't affect future duplications

**Example:**
- Default status is set to "Draft" in settings
- You duplicate a post and change status to "Published" in the modal
- The duplicate is published, but future duplications will still default to "Draft"

## Troubleshooting

**Settings aren't saving:**
- Make sure you click "Save Changes" at the bottom
- Check that you have "manage_options" capability (typically Administrator role)
- Try refreshing the page

**Defaults aren't being applied:**
- Clear your browser cache
- Check that you saved the settings
- Verify you're looking at the correct settings section

**Permissions aren't working:**
- Ensure you saved the Permissions section
- Check that the user role actually has the capabilities assigned
- Some plugins may override WordPress capabilities





