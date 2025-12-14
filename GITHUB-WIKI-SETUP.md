# Setting Up GitHub Wiki Documentation

This guide explains how to use the help documentation files for your GitHub wiki.

## Files Available

- `HELP-DUPLICATING-POSTS.md` - Complete guide on duplicating posts
- `HELP-SETTINGS.md` - Complete guide on plugin settings

## Setting Up GitHub Wiki

### Step 1: Enable GitHub Wiki

1. Go to your GitHub repository
2. Click on **Settings**
3. Scroll down to **Features** section
4. Ensure **Wikis** is checked/enabled
5. Click **Save**

### Step 2: Create Wiki Pages

1. Go to your repository on GitHub
2. Click on the **Wiki** tab (or navigate to `https://github.com/YOUR_USERNAME/YOUR_REPO/wiki`)
3. Click **New Page** or **Create the first page**

### Step 3: Add Documentation

#### Option A: Copy-Paste Method

1. Open `HELP-DUPLICATING-POSTS.md` in a text editor
2. Copy all content
3. In GitHub wiki, create a new page named **"Duplicating-Posts"** (or similar)
4. Paste the content into the wiki editor
5. Click **Save Page**
6. Repeat for `HELP-SETTINGS.md` (create page named **"Settings"**)

#### Option B: Upload Method

1. In GitHub wiki, create a new page
2. Click the **Edit** button
3. You can paste the markdown content directly
4. GitHub will render it properly

### Step 4: Create Home Page

Create a main wiki page (`Home.md`) with links to your documentation:

```markdown
# Post Duplicator Documentation

Welcome to the Post Duplicator plugin documentation.

## Getting Started

- [Duplicating Posts](Duplicating-Posts) - Learn how to duplicate posts using various methods
- [Settings](Settings) - Configure plugin settings and permissions

## Quick Links

- [GitHub Repository](https://github.com/YOUR_USERNAME/YOUR_REPO)
- [Issues](https://github.com/YOUR_USERNAME/YOUR_REPO/issues)
```

## Linking Between Pages

GitHub wiki supports linking between pages using:

```markdown
[Link Text](Page-Name)
```

For example:
- `[Settings](Settings)` links to the Settings page
- `[Duplicating Posts](Duplicating-Posts)` links to the Duplicating Posts page

## Internal Anchor Links

The documentation uses anchor links for sections within the same page. These work automatically in GitHub wiki:

```markdown
[Link to section](#section-name)
```

GitHub automatically converts heading names to anchors (lowercase, spaces become hyphens).

## Tips

1. **Page Names**: Use descriptive names with hyphens (e.g., `Duplicating-Posts`, not `Duplicating Posts`)
2. **Home Page**: Set a home page by creating `Home.md` or using the wiki settings
3. **Sidebar**: GitHub wiki automatically creates a sidebar with all your pages
4. **Search**: GitHub wiki has built-in search functionality
5. **Version Control**: GitHub wiki pages are stored in a separate git repository (you can clone it)

## Cloning Wiki Repository

You can clone the wiki as a separate git repository:

```bash
git clone https://github.com/YOUR_USERNAME/YOUR_REPO.wiki.git
```

This allows you to:
- Edit pages locally
- Use version control for documentation
- Push changes back to GitHub

## Markdown Compatibility

The provided `.md` files are fully compatible with GitHub wiki markdown, including:

- ✅ Headings
- ✅ Lists (ordered and unordered)
- ✅ Bold and italic text
- ✅ Code blocks
- ✅ Tables
- ✅ Links
- ✅ Emojis (✅ ❌)

## Customization

You can customize the wiki by:

1. **Adding more pages** - Create additional documentation pages as needed
2. **Custom sidebar** - Edit the `_Sidebar.md` file in the wiki repository
3. **Footer** - Edit the `_Footer.md` file
4. **Home page** - Edit `Home.md` or set a custom home page

## Example Wiki Structure

```
Home
├── Duplicating-Posts
├── Settings
├── Installation
├── FAQ
└── Changelog
```

## Need Help?

- [GitHub Wiki Documentation](https://docs.github.com/en/communities/documenting-your-project-with-wikis)
- [GitHub Markdown Guide](https://docs.github.com/en/get-started/writing-on-github/getting-started-with-writing-and-formatting-on-github)
