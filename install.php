#!/usr/bin/env php
<?php
/**
 * Mawang Plugin Suite Installer
 *
 * This script automatically downloads and installs all Mawang plugins
 * for Moodle. It performs safety checks and handles dependencies.
 *
 * Usage: php install.php [--force] [--dry-run]
 *
 * @package    format_mawang
 * @copyright  2024 Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Prevent web access
if (isset($_SERVER['REQUEST_METHOD'])) {
    die('This script can only be run from the command line.');
}

// Configuration
const GITHUB_USER = 'bmbrands';
const REQUIRED_PHP_VERSION = '8.1';
const REQUIRED_MOODLE_VERSION = '4.4';

// Plugin definitions: plugin_key => [repo_name, target_path]
$plugins = [
    'format_mawang' => ['moodle-format_mawang', 'course/format/mawang'],
    'filter_teacherprofile' => ['moodle-filter_teacherprofile', 'filter/teacherprofile'],
    'local_modcustomfields' => ['moodle-local_modcustomfields', 'local/modcustomfields'],
    'theme_mawang' => ['moodle-theme_mawang', 'theme/mawang'],
    'assignsubmission_forms' => ['moodle-assignsubmission_forms', 'mod/assign/submission/forms']
];

// Installation order (dependencies first)
$install_order = [
    'local_modcustomfields',
    'filter_teacherprofile',
    'assignsubmission_forms',
    'theme_mawang',
    'format_mawang'
];

// Global options
$options = [
    'force' => false,
    'dry_run' => false,
    'verbose' => false
];

// Color output functions
function print_color($text, $color = 'default') {
    $colors = [
        'red' => "\033[31m",
        'green' => "\033[32m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'cyan' => "\033[36m",
        'white' => "\033[37m",
        'bold' => "\033[1m",
        'reset' => "\033[0m"
    ];

    if (isset($colors[$color])) {
        echo $colors[$color] . $text . $colors['reset'];
    } else {
        echo $text;
    }
}

function print_success($message) {
    print_color("✓ ", 'green');
    echo $message . "\n";
}

function print_error($message) {
    print_color("✗ ", 'red');
    echo $message . "\n";
}

function print_warning($message) {
    print_color("⚠ ", 'yellow');
    echo $message . "\n";
}

function print_info($message) {
    print_color("ℹ ", 'blue');
    echo $message . "\n";
}

function print_header($message) {
    echo "\n";
    print_color(str_repeat("=", 60), 'cyan');
    echo "\n";
    print_color($message, 'bold');
    echo "\n";
    print_color(str_repeat("=", 60), 'cyan');
    echo "\n\n";
}

// Parse command line arguments
function parse_arguments($argv) {
    global $options;

    foreach ($argv as $arg) {
        switch ($arg) {
            case '--force':
                $options['force'] = true;
                break;
            case '--dry-run':
                $options['dry_run'] = true;
                break;
            case '--verbose':
                $options['verbose'] = true;
                break;
            case '--help':
            case '-h':
                show_help();
                exit(0);
        }
    }
}

function show_help() {
    echo "Mawang Plugin Suite Installer\n\n";
    echo "Usage: php install.php [options]\n\n";
    echo "Options:\n";
    echo "  --force     Skip confirmation prompts\n";
    echo "  --dry-run   Show what would be done without making changes\n";
    echo "  --verbose   Show detailed output\n";
    echo "  --help, -h  Show this help message\n\n";
    echo "This script will download and install all Mawang plugins:\n";
    echo "  - Course format: Mawang\n";
    echo "  - Filter: Teacher Profile\n";
    echo "  - Local plugin: Module Custom Fields\n";
    echo "  - Theme: Mawang\n";
    echo "  - Assignment submission: Forms\n\n";
}

// Check if we're in a Moodle directory
function check_moodle_directory() {
    if (!file_exists('config.php') || !file_exists('version.php')) {
        print_error('This does not appear to be a Moodle root directory.');
        print_info('Please run this script from your Moodle root directory.');
        return false;
    }
    return true;
}

// Check PHP version
function check_php_version() {
    $current_version = PHP_VERSION;
    if (version_compare($current_version, REQUIRED_PHP_VERSION, '<')) {
        print_error("PHP {REQUIRED_PHP_VERSION}+ required. Current version: $current_version");
        return false;
    }
    return true;
}

// Check required PHP extensions
function check_php_extensions() {
    $required_extensions = ['curl', 'zip', 'json'];
    $missing = [];

    foreach ($required_extensions as $ext) {
        if (!extension_loaded($ext)) {
            $missing[] = $ext;
        }
    }

    if (!empty($missing)) {
        print_error('Missing required PHP extensions: ' . implode(', ', $missing));
        return false;
    }
    return true;
}

// Check Moodle version
function check_moodle_version() {
    if (!file_exists('version.php')) {
        print_warning('Cannot determine Moodle version');
        return true;
    }

    $version_content = file_get_contents('version.php');
    if (preg_match('/\$version\s*=\s*([0-9.]+)/', $version_content, $matches)) {
        $version = $matches[1];
        $version_parts = explode('.', $version);
        $major = (int)$version_parts[0];
        $minor = isset($version_parts[1]) ? (int)$version_parts[1] : 0;

        if ($major < 4 || ($major == 4 && $minor < 4)) {
            print_warning("Moodle version $version detected. Mawang plugins require Moodle 4.4+");
            if (!$GLOBALS['options']['force']) {
                $continue = readline('Continue anyway? (y/N): ');
                if (strtolower(trim($continue)) !== 'y') {
                    print_info('Installation cancelled.');
                    return false;
                }
            }
        }
    }
    return true;
}

// Download file from URL
function download_file($url, $destination) {
    global $options;

    if ($options['dry_run']) {
        print_info("Would download: $url -> $destination");
        return true;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mawang-Installer/1.0');
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);

    $data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200 || $data === false) {
        return false;
    }

    return file_put_contents($destination, $data) !== false;
}

// Extract ZIP file
function extract_zip($zip_file, $extract_to) {
    global $options;

    if ($options['dry_run']) {
        print_info("Would extract: $zip_file -> $extract_to");
        return true;
    }

    $zip = new ZipArchive();
    if ($zip->open($zip_file) === TRUE) {
        $zip->extractTo($extract_to);
        $zip->close();
        return true;
    }
    return false;
}

// Download and install a plugin
function install_plugin($plugin_key, $repo_name, $target_path) {
    global $options;

    print_info("Installing $plugin_key...");

    // Check if plugin already exists
    if (is_dir($target_path)) {
        print_warning("Plugin already exists at: $target_path");
        if (!$options['force']) {
            $overwrite = readline('Overwrite existing installation? (y/N): ');
            if (strtolower(trim($overwrite)) !== 'y') {
                print_info("Skipped $plugin_key");
                return true;
            }
        }

        if (!$options['dry_run']) {
            remove_directory($target_path);
        }
    }

    // Create temp directory
    $temp_dir = sys_get_temp_dir() . '/mawang_install_' . uniqid();
    if (!$options['dry_run']) {
        mkdir($temp_dir, 0755, true);
    }

    try {
        // Download plugin
        $zip_url = "https://github.com/" . GITHUB_USER . "/$repo_name/archive/refs/heads/main.zip";
        $zip_file = "$temp_dir/$plugin_key.zip";

        print_info("Downloading from: $zip_url");

        if (!download_file($zip_url, $zip_file)) {
            throw new Exception("Failed to download $plugin_key");
        }

        print_success("Downloaded $plugin_key");

        // Extract plugin
        if (!extract_zip($zip_file, $temp_dir)) {
            throw new Exception("Failed to extract $plugin_key");
        }

        // Find extracted directory (should be repo_name-main)
        $extracted_dir = "$temp_dir/$repo_name-main";
        if (!is_dir($extracted_dir)) {
            throw new Exception("Extracted directory not found: $extracted_dir");
        }

        // Create target directory
        $target_dir = dirname($target_path);
        if (!$options['dry_run']) {
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            // Move extracted content to target
            if (!rename($extracted_dir, $target_path)) {
                throw new Exception("Failed to move plugin to $target_path");
            }
        }

        print_success("Installed $plugin_key to $target_path");
        return true;

    } catch (Exception $e) {
        print_error($e->getMessage());
        return false;
    } finally {
        // Cleanup temp directory
        if (!$options['dry_run'] && is_dir($temp_dir)) {
            remove_directory($temp_dir);
        }
    }
}

// Remove directory recursively
function remove_directory($dir) {
    if (!is_dir($dir)) {
        return;
    }

    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = "$dir/$file";
        if (is_dir($path)) {
            remove_directory($path);
        } else {
            unlink($path);
        }
    }
    rmdir($dir);
}

// Verify installation
function verify_installation($plugin_key, $target_path) {
    if (!is_dir($target_path)) {
        print_error("Installation verification failed: $target_path not found");
        return false;
    }

    // Check for version.php
    $version_file = "$target_path/version.php";
    if (!file_exists($version_file)) {
        print_error("Installation verification failed: $version_file not found");
        return false;
    }

    print_success("Installation verified: $plugin_key");
    return true;
}

// Main installation function
function main($argv) {
    parse_arguments($argv);

    print_header("Mawang Plugin Suite Installer");

    if ($GLOBALS['options']['dry_run']) {
        print_warning("DRY RUN MODE - No changes will be made");
        echo "\n";
    }

    // Pre-installation checks
    print_info("Performing pre-installation checks...");

    if (!check_moodle_directory()) {
        exit(1);
    }

    if (!check_php_version()) {
        exit(1);
    }

    if (!check_php_extensions()) {
        exit(1);
    }

    if (!check_moodle_version()) {
        exit(1);
    }

    print_success("All checks passed");

    // Show installation plan
    echo "\nInstallation plan:\n";
    foreach ($GLOBALS['install_order'] as $plugin_key) {
        $target_path = $GLOBALS['plugins'][$plugin_key][1];
        echo "  • $plugin_key -> $target_path\n";
    }

    if (!$GLOBALS['options']['force'] && !$GLOBALS['options']['dry_run']) {
        $proceed = readline("\nProceed with installation? (Y/n): ");
        if (strtolower(trim($proceed)) === 'n') {
            print_info('Installation cancelled.');
            exit(0);
        }
    }

    // Install plugins
    echo "\n";
    print_header("Installing Plugins");

    $success_count = 0;
    $total_count = count($GLOBALS['install_order']);

    foreach ($GLOBALS['install_order'] as $plugin_key) {
        list($repo_name, $target_path) = $GLOBALS['plugins'][$plugin_key];

        if (install_plugin($plugin_key, $repo_name, $target_path)) {
            if (!$GLOBALS['options']['dry_run']) {
                verify_installation($plugin_key, $target_path);
            }
            $success_count++;
        }
        echo "\n";
    }

    // Summary
    print_header("Installation Summary");

    if ($success_count === $total_count) {
        print_success("All plugins installed successfully ($success_count/$total_count)");

        if (!$GLOBALS['options']['dry_run']) {
            echo "\nNext steps:\n";
            echo "1. Visit your Moodle admin notifications page to complete plugin installation\n";
            echo "2. Configure the Mawang plugins in Site Administration\n";
            echo "3. Create or edit courses to use the Mawang format\n";
        }
    } else {
        print_warning("Installation completed with issues ($success_count/$total_count plugins installed)");
        exit(1);
    }
}

// Run the installer
if (isset($argc) && $argc > 0) {
    main($argv);
} else {
    print_error('This script must be run from the command line.');
    exit(1);
}
