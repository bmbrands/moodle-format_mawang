# Mawang Course Format - Setup Instructions

## Overview
The Mawang course format provides a modern, tile-based layout for Moodle courses with enhanced navigation, progress tracking, and visual appeal.

## Prerequisites
- Moodle 4.4 or higher
- PHP 8.1 or higher

## Installation

### Method 1: Manual Installation
1. Download the latest release from GitHub
2. Extract the zip file
3. Upload the `mawang` folder to `course/format/` in your Moodle installation
4. Visit your Moodle site as admin to complete the installation

### Method 2: ZIP Installation
1. Download the plugin ZIP file from GitHub
2. Go to Site Administration → Plugins → Install plugins
3. Upload the ZIP file and follow the installation process

## Required Dependencies
The Mawang course format works best with these complementary plugins:

### Essential (for full functionality):
- **local_modcustomfields**: Provides duration and video indicator custom fields
- **theme_mawang**: Provides the optimal visual styling

### Optional (for enhanced features):
- **filter_teacherprofile**: Displays teacher profile information in courses
- **assignsubmission_forms**: Enhanced form-based assignment submissions

## Configuration

### 1. Site Administration Settings
Navigate to: **Site Administration → Plugins → Course formats → Mawang format**

Available settings:
- **Show backlink in activities** (`format_mawang | cmbacklink`)
  - Default: No
  - Description: Show 'Back to ...' link in activities to return to course section

### 2. Course Level Setup
1. Create a new course or edit an existing one
2. In Course settings, set **Course format** to "Mawang format"
3. Configure sections and add activities as needed

### 3. Section Images
To add visual appeal to your course tiles:
1. Navigate to a course using Mawang format
2. Turn editing on
3. Click the section dropdown menu → "Edit section"
4. Upload an image in the section settings
5. The image will appear as the tile background

### 4. Custom Fields (if local_modcustomfields is installed)
The format automatically creates these custom fields:
- **Duration**: Time estimate for activities (in minutes)
- **Is Video**: Checkbox to mark video content

To use these:
1. Edit any activity
2. Scroll to "Custom fields" section
3. Set duration and video indicator as needed

## Features

### Tile-Based Layout
- Clean, modern tile interface
- Responsive design that works on all devices
- Visual section images for better engagement

### Progress Tracking
- Recently viewed section highlighting
- Activity completion progress bars
- Visual indicators for completed sections

### Enhanced Navigation
- Breadcrumb navigation in activities
- Previous/Next activity navigation
- Back to section links (if enabled)

### Custom Fields Integration
- Duration estimates displayed on activities
- Video content indicators
- Automatic calculation of section totals

## Troubleshooting

### Common Issues

**Problem**: Tiles not displaying correctly
**Solution**:
- Ensure theme_mawang is installed and active
- Clear Moodle caches (Site Administration → Development → Purge caches)

**Problem**: Custom fields not appearing
**Solution**:
- Verify local_modcustomfields plugin is installed
- Check that custom fields category exists
- Run the setup: `php admin/cli/scheduled_task.php --execute=format_mawang\task\setup_task`

**Problem**: Teacher profile not showing
**Solution**:
- Ensure filter_teacherprofile plugin is installed
- Activate the filter in Site Administration → Plugins → Filters

### Cache Issues
If changes don't appear immediately:
```bash
# Clear all caches
php admin/cli/purge_caches.php

# Or via web interface
Site Administration → Development → Purge caches
```

## Development

### Running Tests
```bash
# PHP Unit tests
vendor/bin/phpunit course/format/mawang/tests

# Behat tests
vendor/bin/behat --config /path/to/behatconfig course/format/mawang/tests/behat
```

### Code Standards
```bash
# Check coding standards
vendor/bin/phpcs course/format/mawang

# Check CSS standards
npm run lint:css
```

## Support
- GitHub Issues: https://github.com/bmbrands/moodle-format_mawang/issues
- Moodle Forums: Reference plugin name "format_mawang"

## License
GNU GPL v3 or later
