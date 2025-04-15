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
 * Tile controls
 *
 * @module     format_mawang/local/tiles
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Set the container grid styles for the sectioncontent. The Tiles are rendered in a grid layout of 3 columns,
 * 2 columns or 1 column depending on the screen width.
 * The sectioncontent should always be positioned in the grid row under the tilesection and take up the full width.
 * @param {HTMLElement} tilesection
 * @param {HTMLElement} sectioncontent
 */
export const setGridStyles = (tilesection, sectioncontent) => {
    const alltilesections = document.querySelectorAll('[data-region="tilesection"]');
    // Where is the tilesection in the grid?
    const grid = tilesection.closest('.tilesections');
    const gridwidth = grid.offsetWidth;
    const tileWidth = tilesection.offsetWidth;

    // How many tiles are in a row?
    const tilesectiontotal = Math.floor(gridwidth / tileWidth);

    // In which row is the tilesection?
    let tilesectionrow = 0;
    alltilesections.forEach((tile, index) => {
        if (tile === tilesection) {
            tilesectionrow = Math.floor(index / tilesectiontotal);
        }
    });
    // Set the grid styles for the sectioncontent
    sectioncontent.style.gridRow = tilesectionrow + 2;
    // Let the sectioncontent take up all columns
    sectioncontent.style.gridColumn = '1 / -1';
};

/**
 * Open and close the sectioncontent
 * @param {HTMLElement} tiletoggle
 * @param {boolean} toggle
 */
export const showSection = (tiletoggle, toggle = true) => {
    const button = tiletoggle;
    const sectionid = button.dataset.number;
    const tilesection = document.querySelector('[data-region="tilesection"][data-number="' + sectionid + '"]');
    const tile = tilesection.querySelector('.tiletoggle');
    const sectioncontent = document.querySelector('[data-region="sectioncontent"][data-sectionid="' + sectionid + '"]');
    const closebutton = sectioncontent.querySelector('.btn-close[data-action="tiletoggle"]');
    // Remove show from all other sectioncontents
    const sectioncontents = document.querySelectorAll('[data-region="sectioncontent"]');
    sectioncontents.forEach((section) => {
        if (section !== sectioncontent) {
            section.classList.remove('show');
        }
    });
    const alltilesections = document.querySelectorAll('[data-region="tilesection"]');
    alltilesections.forEach((tile) => {
        if (tile !== tilesection) {
            tile.classList.remove('show');
        }
    });

    if (toggle) {
        sectioncontent.classList.toggle('show');
        tilesection.classList.toggle('show');
    } else {
        sectioncontent.classList.add('show');
        tilesection.classList.add('show');
    }

    setGridStyles(tilesection, sectioncontent);
    if (tiletoggle.dataset.keypressed === 'true') {
        setTimeout(() => {
            if (sectioncontent.classList.contains('show') && closebutton) {
                closebutton.focus();
            } else {
                tile.focus();
            }
        }, 300);
    }
    tiletoggle.dataset.keypressed = 'false';
};

const navigateToSection = (sectionid) => {
    let button = document.querySelector(`[data-action="tiletoggle"][data-number="${sectionid}"]`);
    if (button) {
        showSection(button, false);
    } else {
        const subsection = document.querySelector(`.course-section[data-number="${sectionid}"]`);
        const parentId = subsection?.closest('[data-region="sectioncontent"]')?.dataset.sectionid;
        if (parentId) {
            button = document.querySelector(`[data-action="tiletoggle"][data-number="${parentId}"]`);
            if (button) {
                showSection(button, false);
            }
        }
    }
};

/**
 * Initialize the tile toggles
 */
export const init = () => {
    document.addEventListener('click', (event) => {
        if (event.target.closest('.tilesections [data-action="tiletoggle"]')) {
            const tiletoggle = event.target.closest('.tilesections [data-action="tiletoggle"]');
            showSection(tiletoggle);

            // Update the page URL, set the hash to the sectionid
            const sectionid = tiletoggle.dataset.number;
            window.location.hash = `section-${sectionid}`;
        }
        if (event.target.closest('.courseindex-link[data-for="section_title"]')) {
            const sectionid = event.target.closest('.courseindex > .courseindex-section')?.dataset.number;
            if (sectionid) {
                navigateToSection(sectionid);
            }
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && event.target.closest('.tilesections .tiletoggle[data-action="tiletoggle"]')) {
            const tiletoggle = event.target.closest('.tilesections .tiletoggle[data-action="tiletoggle"]');
            tiletoggle.dataset.keypressed = 'true';
            showSection(tiletoggle);

            // Update the page URL, set the hash to the sectionid
            const sectionid = tiletoggle.dataset.number;
            window.location.hash = `section-${sectionid}`;
        }
    });
    // On resize, reset the grid styles for the sectioncontent and tilesection that are shown
    window.addEventListener('resize', () => {
        const sectioncontent = document.querySelector('.tilesections .show[data-region="sectioncontent"]');
        const tilesection = document.querySelector('.tilesections .show[data-region="tilesection"]');
        if (sectioncontent && tilesection) {
            setGridStyles(tilesection, sectioncontent);
        }
    });
    // On load check if the hash is set and click the corresponding tiletoggle button
    const hash = window.location.hash;
    if (hash) {
        const sectionid = hash.replace('#section-', '');
        navigateToSection(sectionid);
    }
};