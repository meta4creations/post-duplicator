# Troubleshooting GitHub Actions Not Triggering

If your GitHub Actions workflow isn't running when you push via SourceTree, follow these steps:

## Quick Checks

### 1. Verify Workflow File is Pushed

The workflow file must be in the repository on GitHub:

```bash
# Check if file exists in remote
git ls-remote --heads origin master
git show origin/master:.github/workflows/sync-wiki.yml
```

Or check on GitHub:
- Go to your repository
- Navigate to `.github/workflows/sync-wiki.yml`
- Verify it exists and has the correct content

### 2. Check GitHub Actions is Enabled

1. Go to your repository on GitHub
2. Click **Settings**
3. Scroll to **Actions** → **General**
4. Under "Actions permissions", ensure one of these is selected:
   - ✅ "Allow all actions and reusable workflows"
   - ✅ "Allow local actions and reusable workflows"
5. Under "Workflow permissions", ensure:
   - ✅ "Read and write permissions" is selected
6. Click **Save**

### 3. Verify You're Pushing to the Right Branch

The workflow only triggers on `main` or `master` branch:

```bash
# Check current branch
git branch --show-current

# Check what branch you're pushing to
git remote show origin
```

In SourceTree:
- Check the branch dropdown at the top
- Ensure you're pushing to `master` or `main`

### 4. Check Actions Tab

1. Go to your repository on GitHub
2. Click the **Actions** tab
3. Look for "Sync Wiki Documentation (Always Run)" workflow
4. Check if there are any runs (even failed ones)
5. If you see runs, click on them to see the logs

### 5. Test with a Manual Trigger

1. Go to **Actions** tab
2. Click "Sync Wiki Documentation (Always Run)" in the left sidebar
3. Click **"Run workflow"** button (top right)
4. Select branch: `master`
5. Click **"Run workflow"**

If this works, the workflow is fine but auto-triggering isn't working.

## Common Issues with SourceTree

### Issue: Workflow File Not Committed

**Symptoms:** Workflow exists locally but not on GitHub

**Solution:**
1. In SourceTree, check if `.github/workflows/sync-wiki.yml` shows as uncommitted
2. If it's uncommitted:
   - Stage the file
   - Commit it
   - Push to remote

### Issue: Pushing to Wrong Remote

**Symptoms:** Pushing works but workflow doesn't trigger

**Check:**
1. In SourceTree, check **Repository** → **Repository Settings** → **Remotes**
2. Verify the remote URL matches your GitHub repository
3. Ensure you're pushing to `origin` (not a different remote)

### Issue: Workflow Runs But Doesn't Detect Changes

**Symptoms:** Workflow appears in Actions but says "No help files changed"

**Check the logs:**
1. Go to **Actions** tab
2. Click on the workflow run
3. Expand "Check if help files changed" step
4. Look at the output - it will show what files changed

**Solution:**
- The workflow checks if `HELP-*.md` files changed
- If you're only changing other files, the workflow will skip syncing
- This is expected behavior - it only syncs when help files change

## Debugging Steps

### Step 1: Verify Workflow Syntax

Check if the workflow file has any syntax errors:

1. Go to your repository on GitHub
2. Navigate to `.github/workflows/sync-wiki.yml`
3. GitHub will show syntax errors if any exist

### Step 2: Check Repository Settings

1. Go to **Settings** → **Actions** → **General**
2. Verify "Actions permissions" allows workflows
3. Check "Workflow permissions" is set to "Read and write"

### Step 3: Test with Command Line

Try pushing via command line to see if it's a SourceTree-specific issue:

```bash
# Make a small change
echo "# Test" >> HELP-DUPLICATING-POSTS.md

# Commit and push
git add HELP-DUPLICATING-POSTS.md
git commit -m "Test workflow trigger"
git push origin master
```

Then check the Actions tab to see if it triggered.

### Step 4: Check Workflow Logs

If the workflow runs but fails:

1. Go to **Actions** tab
2. Click on the failed workflow run
3. Expand each step to see error messages
4. Common issues:
   - Wiki repository doesn't exist (create a page manually first)
   - Permission errors (check repository settings)
   - File not found errors (check file paths)

## Force Workflow to Run

If you need to force the workflow to run regardless of file changes:

1. Go to **Actions** tab
2. Click "Sync Wiki Documentation (Always Run)"
3. Click **"Run workflow"**
4. Select branch: `master`
5. Click **"Run workflow"**

This will run the workflow immediately and sync your wiki.

## Still Not Working?

If none of the above works:

1. **Check GitHub Status**: Visit https://www.githubstatus.com/ to see if GitHub Actions is having issues

2. **Verify Repository Access**: Ensure you have write access to the repository

3. **Check Organization Settings**: If this is an organization repository, check if Actions are enabled at the organization level

4. **Contact Support**: If all else fails, the issue might be with GitHub Actions itself - check GitHub's status page or contact support

## Expected Behavior

When working correctly:
- ✅ Every push to `master`/`main` triggers the workflow
- ✅ Workflow checks if `HELP-*.md` files changed
- ✅ If changed, it syncs to wiki
- ✅ If not changed, it skips (no error, just skips)

The workflow will appear in the Actions tab even when it skips (you'll see "No help files changed, skipping sync" in the logs).
