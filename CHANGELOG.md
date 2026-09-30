# Changelog

All notable changes to this project will be documented in this file.


## [1.5.0] - 2026-09-30

### Changed
- Removed the activation-key requirement. Imports no longer depend on a license key or on `yachts.wpharbor.com`.
- The Boats API key and API URL are still configured per site under Settings → Yacht Importer.

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

### Changed
- Changed ENGINE=MyISAM to ENGINE=InnoDB in wp_boats table creation to ensure compatibility with modern hosting environments, including WP Engine which does not support MyISAM.

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