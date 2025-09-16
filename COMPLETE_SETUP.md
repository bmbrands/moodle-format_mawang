# Mawang Plugin Suite - Complete Setup Guide

This guide provides comprehensive installation and configuration instructions for the complete Mawang plugin suite designed by Madison Wang and implemented by Bas Brands.

## 📦 Plugin Overview

The Mawang suite consists of five complementary plugins:

| Plugin | Type | Purpose |
|--------|------|---------|
| **format_mawang** | Course Format | Tile-based course layout with progress tracking |
| **theme_mawang** | Theme | Visual design implementing Madison Wang's Figma designs |
| **local_modcustomfields** | Local | Custom fields for activity duration and content type |
| **filter_teacherprofile** | Filter | Teacher profile display in course content |
| **assignsubmission_forms** | Assignment Submission | Custom form builder for assignments |

## 🚀 Quick Start

### Recommended Installation Order
1. **local_modcustomfields** (provides custom fields)
2. **filter_teacherprofile** (provides teacher profiles)
3. **theme_mawang** (provides visual styling)
4. **format_mawang** (course format - requires above plugins)
5. **assignsubmission_forms** (optional - enhanced assignments)

## 📋 Prerequisites

### System Requirements
- **Moodle**: 4.4 or higher
- **PHP**: 8.1 or higher
- **Database**: MySQL 8.0+, PostgreSQL 13+, or MariaDB 10.6+
- **Web Server**: Apache 2.4+ or Nginx 1.18+

### Administrative Access
- Moodle site administrator privileges
- Server file system access (for manual installation)
- Command line access (recommended for maintenance)

## 🛠️ Installation Methods

### Method 1: Manual Installation (Recommended)

1. **Download All Plugins**
   ```bash
   # Create temporary directory
   mkdir mawang-plugins && cd mawang-plugins

   # Download all plugins
   curl -L -o format_mawang.zip https://github.com/bmbrands/moodle-format_mawang/archive/refs/heads/main.zip
   curl -L -o theme_mawang.zip https://github.com/bmbrands/moodle-theme_mawang/archive/refs/heads/main.zip
   curl -L -o local_modcustomfields.zip https://github.com/bmbrands/moodle-local_modcustomfields/archive/refs/heads/main.zip
   curl -L -o filter_teacherprofile.zip https://github.com/bmbrands/moodle-filter_teacherprofile/archive/refs/heads/main.zip
   curl -L -o assignsubmission_forms.zip https://github.com/bmbrands/moodle-assignsubmission_forms/archive/refs/heads/main.zip
   ```

2. **Extract and Install**
   ```bash
   # Extract to Moodle directory structure
   unzip format_mawang.zip -d /path/to/moodle/course/format/
   unzip theme_mawang.zip -d /path/to/moodle/theme/
   unzip local_modcustomfields.zip -d /path/to/moodle/local/
   unzip filter_teacherprofile.zip -d /path/to/moodle/filter/
   unzip assignsubmission_forms.zip -d /path/to/moodle/mod/assign/submission/

   # Rename directories (remove -main suffix)
   cd /path/to/moodle
   mv course/format/moodle-format_mawang-main course/format/mawang
   mv theme/moodle-theme_mawang-main theme/mawang
   mv local/moodle-local_modcustomfields-main local/modcustomfields
   mv filter/moodle-filter_teacherprofile-main filter/teacherprofile
   mv mod/assign/submission/moodle-assignsubmission_forms-main mod/assign/submission/forms
   ```

3. **Complete Installation**
   - Visit your Moodle site as administrator
   - Follow the installation notifications
   - Complete all plugin installations in the recommended order

### Method 2: ZIP Upload Installation

For each plugin:
1. Download ZIP file from GitHub
2. Go to **Site administration** → **Plugins** → **Install plugins**
3. Upload ZIP file
4. Follow installation prompts
5. Repeat for all plugins

## ⚙️ Configuration

### 1. Local ModCustomFields Setup

**Navigate to**: Site administration → Notifications
- Plugin automatically creates "Activity custom fields" category
- Default fields: Duration (number), Is Video (checkbox)

**Verify Installation**:
```bash
# Check custom fields are created
mysql> SELECT * FROM mdl_customfield_category WHERE component = 'local_modcustomfields';
```

### 2. Filter TeacherProfile Setup

**Enable Filter**:
1. **Site administration** → **Plugins** → **Filters** → **Manage filters**
2. Enable "Teacher Profile" filter
3. Set to "On" or "On but disabled by default"

**Configure Settings**:
1. **Site administration** → **Plugins** → **Filters** → **Teacher Profile**
2. Set **Teacher Profile Custom Role** (optional)
3. Configure **Teacher Quality Standard** custom fields

**Create Custom User Profile Fields**:
1. **Site administration** → **Users** → **User profile fields**
2. Add fields like: qualification, experience, specialization
3. Add field shortnames to filter settings

### 3. Theme Mawang Setup

**Activate Theme**:
1. **Site administration** → **Appearance** → **Themes** → **Theme selector**
2. Select "Mawang" as default theme
3. Save changes

**Configure Theme**:
1. **Site administration** → **Appearance** → **Themes** → **Mawang**
2. Upload site logo and favicon
3. Configure footer content
4. Add custom CSS if needed

### 4. Format Mawang Setup

**Global Settings**:
1. **Site administration** → **Plugins** → **Course formats** → **Mawang format**
2. Configure "Show backlink in activities" setting

**Course Setup**:
1. Create new course or edit existing
2. Set **Course format** to "Mawang format"
3. Turn editing on
4. Add section images via "Edit section"
5. Configure activities with custom fields

### 5. Assignment Forms Setup (Optional)

**Enable Plugin**:
1. **Site administration** → **Plugins** → **Assignment submission plugins**
2. Enable "Form" submission plugin

**Create Form Assignment**:
1. Add new Assignment activity
2. In submission types, enable "Form"
3. Use form builder to create custom forms

## 🎯 Usage Examples

### Creating a Complete Mawang Course

1. **Course Setup**
   ```
   Course name: Introduction to Mathematics
   Course format: Mawang format
   Theme: Mawang (site-wide)
   ```

2. **Section Configuration**
   ```
   Section 1: "Getting Started"
   - Upload section image
   - Add introduction video (mark as video content, 15 minutes)
   - Add reading material (30 minutes duration)
   ```

3. **Teacher Profile**
   ```
   In course description, add:
   ## Meet Your Instructor
   [[course_teacherprofile]]
   ```

4. **Activity Custom Fields**
   ```
   Video Introduction:
   - Duration: 15 (minutes)
   - Is Video: ✓

   Reading Assignment:
   - Duration: 30 (minutes)
   - Is Video: ☐
   ```

### Using Teacher Profiles

**Basic Usage**:
```
Welcome to our course!

[[course_teacherprofile]]

I look forward to working with you this semester.
```

**Specific Instructor**:
```
## Course Team

**Lead Instructor:**
[[course_teacherprofile:123]]

**Teaching Assistant:**
[[course_teacherprofile:456]]
```

## 🔍 Troubleshooting

### Common Issues

#### Plugins Not Installing
```bash
# Check file permissions
chmod -R 755 /path/to/moodle/course/format/mawang
chmod -R 755 /path/to/moodle/theme/mawang
chmod -R 755 /path/to/moodle/local/modcustomfields
chmod -R 755 /path/to/moodle/filter/teacherprofile

# Clear caches
php admin/cli/purge_caches.php
```

#### Theme Not Applying
1. Verify theme is selected in theme selector
2. Clear browser cache
3. Check for CSS compilation errors

#### Custom Fields Not Showing
```bash
# Run custom field setup manually
php admin/cli/scheduled_task.php --execute=format_mawang\task\setup_task

# Check database
mysql> SELECT * FROM mdl_customfield_category WHERE component = 'local_modcustomfields';
```

#### Teacher Profile Not Rendering
1. Verify filter is enabled and active
2. Check teacher has appropriate role/capability
3. Ensure filter syntax is correct: `[[course_teacherprofile]]`

### Performance Optimization

```bash
# Compile theme SCSS
php admin/cli/build_theme_css.php --themes=mawang

# Clear all caches
php admin/cli/purge_caches.php

# Update component cache
php admin/cli/upgrade.php --non-interactive
```

## 🧪 Testing Installation

### Verification Checklist

- [ ] All plugins installed without errors
- [ ] Theme applied and displaying correctly
- [ ] Custom fields appear in activity settings
- [ ] Teacher profile filter processes correctly
- [ ] Course format displays tiles properly
- [ ] Assignment forms plugin enabled (if used)

### Test Course Creation

```bash
# Create test course via CLI
php admin/cli/create_course.php \
  --fullname="Mawang Test Course" \
  --shortname="test-mawang" \
  --format="mawang" \
  --visible=1
```

### Test Cases

1. **Create test course with Mawang format**
2. **Upload section images**
3. **Add activities with duration custom fields**
4. **Test teacher profile display**
5. **Verify theme styling**
6. **Test on mobile devices**

## 📊 Monitoring and Maintenance

### Regular Maintenance

```bash
# Weekly cache clearing
php admin/cli/purge_caches.php

# Monthly plugin updates check
# Visit Site administration → Plugins → Plugins overview

# Quarterly performance review
# Check page load times and user feedback
```

### Log Monitoring

```bash
# Check for plugin errors
tail -f /path/to/moodle/config/apache2/error.log | grep -i "mawang\|teacherprofile\|modcustomfields"

# Check Moodle logs
mysql> SELECT * FROM mdl_logstore_standard_log WHERE component LIKE '%mawang%' ORDER BY timecreated DESC LIMIT 50;
```

## 🔒 Security Considerations

### File Permissions
```bash
# Set correct permissions
find /path/to/moodle -type f -name "*.php" -exec chmod 644 {} \;
find /path/to/moodle -type d -exec chmod 755 {} \;
```

### Plugin Updates
- Monitor GitHub repositories for security updates
- Test updates in staging environment first
- Keep regular backups before major updates

## 📄 Support and Documentation

### Individual Plugin Documentation
- **format_mawang**: See `course/format/mawang/README.md`
- **theme_mawang**: See `theme/mawang/README.md`
- **local_modcustomfields**: See `local/modcustomfields/README.md`
- **filter_teacherprofile**: See `filter/teacherprofile/README.md`
- **assignsubmission_forms**: See `mod/assign/submission/forms/README.md`

### Getting Help
- **GitHub Issues**: Create issues in respective plugin repositories
- **Moodle Forums**: Tag posts with plugin names
- **Documentation**: Refer to individual README files
- **Community**: Moodle developer forums for technical questions

### Contributing
- Fork repositories on GitHub
- Submit pull requests with improvements
- Report bugs through GitHub issues
- Contribute to documentation

---

*This setup guide covers the complete Mawang plugin suite. For specific plugin details, refer to individual README files included with each plugin.*
