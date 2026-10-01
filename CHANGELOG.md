# Changelog

All notable changes to this project will be documented in this file.


## [1.6.1] - 2026-10-01

### Changed
- Plugin URI, author, and the settings documentation link now point at Freshy and this GitHub repository.
- Removed the settings-page contact form and contact details. This plugin does not offer support.


## [1.6.0] - 2026-10-01

### Changed
- Removed the activation-key requirement. Imports no longer depend on a license key.
- Removed the license-server update check.
- The Boats API key and API URL are still configured per site under Settings → Yacht Importer.


## [1.5.5] – 2026-04-16

### Fixed
- Fixed a critical issue where boat listings were not importing due to a recent security change on the Boats Group API servers. The API began rejecting requests that did not include proper identification headers, causing the plugin to receive an access-denied response instead of listing data. The plugin now sends appropriate request headers to comply with the updated API requirements.
- Updated the API status filter parameter from `status` to `salesstatus` to align with a recent change in the Boats Group Inventory API, which deprecated the older parameter name.

### Improved
- Enhanced API request reliability by adding proper `User-Agent` and `Accept` headers to all outbound API calls.



## [1.5.4] – 2025-12-29

### Added
- Introduced shortcode attributes `type`, `fuel`, and `condition` for Yacht Listings.
- Enabled automatic application of preset filters on page load when filters are provided via shortcode.
- Preserved backward compatibility with existing GET-based search and pagination behavior.


## [1.5.3] - 2025-12-09

### Fixed
- Resolved issues caused by changing category IDs during recurring yacht listing imports from the API. Using slugs ensures consistent matching and prevents filter breaks after each import cycle.

### Changed
- Updated filter logic to store category slugs instead of category IDs in the database.

### Enhanced
- Improved stability and reliability of category-based filtering throughout the plugin.


## [1.5.2] - 2025-11-14

### Added
- Implemented automatic cleanup system for generated yacht PDF files.
- Added a scheduled WP-Cron event (`yacht_pdf_cleanup_event`) that runs once daily.
- Cleanup function removes any temporary PDF older than 24 hours from the `/wp-content/uploads/yacht_pdfs/` directory.

### Changed
- Updated PDF storage directory from `/uploads/pdfs/` to `/uploads/yacht_pdfs/` for better organization and to avoid conflicts with other plugins.

### Technical Notes
- The cleanup function uses `filemtime()` to determine if a PDF is older than 24 hours.
- PDFs are intended only for immediate page load usage. This prevents storage growth and keeps disk usage manageable.
- A scheduler is created with `wp_schedule_event()` and only runs if not already scheduled.
- Cleanup is executed silently in the background with no impact on user experience.

### Benefits
- Prevents unnecessary accumulation of temporary PDF files.
- Reduces server storage usage which can grow to gigabytes over time.
- Provides a clean, organized, and isolated directory for all yacht-related PDF exports.


## [1.4.0] - 2025-07-08

### Changed
-  Codebase updated for better compatibility with PHP 8.1 and future versions.
-  Improved error handling and code robustness in class.boatapis.php.
-  Improved safety checks when extracting engine properties
-  Overall code cleanup for better stability and compatibility with current WordPress standards.

### Fixed
- Fixed fatal error caused by improper access of stdClass as array in engine data parsing.
- Fixed deprecated PHP 8.1+ warning by ensuring explode() receives a valid string instead of null.
- Resolved "Undefined property" warnings by safely accessing optional object properties using null coalescing (??) for improved stability.

## [1.3.0] - 2025-05-29
### Added
- Added option to insert the Yacht Detail template content using a shortcode.

## [1.2.0] - 2025-04-03
### Added
- Introduced a new dropdown option in the plugin options page within the WordPress admin dashboard to select the individual listing detail page.
- Added a `CHANGELOG.md` file to track improvements and new feature additions.

### Changed
- Improved SQL query code to enhance security.
- Changed ENGINE=MyISAM to ENGINE=InnoDB in wp_boats table creation to ensure compatibility with modern hosting environments, including WP Engine which does not support MyISAM.

### Fixed
- Resolved compatibility issues with PHP 8.2.
- Fixed a conflict with SQL queries related to the Yacht import data.
- Fixed an issue where the slider on the single detail listing page was not rendering correctly.

## [1.2.0] - 2025-03-28
### Added
- Added a "Download PDF" button to allow users to download Yacht listing information in PDF format.

## [1.1.0] - 2025-03-15
### Added
- Introduced custom shortcodes for displaying plugin-related content.
- Added support for multisite installations.

### Changed
- Enhanced database queries to improve performance on large sites.

### Fixed
- Fixed a bug causing incorrect data output in certain scenarios.

## [1.0.0] - 2025-02-01
### Initial Release
- First stable version of the plugin with core functionality.