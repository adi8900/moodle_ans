/**
 *
 * @module      mod_nextblocks/overview
 * @copyright   2025 Rui Correia<rjr.correia@campus.fct.unl.pt>
 * @copyright   based on work by 2024 Duarte Pereira<dg.pereira@campus.fct.unl.pt>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function() {
    return {
        init: function(activityId) {
            const rows = document.querySelectorAll('.user-submission-url');
            for (const row of rows) {
                const userId = row.getAttribute('id').split("=")[1];

                // Change row href to report.php
                row.href = `report.php?id=${activityId}&userid=${userId}`;
            }
        }
    };
});