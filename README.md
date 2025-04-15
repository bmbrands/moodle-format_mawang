# Documentation for the Couthino Course Format

## 1. Functionality of the Course Format

The Couthino course format combines the functionality of the Flexible Sections course format with that of the Tiles course format. The technical basis for the Couthino course format is the latest version (May 13, 2024) of the Flexible Sections course format. On top of this, modifications have been made to add tiles to this format. Clicking on a tile opens the content of the corresponding section while closing other sections.

## 2. Additional Features

In addition, the course format includes several custom features such as:
- **Previous/Next navigation under activities:** Navigation buttons to easily go to the previous or next activity.
- **Full breadcrumb path in activities of the Couthino course format:** The full breadcrumb path is displayed to facilitate navigation.
- **Close the course index on the course page:** The course index is closed by default when the course page is opened.
- **Open the course index on the activity page:** The course index is automatically opened when an activity is viewed.
- **Open blocks automatically on an H5P page:** Blocks on the right side are automatically opened on H5P pages.
- **Activity progress visible on tiles:** A progress circle shows how many activities in the section have been completed. This is updated immediately when an activity is completed.

## 3. Design

A responsive grid layout is used, which is only active when the course editing is turned off. When the course is being edited, all sections are shown open. This allows activities to be moved. Moving sections is only possible via the section menu due to restrictions of the Flexible Sections system.

## 4. Section Images

Section images can be added via the "edit section" option in the section dropdown or by clicking the picture icon on a section image. This makes it easy to create visually appealing and informative tiles that enhance the user experience.

## 5. Global Settings

The Couthino course format has several global settings that administrators can configure:

- **Maximum subsection depth**
  - Setting: `format_mawang | maxsectiondepth`
  - Default: 2
  - Description: Maximum number of subsection levels.

- **Show default title of top section**
  - Setting: `format_mawang | showsection0titledefault`
  - Default: No
  - Description: This defines the default setting used for new and existing courses. It can be changed for individual courses in their settings.

- **Display course index**
  - Setting: `format_mawang | courseindexdisplay`
  - Default: Sections and activities
  - Description: Determines how the course index is displayed on the left side of the course page.

- **Show backlink in activities**
  - Setting: `format_mawang | cmbacklink`
  - Default: No
  - Description: Show 'Back to ...' link that allows returning to the course section within the section activities.

- **Automatically close the course index on the course page, open the course index on the activity page**
  - Setting: `format_mawang | courseindexautoclose`
  - Default: Yes
  - Description: When enabled, the course index is automatically closed on the course page and opened on the activity page.

- **Open blocks automatically on the H5P activity page**
  - Setting: `format_mawang | h5pblockopen`
  - Default: No
  - Description: When enabled, blocks are automatically opened on the H5P activity page.

- **Default section image**
  - Setting: `format_mawang | defaultsectionimage`
  - Description: A default image can be set for sections.

## 6. Maintenance and Updates

The code is based on Flexible Sections. When the plugin is updated, the Couthino format can also be updated. The commit history of the repository is required for this. Most Moodle developers can perform such an update by reapplying the customizations.

- **Version Compatibility:** The current version is compatible with Moodle versions 4.1 to 4.4 and possibly newer versions.

## 7. Code Quality

The code for the Couthino course format adheres as much as possible to the Moodle coding guidelines. The format has been tested for accessibility and utilizes the latest Moodle technologies such as reactive JavaScript for building the course user interface.