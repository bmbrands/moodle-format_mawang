# Documentation for the Mawang Course Format

## 1. Functionality of the Mawang Format

The Mawang course format is designed by Madison Wang en implemented by Bas Brands. It features a tile based layout and is intended to
provide a clean and professional course experience. It features progress indication, last visisted section, a course warning messages
Custom navigation experience for some activities, and inpage block area.
This course format should be installed with theme_mawang

## 2. Additional Features

In addition, the course format includes several custom features such as:
- **Previous/Next navigation under activities:** Navigation buttons to easily go to the previous or next activity.
- **Full breadcrumb path in activities of the Mawang course format:** The full breadcrumb path is displayed to facilitate navigation.
- **Activity progress visible on section pages:** A progress bar shows how many activities in the section have been completed. This is updated immediately when an activity is completed.

## 3. Design

A responsive grid layout is used, which is only active when the course editing is turned off. When the course is being edited, all sections are shown open. This allows activities to be moved. Moving sections is only possible via the section menu due to restrictions of the Flexible Sections system.

## 4. Section Images

Section images can be added via the "edit section" option in the section dropdown or by clicking the picture icon on a section image. This makes it easy to create visually appealing and informative tiles that enhance the user experience.

## 5. Global Settings

The Mawang course format has several global settings that administrators can configure:

- **Show backlink in activities**
  - Setting: `format_mawang | cmbacklink`
  - Default: No
  - Description: Show 'Back to ...' link that allows returning to the course section within the section activities.

- **Version Compatibility:** The current version is compatible with Moodle versions 4.4 to 4.5 and possibly newer versions.

## 7. Code Quality

The code for the course format adheres as much as possible to the Moodle coding guidelines. The format has been tested for accessibility and utilizes the latest Moodle technologies such as reactive JavaScript for building the course user interface.
