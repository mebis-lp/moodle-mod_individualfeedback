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

// This file keeps track of upgrades to
// the individualfeedback module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installation to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the methods of database_manager class
//
// Please do not forget to use upgrade_set_timeout()
// before any action that may take longer time to finish.

defined('MOODLE_INTERNAL') || die();

function xmldb_individualfeedback_upgrade($oldversion) {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/mod/individualfeedback/db/upgradelib.php');

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    // Moodle v2.8.0 release upgrade line.
    // Put any upgrade step following this.

    // Moodle v2.9.0 release upgrade line.
    // Put any upgrade step following this.

    // Moodle v3.0.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2016031600) {
        // Remove labels from all 'captcha' and 'label' items.
        $DB->execute('UPDATE {individualfeedback_item} SET label = ? WHERE typ = ? OR typ = ?',
                array('', 'captcha', 'label'));

        // Data savepoint reached.
        upgrade_mod_savepoint(true, 2016031600, 'individualfeedback');
    }

    if ($oldversion < 2016040100) {

        // In order to keep the previous "Analysis" results unchanged,
        // set all multiple-answer multiplechoice questions as "Do not analyse empty submits"="Yes"
        // because prior to this date this setting did not work.

        $sql = "UPDATE {individualfeedback_item} SET options = " . $DB->sql_concat('?', 'options') .
                " WHERE typ = ? AND presentation LIKE ? AND options NOT LIKE ?";
        $params = array('i', 'multichoice', 'c%', '%i%');
        $DB->execute($sql, $params);

        // individualfeedback savepoint reached.
        upgrade_mod_savepoint(true, 2016040100, 'individualfeedback');
    }

    if ($oldversion < 2016051103) {

        // Define index completed_item (unique) to be added to individualfeedback_value.
        $table = new xmldb_table('individualfeedback_value');
        $index = new xmldb_index('completed_item', XMLDB_INDEX_UNIQUE, array('completed', 'item', 'course_id'));

        // Conditionally launch add index completed_item.
        if (!$dbman->index_exists($table, $index)) {
            mod_individualfeedback_upgrade_delete_duplicate_values();
            $dbman->add_index($table, $index);
        }

        // individualfeedback savepoint reached.
        upgrade_mod_savepoint(true, 2016051103, 'individualfeedback');
    }

    if ($oldversion < 2016051104) {

        // Define index completed_item (unique) to be added to individualfeedback_valuetmp.
        $table = new xmldb_table('individualfeedback_valuetmp');
        $index = new xmldb_index('completed_item', XMLDB_INDEX_UNIQUE, array('completed', 'item', 'course_id'));

        // Conditionally launch add index completed_item.
        if (!$dbman->index_exists($table, $index)) {
            mod_individualfeedback_upgrade_delete_duplicate_values(true);
            $dbman->add_index($table, $index);
        }

        // individualfeedback savepoint reached.
        upgrade_mod_savepoint(true, 2016051104, 'individualfeedback');
    }

    if ($oldversion < 2016051105) {

        // Define field courseid to be added to individualfeedback_completed.
        $table = new xmldb_table('individualfeedback_completed');
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'anonymous_response');

        // Conditionally launch add field courseid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            // Run upgrade script to fill the new field courseid with the data from individualfeedback_value table.
            mod_individualfeedback_upgrade_courseid(false);
        }

        // Define field courseid to be added to indfeedback_completedtmp.
        $table = new xmldb_table('indfeedback_completedtmp');
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'anonymous_response');

        // Conditionally launch add field courseid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            // Run upgrade script to fill the new field courseid with the data from individualfeedback_valuetmp table.
            mod_individualfeedback_upgrade_courseid(true);
        }

        // Define table individualfeedback_tracking to be dropped.
        $table = new xmldb_table('individualfeedback_tracking');

        // Conditionally launch drop table for individualfeedback_tracking.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        // individualfeedback savepoint reached.
        upgrade_mod_savepoint(true, 2016051105, 'individualfeedback');
    }

    // Moodle v3.1.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.2.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2017032800) {

        // Delete duplicated records in individualfeedback_completed. We just keep the last record of completion.
        // Related values in individualfeedback_value won't be deleted (they won't be used and can be kept there as a backup).
        $sql = "SELECT MAX(id) as maxid, userid, individualfeedback, courseid
                  FROM {individualfeedback_completed}
                 WHERE userid <> 0
              GROUP BY userid, individualfeedback, courseid
                HAVING COUNT(id) > 1";

        $duplicatedrows = $DB->get_recordset_sql($sql);
        foreach ($duplicatedrows as $row) {
            $DB->delete_records_select('individualfeedback_completed', 'userid = ? AND individualfeedback = ? AND courseid = ? AND id <> ?', array(
                $row->userid,
                $row->individualfeedback,
                $row->courseid,
                $row->maxid,
            ));
        }
        $duplicatedrows->close();

        // individualfeedback savepoint reached.
        upgrade_mod_savepoint(true, 2017032800, 'individualfeedback');
    }

    // Automatically generated Moodle v3.3.0 release upgrade line.
    // Put any upgrade step following this.

    return true;
}
