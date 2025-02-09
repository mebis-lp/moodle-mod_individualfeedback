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
 * Edit items in individualfeedback module
 *
 * @module     mod_individualfeedback/edit
 * @package    mod_individualfeedback
 * @copyright  2016 Marina Glancy
 */
define(['jquery', 'core/ajax', 'core/str', 'core/notification'],
function($, ajax, str, notification) {
    var manager = {
        deleteItem: function(e) {
            e.preventDefault();
            var targetUrl = $(e.currentTarget).attr('href');

            stringkey = 'confirmdeleteitem';
            if ($(this).hasClass('questiongroup')) {
                stringkey = 'confirmdeleteitem_questiongroup';
            }

            str.get_strings([
                {
                    key:        'confirmation',
                    component:  'admin'
                },
                {
                    key:        stringkey,
                    component:  'mod_individualfeedback'
                },
                {
                    key:        'yes',
                    component:  'moodle'
                },
                {
                    key:        'no',
                    component:  'moodle'
                }
            ])
            .then(function(s) {
                notification.confirm(s[0], s[1], s[2], s[3], function() {
                    window.location = targetUrl;
                });

                return;
            })
            .catch();
        },

        setup: function() {
            $('body').delegate('[data-action="delete"]', 'click', manager.deleteItem);
        }
    };

    return {
        setup: manager.setup
    };
});
