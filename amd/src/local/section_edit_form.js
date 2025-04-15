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
 * TODO describe module section_edit_from
 *
 * @module     format_mawang/local/section_edit_form
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


import ModalForm from 'core_form/modalform';
import {get_string as getString} from 'core/str';

export const init = () => {

    const submitEventHandler = (event) => {
        // Fire a custom event to notify the grading app that a case has been added.
        const courseContent = document.querySelector('.course-content');
        const customEvent = new CustomEvent('sectionUpdated', {});
        courseContent.dispatchEvent(customEvent);
        if (event.detail && event.detail.returnurl) {
            window.location.href = event.detail.returnurl;
            window.location.reload();
        }
    };
    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-action="section-edit-form"]')) {
            return;
        }
        const button = event.target.closest('[data-action="section-edit-form"]');
        event.preventDefault();

        const modalForm = new ModalForm({
            modalConfig: {
                title: getString('editsection'),
            },
            formClass: '\\format_mawang\\form\\section_edit',
            args: {
                ...button.dataset,
                currenturl: window.location.href,
            },
            saveButtonText: getString('save'),
        });
        modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, submitEventHandler);
        modalForm.show();
    });
};