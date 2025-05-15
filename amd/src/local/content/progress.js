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
 * Reactive component to show the course progress.
 *
 * @module     format_mawang/local/content/sectionprogress
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {BaseComponent} from 'core/reactive';
import {getCurrentCourseEditor} from 'core_courseformat/courseeditor';
import Templates from 'core/templates';

export default class Component extends BaseComponent {

    /**
     * Constructor hook.
     */
    create() {
        // Optional component name for debugging.
        this.name = 'courseprogress';
        // Default query selectors.
        this.selectors = {
            PROGRESSCONTAINER: `[data-region="courseprogresscontainer"]`,
            DESTINATION: `[data-region="mawang-extra"]`,
        };

        // Default classes to toggle on refresh.
        this.classes = {
            PROGRESSHIDDEN: 'd-none',
        };
        // Arrays to keep cms and sections elements.
        this.wrapper = null;
        this.progress = null;
    }

    /**
     * Static method to create a component instance form the mustache template.
     *
     * @param {element|string} target the DOM main element or its ID
     * @param {object} selectors optional css selector overrides
     * @return {Component}
     */
    static init(target, selectors) {
        this.addEvents();
        return new Component({
            element: document.getElementById(target),
            reactive: getCurrentCourseEditor(),
            selectors,
        });
    }

    /**
     * Initial state ready method.
     *
     * @param {Object} state the state data
     */
    stateReady(state) {

        // Get cms and sections elements.
        this.wrapper = this.getElement();

        this._refreshCompletion({state: state});
    }


    /**
     * Update course completion based on the completion of the cms in the sections
     *
     * @param {Object} param details the update details.
     * @param {Object} param.state the state data.
     */
    async _refreshCompletion({state}) {
        if (this.reactive.isEditing) {
            return;
        }

        const container = document.querySelector(this.selectors.PROGRESSCONTAINER);

        if (!container) {
            return;
        }
        const items = this.reactive.getExporter().allItemsArray(this.reactive.state);
        const exporter = this.reactive.getExporter();
        if (items.length === 0) {
            return;
        }
        const rootsections = [];
        const allsections = [];
        items.forEach((item) => {
            if (item.type == 'section') {
                const section = state.section.get(item.id);
                allsections.push(section);
            }
        });

        allsections.forEach((section) => {
            rootsections[section.id] = {
                total: 0,
                completed: 0,
                subsections: [],
            };
        });

        items.every((item) => {
            if (item.type == 'cm') {
                const cm = state.cm.get(item.id);
                // Find the root section of the cm.
                const rootid = cm.sectionid;
                if (cm.visible === false || cm.uservisible === false) {
                    return true;
                }
                const data = exporter.cmCompletion(state, cm);
                if (data.hasstate !== true) {
                    return true;
                }

                rootsections[rootid].total++;
                if (data.iscomplete) {
                    rootsections[rootid].completed++;
                }
            }
            return true;
        });

        if (rootsections[container.dataset.id]) {
            const completed = rootsections[container.dataset.id].completed;
            const total = rootsections[container.dataset.id].total;
            container.dataset.subsections = rootsections[container.dataset.id].subsections.join(',');
            if (total === 0) {
                return;
            }

            const progress = Math.round((completed / total) * 100);

            if (progress !== container.dataset.progress) {

                const {html, js} = await Templates.renderForPromise('format_mawang/local/content/progress',
                    {
                        'progress': progress,
                        'sectionid': container.dataset.id,
                    }
                );

                Templates.replaceNode(container, html, js);
            }

        }
    }

    /**
     * Component watchers.
     *
     * @returns {Array} of watchers
     */
    getWatchers() {
        return [
            {watch: `cm:updated`, handler: this._refreshCompletion},
        ];
    }

    /**
     * Add events to the component.
     */
    static addEvents() {
        document.addEventListener('click', (event) => {
            if (event.target.closest('[data-action="progressmoreinfo"]')) {
                event.preventDefault();
                const todo = document.querySelector('[data-region="progressmoretodo"]');
                todo.classList.toggle('show');
            }
        });
    }
}