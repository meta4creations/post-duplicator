# Post Duplicator Documentation

Welcome to the Post Duplicator plugin documentation. This plugin allows you to create exact copies of any WordPress post type, including all content, custom fields, taxonomies, and metadata.

## 🚀 Quick Start

1. **Enable the Plugin**: Activate Post Duplicator from your WordPress plugins page
2. **Start Duplicating**: Navigate to any post list and hover over a post to see the "Duplicate" link
3. **Configure Settings**: Go to **Settings > Post Duplicator** to customize default behavior

## 📚 Documentation

### Getting Started

- **[Duplicating Posts](Duplicating-Posts)** - Complete guide on how to duplicate posts using various methods
  - From the posts list screen
  - From the Gutenberg editor
  - From the classic editor
  - Bulk duplication
  - Multiple clones

- **[Settings](Settings)** - Configure plugin settings and permissions
  - Default duplication settings
  - User role permissions
  - Advanced options

## ✨ Key Features

- **Duplicate Any Post Type** - Works with posts, pages, and all custom post types
- **Preserve Everything** - Copies content, custom fields, taxonomies, featured images, and more
- **Bulk Operations** - Duplicate multiple posts at once
- **Multiple Clones** - Create several copies of a single post simultaneously
- **Flexible Settings** - Customize default behavior for status, author, date, and more
- **Permission Control** - Granular control over who can duplicate posts
- **Smart Defaults** - Works immediately with sensible default settings

## 🎯 Common Use Cases

- **Content Templates**: Duplicate well-structured posts to use as templates
- **Content Variations**: Create multiple versions of posts for A/B testing
- **Content Migration**: Bulk duplicate posts during site migrations
- **Content Series**: Create series of related posts with similar structure
- **Testing**: Test changes without affecting original posts

## 🔧 Default Behavior

The plugin works immediately with these defaults:

- **Status**: Draft (prevents accidental publishing)
- **Title Suffix**: "Copy"
- **Slug Suffix**: "copy"
- **Author**: Current User
- **Date**: Current Time

All defaults can be customized in **Settings > Post Duplicator**.

## 🔗 Quick Links

- [GitHub Repository](https://github.com/meta4creations/post-duplicator)
- [Report an Issue](https://github.com/meta4creations/post-duplicator/issues)
- [View Source Code](https://github.com/meta4creations/post-duplicator)

## 💡 Tips

- **Quick Duplication**: Click duplicate and immediately click "Duplicate" again for fastest workflow
- **Bulk Operations**: Select multiple posts and use bulk actions for efficient duplication
- **Multiple Clones**: Use the multiple clones feature to create variations of the same content
- **Custom Settings**: Override defaults for individual duplications in the modal

## ❓ Need Help?

- Check the [Duplicating Posts](Duplicating-Posts) guide for step-by-step instructions
- Review the [Settings](Settings) documentation for configuration options
- [Open an issue](https://github.com/meta4creations/post-duplicator/issues) on GitHub for support

## 📝 What Gets Duplicated?

✅ **Included:**
- Post content and formatting
- Title and slug (with customizable suffixes)
- All custom fields and metadata
- Categories, tags, and custom taxonomies
- Featured images
- Post format and excerpt
- Comment and ping status
- Menu order and post parent

❌ **Not Duplicated:**
- Comments (by design)
- Post ID and GUID (new identifiers assigned)

---

**Plugin Version**: 3.0.0  
**Requires**: WordPress 5.0+  
**Tested up to**: WordPress 6.8.3
