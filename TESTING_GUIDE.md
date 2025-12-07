# Duplicate Modal Testing Guide

## Overview
The duplicate post modal has been successfully implemented and built. This document outlines the testing steps.

## Features Implemented

### 1. Backend Changes
- ✅ Added default settings to `postDuplicatorVars` in scripts.php
- ✅ Includes post type choices and status choices
- ✅ Settings are available to both post list and Gutenberg editor scripts

### 2. Modal Components
- ✅ **DuplicateModal.js** - Main modal component with preview and settings
- ✅ **SettingsSummary.js** - Compact display of default settings
- ✅ **DuplicateSettingsFields.js** - Full settings editor with all field types
- ✅ **Styling** - Complete CSS for modal, preview, and settings sections

### 3. Integration
- ✅ **Post List Screen** - Click handler updated to open modal
- ✅ **Gutenberg Editor** - Button now opens modal instead of immediate duplication
- ✅ **Dynamic Preview** - Updates in real-time as settings change

## Testing Steps

### Test 1: Post List Screen Modal

1. Navigate to Posts → All Posts (or any custom post type)
2. Find a post and click "Duplicate Post" link in row actions
3. **Expected Result:**
   - Modal opens with preview section showing:
     - Title with suffix (e.g., "Original Title Copy")
     - Post Type
     - Post Status
   - Settings summary shows all default settings in compact format
   - "Customize Settings" button is visible
   - "Cancel" and "Duplicate {type} as {status}" buttons at bottom

4. Click "Customize Settings"
5. **Expected Result:**
   - Settings summary collapses
   - Full settings editor appears with all fields:
     - Post Status dropdown
     - Post Type dropdown
     - Post Author radio buttons
     - Post Date radio buttons
     - Title suffix text field
     - Slug suffix text field
     - Offset Date checkbox
     - Time offset fields (appear when checkbox is checked)
     - Offset direction radio buttons

6. Modify some settings (e.g., change status to "Pending", add different title suffix)
7. **Expected Result:**
   - Preview section updates immediately to reflect changes
   - Button label updates to show new status

8. Click "Duplicate {type} as {status}" button
9. **Expected Result:**
   - Post is duplicated with custom settings (not defaults)
   - Page refreshes/redirects showing success
   - New post appears in list with overridden settings

### Test 2: Gutenberg Editor Modal

1. Open any published post in the Gutenberg editor
2. Look for "Duplicate Post" button in the right sidebar (Post → Status section)
3. Click the "Duplicate {Post Type}" button
4. **Expected Result:**
   - Same modal opens as in post list test
   - Preview shows current post title with suffix
   - All settings work the same

5. Test customizing settings and duplicating
6. **Expected Result:**
   - New post opens in new tab with custom settings applied

### Test 3: Settings Override Verification

1. Go to Settings → Post Duplicator
2. Note the default settings (e.g., Status: Draft, Title suffix: "Copy")
3. Open a post and click duplicate
4. In the modal, change settings to different values
5. Duplicate the post
6. **Expected Result:**
   - New post uses the overridden settings from modal
   - Default settings in Settings → Post Duplicator remain unchanged

### Test 4: Cancel and Close

1. Open duplicate modal from any location
2. Make changes to settings
3. Click "Cancel" or the X button
4. **Expected Result:**
   - Modal closes
   - No duplication occurs
   - If reopened, settings reset to defaults

### Test 5: Time Offset

1. Open duplicate modal
2. Click "Customize Settings"
3. Check "Offset Date" checkbox
4. **Expected Result:**
   - Time offset fields appear (Days, Hours, Minutes, Seconds)
   - Offset direction radio buttons appear

5. Enter values and select direction
6. Duplicate the post
7. **Expected Result:**
   - New post has date offset applied according to settings

## Known Limitations

- The modal requires WordPress Components library (included via Gutenberg)
- Post data is fetched via WP REST API for post list screen clicks
- Settings are processed client-side before sending to API

## Files Modified/Created

### Modified
- `includes/scripts.php` - Added settings data to localized script
- `assets/src/index.js` - Converted to React with modal integration
- `assets/src/gutenberg-button.js` - Added modal integration
- `assets/src/css/index.scss` - Added modal styling

### Created
- `assets/src/components/DuplicateModal.js`
- `assets/src/components/DuplicateSettingsFields.js`
- `assets/src/components/SettingsSummary.js`

## Build Status
✅ Build completed successfully with no errors
✅ No linting errors detected

