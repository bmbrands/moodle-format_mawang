<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Behat custom steps and configuration for format_mawang.
 *
 * @package   format_mawang
 * @category  test
 * @copyright 2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\ElementNotFoundException;

/**
 * Behat custom steps and configuration for format_mawang.
 *
 * @package   format_mawang
 * @category  test
 * @copyright 2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_format_mawang extends behat_base {

    /**
     * Click on a tile for a specific section number.
     *
     * @Given /^I click on tile for section "([^"]*)"$/
     * @param string $sectionnumber The section number
     */
    public function i_click_on_tile_for_section($sectionnumber) {
        // Find the tile section by section number.
        $xpath = "//li[@id='section-{$sectionnumber}' and contains(@class, 'tilesection')]";
        $element = $this->find('xpath', $xpath);
        $element->click();
    }

    /**
     * Check if specified text appears in a tile.
     *
     * @Given /^I should see "([^"]*)" in the "([^"]*)" tile$/
     * @param string $text The text to look for
     * @param string $tiledescriptor The tile descriptor (e.g., "Topic 1", "section-2")
     */
    public function i_should_see_text_in_the_tile($text, $tiledescriptor) {
        // Handle different tile descriptors.
        if (preg_match('/^section-(\d+)$/', $tiledescriptor, $matches)) {
            $xpath = "//li[@id='section-{$matches[1]}' and contains(@class, 'tilesection')]";
        } else {
            // Assume it's a topic name and find by text content.
            $xpath = "//li[contains(@class, 'tilesection') and contains(., '{$tiledescriptor}')]";
        }

        $element = $this->find('xpath', $xpath);
        $tiletext = $element->getText();
        if (strpos($tiletext, $text) === false) {
            throw new \Exception("'{$text}' text not found in tile '{$tiledescriptor}'");
        }
    }

    /**
     * Check if specified text appears in a specific tile section.
     *
     * @Given /^I should see "([^"]*)" in tile for section "([^"]*)"$/
     * @param string $text The text to look for
     * @param string $sectionnumber The section number
     */
    public function i_should_see_text_in_tile_for_section($text, $sectionnumber) {
        // Find the specific tile section and check for the specified text.
        $xpath = "//li[@id='section-{$sectionnumber}' and contains(@class, 'tilesection')]";
        $element = $this->find('xpath', $xpath);

        // Check if the tile contains the specified text.
        $tiletext = $element->getText();
        if (strpos($tiletext, $text) === false) {
            throw new \Exception("'{$text}' text not found in tile for section {$sectionnumber}");
        }
    }

    /**
     * Check if specified text does NOT appear in a specific tile section.
     *
     * @Given /^I should not see "([^"]*)" in tile for section "([^"]*)"$/
     * @param string $text The text to check for absence
     * @param string $sectionnumber The section number
     */
    public function i_should_not_see_text_in_tile_for_section($text, $sectionnumber) {
        // Find the specific tile section and check that the specified text is not present.
        $xpath = "//li[@id='section-{$sectionnumber}' and contains(@class, 'tilesection')]";

        try {
            $element = $this->find('xpath', $xpath);
            $tiletext = $element->getText();
            if (strpos($tiletext, $text) !== false) {
                throw new \Exception("'{$text}' text unexpectedly found in tile for section {$sectionnumber}");
            }
        } catch (ElementNotFoundException $e) {
            // If tile doesn't exist, that's also fine for a "should not see" test.
            return;
        }
    }

    /**
     * Check if specified text does NOT appear in any tile.
     *
     * @Given /^I should not see "([^"]*)" in any tile$/
     * @param string $text The text to check for absence
     */
    public function i_should_not_see_text_in_any_tile($text) {
        // Find all tile sections and check that none contain the specified text.
        $xpath = "//li[contains(@class, 'tilesection')]";

        try {
            $elements = $this->find_all('xpath', $xpath);
            $foundintiles = [];
            foreach ($elements as $element) {
                $tiletext = $element->getText();
                if (strpos($tiletext, $text) !== false) {
                    $tileid = $element->getAttribute('id');
                    $foundintiles[] = $tileid;
                }
            }
            if (!empty($foundintiles)) {
                $tileslist = implode(', ', $foundintiles);
                throw new \Exception("'{$text}' text unexpectedly found in tiles: {$tileslist}");
            }
        } catch (ElementNotFoundException $e) {
            // Expected - no tiles found, which is fine.
            return;
        }
    }    /**
          * Get the tile element for a specific section number.
          *
          * @param string $sectionnumber The section number
          * @return \Behat\Mink\Element\NodeElement The tile element
          */
    protected function get_tile_for_section($sectionnumber) {
        $xpath = "//li[@id='section-{$sectionnumber}' and contains(@class, 'tilesection')]";
        return $this->find('xpath', $xpath);
    }

    /**
     * Wait for a tile to be clickable.
     *
     * @param string $sectionnumber The section number
     */
    protected function wait_for_tile_clickable($sectionnumber) {
        $xpath = "//li[@id='section-{$sectionnumber}' and contains(@class, 'tilesection')]";
        $this->wait_for_pending_js();
        $this->find('xpath', $xpath);
    }

    /**
     * Check if a tile has the recently viewed indicator.
     *
     * @param string $sectionnumber The section number
     * @return bool True if the tile has the recently viewed indicator
     */
    protected function tile_has_recently_viewed($sectionnumber) {
        $xpath = "//li[@id='section-{$sectionnumber}' and contains(@class, 'tilesection')]" .
                 "//span[contains(@class, 'recentlyviewed')]";

        try {
            $this->find('xpath', $xpath);
            return true;
        } catch (ElementNotFoundException $e) {
            return false;
        }
    }

    /**
     * Check if a section image is visible in a specific tile.
     *
     * @Given /^I should see the section image in tile for section "([^"]*)"$/
     * @param string $sectionnumber The section number
     */
    public function i_should_see_section_image_in_tile($sectionnumber) {
        // Find the section-image-background div within the tile.
        $xpath = "//li[@id='section-{$sectionnumber}' and contains(@class, 'tilesection')]" .
                 "//div[contains(@class, 'section-image-background')]";

        try {
            $element = $this->find('xpath', $xpath);

            // Get the style attribute to check for background-image.
            $style = $element->getAttribute('style');

            if (empty($style)) {
                throw new \Exception("No style attribute found on section image background for section {$sectionnumber}");
            }

            // Check if the background-image contains pluginfile (indicating uploaded image).
            $pattern = '/background-image:\s*url\(["\']?[^"\']*pluginfile\.php[^"\']*["\']?\)/';
            if (!preg_match($pattern, $style)) {
                $message = "Section image background does not contain uploaded image (pluginfile.php) " .
                          "for section {$sectionnumber}. Style: {$style}";
                throw new \Exception($message);
            }

        } catch (ElementNotFoundException $e) {
            throw new \Exception("Section image background div not found in tile for section {$sectionnumber}");
        }
    }

    /**
     * Check if a section image is NOT visible in a specific tile.
     *
     * @Given /^I should not see the section image in tile for section "([^"]*)"$/
     * @param string $sectionnumber The section number
     */
    public function i_should_not_see_section_image_in_tile($sectionnumber) {
        // Find the section-image-background div within the tile.
        $xpath = "//li[@id='section-{$sectionnumber}' and contains(@class, 'tilesection')]" .
                 "//div[contains(@class, 'section-image-background')]";

        try {
            $element = $this->find('xpath', $xpath);

            // Get the style attribute to check for background-image.
            $style = $element->getAttribute('style');

            // If no style attribute, that's fine for "should not see".
            if (empty($style)) {
                return;
            }

            // Check if the background-image contains the default image (not pluginfile).
            $pattern = '/background-image:\s*url\(["\']?[^"\']*pluginfile\.php[^"\']*["\']?\)/';
            if (preg_match($pattern, $style)) {
                $message = "Section image background unexpectedly contains uploaded image (pluginfile.php) " .
                          "for section {$sectionnumber}. Style: {$style}";
                throw new \Exception($message);
            }

            // If it contains the default image path, that's expected for "should not see uploaded image".
            $defaultpattern = '/background-image:\s*url\(["\']?[^"\']*defaultsectionimage[^"\']*["\']?\)/';
            if (preg_match($defaultpattern, $style)) {
                // This is fine - default image is showing, no uploaded image.
                return;
            }

        } catch (ElementNotFoundException $e) {
            // If the div doesn't exist, that's also fine for "should not see".
            return;
        }
    }

    /**
     * Helper method to get the background image style for a section.
     *
     * @param string $sectionnumber The section number
     * @return string The background-image style value
     */
    protected function get_section_background_image_style($sectionnumber) {
        $xpath = "//li[@id='section-{$sectionnumber}' and contains(@class, 'tilesection')]" .
                 "//div[contains(@class, 'section-image-background')]";

        try {
            $element = $this->find('xpath', $xpath);
            return $element->getAttribute('style') ?: '';
        } catch (ElementNotFoundException $e) {
            return '';
        }
    }

    /**
     * Check if a background image style contains an uploaded image.
     *
     * @param string $style The CSS style attribute value
     * @return bool True if it contains pluginfile.php (uploaded image)
     */
    protected function style_contains_uploaded_image($style) {
        return preg_match('/background-image:\s*url\(["\']?[^"\']*pluginfile\.php[^"\']*["\']?\)/', $style);
    }

    /**
     * Check if a background image style contains the default image.
     *
     * @param string $style The CSS style attribute value
     * @return bool True if it contains defaultsectionimage
     */
    protected function style_contains_default_image($style) {
        return preg_match('/background-image:\s*url\(["\']?[^"\']*defaultsectionimage[^"\']*["\']?\)/', $style);
    }

    /**
     * Check if a specific duration total is displayed in a tile.
     *
     * @Given /^I should see duration total "([^"]*)" in tile for section "([^"]*)"$/
     * @param string $duration The expected duration total
     * @param string $sectionnumber The section number
     */
    public function i_should_see_duration_total_in_tile($duration, $sectionnumber) {
        // Find the tile section and look for duration display in the specific div.
        $xpath = "//li[@id='section-{$sectionnumber}' and contains(@class, 'tilesection')]" .
                 "//div[contains(@class, 'totalduration')]";

        try {
            $element = $this->find('xpath', $xpath);
            $durationtext = $element->getText();

            // Check if the duration matches the expected format (e.g., "15m").
            $expectedtext = $duration . 'm';

            if (trim($durationtext) !== $expectedtext) {
                $message = "Expected duration '{$expectedtext}' but found '{$durationtext}' " .
                          "in tile for section {$sectionnumber}";
                throw new \Exception($message);
            }

        } catch (ElementNotFoundException $e) {
            $message = "Duration div with class 'totalduration ma-small' not found " .
                      "in tile for section {$sectionnumber}";
            throw new \Exception($message);
        }
    }

    /**
     * Check that no duration is displayed in a tile.
     *
     * @Given /^I should not see any duration in tile for section "([^"]*)"$/
     * @param string $sectionnumber The section number
     */
    public function i_should_not_see_any_duration_in_tile($sectionnumber) {
        // Find the tile section and check for absence of duration div.
        $xpath = "//li[@id='section-{$sectionnumber}' and contains(@class, 'tilesection')]" .
                 "//div[contains(@class, 'totalduration') and contains(@class, 'ma-small')]";

        try {
            $element = $this->find('xpath', $xpath);
            // If we find the element, check if it's empty or contains "0m".
            $durationtext = trim($element->getText());

            if (!empty($durationtext) && $durationtext !== '0m') {
                throw new \Exception("Unexpected duration text '{$durationtext}' found in tile for section {$sectionnumber}");
            }

        } catch (ElementNotFoundException $e) {
            // If the duration div doesn't exist, that's fine for "should not see".
            return;
        }
    }

    /**
     * Debug helper to print the tile content for troubleshooting.
     *
     * @Given /^I debug tile content for section "([^"]*)"$/
     * @param string $sectionnumber The section number
     */
    public function i_debug_tile_content_for_section($sectionnumber) {
        $xpath = "//li[@id='section-{$sectionnumber}' and contains(@class, 'tilesection')]";

        try {
            $tile = $this->find('xpath', $xpath);
            $tilehtml = $tile->getHtml();
            $tiletext = $tile->getText();

            echo "\n=== DEBUG: TILE CONTENT FOR SECTION {$sectionnumber} ===\n";
            echo "Text: {$tiletext}\n";
            echo "HTML: " . substr($tilehtml, 0, 500) . "...\n";

            // Check specifically for duration div.
            $durationxpath = "//li[@id='section-{$sectionnumber}' and contains(@class, 'tilesection')]" .
                            "//div[contains(@class, 'totalduration') and contains(@class, 'ma-small')]";
            try {
                $durationdiv = $this->find('xpath', $durationxpath);
                $durationtext = $durationdiv->getText();
                echo "Duration div found: '{$durationtext}'\n";
            } catch (ElementNotFoundException $e) {
                echo "Duration div NOT found\n";
            }
            echo "=== END DEBUG ===\n";

        } catch (ElementNotFoundException $e) {
            echo "\n=== DEBUG: TILE FOR SECTION {$sectionnumber} NOT FOUND ===\n";
        }
    }

    /**
     * Ensure mawang format custom fields are set up.
     *
     * @Given /^I ensure mawang custom fields are set up$/
     */
    public function i_ensure_mawang_custom_fields_are_set_up() {
        try {
            // Try to set up the custom fields using the setup class.
            \format_mawang\setup::install();
            echo "\n=== Mawang custom fields setup completed ===\n";
        } catch (\Exception $e) {
            // Log the error but don't fail the test - the custom fields might not be available.
            echo "\n=== Warning: Could not set up custom fields: " . $e->getMessage() . " ===\n";
            echo "=== This might be expected if local_modcustomfields plugin is not installed ===\n";
        }
    }
}
