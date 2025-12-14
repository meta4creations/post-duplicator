# Setting Up GitHub Wiki Documentation

This guide explains how to use the help documentation files for your GitHub wiki.

## Files Available

- `HELP-DUPLICATING-POSTS.md` - Complete guide on duplicating posts
- `HELP-SETTINGS.md` - Complete guide on plugin settings
- `.github/workflows/sync-wiki.yml` - Automated sync workflow (GitHub Actions)

## Setting Up GitHub Wiki

### Option A: Automated Sync (Recommended)

The repository includes a GitHub Actions workflow that automatically syncs your help files to the wiki.

#### Step 1: Enable GitHub Wiki

1. Go to your GitHub repository
2. Click on **Settings**
3. Scroll down to **Features** section
4. Ensure **Wikis** is checked/enabled
5. Click **Save**

#### Step 2: Initialize Wiki (One-Time Setup)

**Important:** The wiki repository must exist before the automation can work.

1. Go to your repository on GitHub
2. Click on the **Wiki** tab (or navigate to `https://github.com/YOUR_USERNAME/YOUR_REPO/wiki`)
3. Click **Create the first page** or **New Page**
4. Create any page (even a blank one) - this initializes the wiki repository
5. Save the page

#### Step 3: Verify Workflow

1. Go to your repository's **Actions** tab
2. You should see "Sync Wiki Documentation" workflow
3. After pushing changes to `HELP-*.md` files, the workflow should run automatically
4. Check the workflow logs if it doesn't work (see Troubleshooting below)

#### Step 4: Test the Sync

1. Make a small change to `HELP-DUPLICATING-POSTS.md`
2. Commit and push to your repository
3. Go to **Actions** tab - you should see the workflow running
4. Once complete, check the **Wiki** tab - your changes should be there

**The workflow automatically:**
- Syncs `HELP-DUPLICATING-POSTS.md` → `Duplicating-Posts.md` in wiki
- Syncs `HELP-SETTINGS.md` → `Settings.md` in wiki
- Creates a `Home.md` page if it doesn't exist
- Runs on every push to `HELP-*.md` files or files in `wiki/` directory

### Option B: Manual Setup

If you prefer to set up the wiki manually:

#### Step 1: Enable GitHub Wiki

1. Go to your GitHub repository
2. Click on **Settings**
3. Scroll down to **Features** section
4. Ensure **Wikis** is checked/enabled
5. Click **Save**

#### Step 2: Create Wiki Pages

1. Go to your repository on GitHub
2. Click on the **Wiki** tab
3. Click **New Page** or **Create the first page**

#### Step 3: Add Documentation

1. Open `HELP-DUPLICATING-POSTS.md` in a text editor
2. Copy all content
3. In GitHub wiki, create a new page named **"Duplicating-Posts"**
4. Paste the content into the wiki editor
5. Click **Save Page**
6. Repeat for `HELP-SETTINGS.md` (create page named **"Settings"**)

#### Step 4: Create Home Page

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

## Troubleshooting Automated Sync

If your wiki isn't updating automatically, check these common issues:

### Issue 1: Wiki Repository Not Initialized

**Symptoms:** Workflow runs but fails with "repository not found" error

**Solution:**
1. Go to your repository's Wiki tab
2. Create at least one page manually (even blank)
3. This initializes the wiki repository
4. Push your changes again - the workflow should now work

### Issue 2: Workflow Not Running

**Symptoms:** No workflow appears in Actions tab after pushing changes

**Check:**
1. Verify `.github/workflows/sync-wiki.yml` exists in your repository
2. Ensure you're pushing to the `main` or `master` branch (workflow is configured for these)
3. Check that you're modifying `HELP-*.md` files (workflow triggers on these paths)
4. Go to **Actions** tab and check if workflow is listed (even if not running)

**Solution:**
- Manually trigger the workflow:
  1. Go to **Actions** tab
  2. Click on "Sync Wiki Documentation"
  3. Click "Run workflow" button
  4. Select your branch and click "Run workflow"

### Issue 3: Workflow Runs But Wiki Doesn't Update

**Symptoms:** Workflow completes successfully but wiki pages don't change

**Check:**
1. Go to **Actions** tab
2. Click on the latest workflow run
3. Check the logs for any errors
4. Look for the "Commit and push wiki changes" step - did it find changes?

**Common causes:**
- Wiki repository might not exist (see Issue 1)
- Files might already be in sync (no changes detected)
- Permissions issue (workflow needs `contents: write` permission)

**Solution:**
- Check workflow logs for specific error messages
- Verify wiki repository exists (create a page manually if needed)
- Ensure workflow has proper permissions (should be automatic with `contents: write`)

### Issue 4: Branch Name Mismatch

**Symptoms:** Workflow doesn't trigger on your branch

**Check:**
- The workflow is configured for `main` and `master` branches
- If you're using a different branch name, update the workflow file:

```yaml
on:
  push:
    paths:
      - 'wiki/**'
      - 'HELP-*.md'
    branches:
      - main
      - master
      - your-branch-name  # Add your branch here
```

### Issue 5: Workflow Permission Errors

**Symptoms:** Workflow fails with permission errors

**Solution:**
1. Go to repository **Settings** → **Actions** → **General**
2. Under "Workflow permissions", ensure "Read and write permissions" is selected
3. Save changes
4. Re-run the workflow

### Manual Workflow Trigger

If automatic sync isn't working, you can always trigger it manually:

1. Go to your repository's **Actions** tab
2. Click on **"Sync Wiki Documentation"** workflow
3. Click **"Run workflow"** button (top right)
4. Select your branch
5. Click **"Run workflow"**

This will sync all your help files to the wiki immediately.

### Verify Workflow Configuration

Check that your workflow file (`.github/workflows/sync-wiki.yml`) includes:

- ✅ Triggers on `HELP-*.md` file changes
- ✅ Triggers on `wiki/**` directory changes
- ✅ Has `contents: write` permission
- ✅ Checks out both main repo and wiki repo
- ✅ Commits and pushes changes to wiki

## Need Help?

- [GitHub Wiki Documentation](https://docs.github.com/en/communities/documenting-your-project-with-wikis)
- [GitHub Markdown Guide](https://docs.github.com/en/get-started/writing-on-github/getting-started-with-writing-and-formatting-on-github)
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
