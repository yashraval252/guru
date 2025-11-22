/**
 * Calendar initialization and management
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeCalendar();
});

function initializeCalendar() {
    const calendarEl = document.getElementById('calendar');

    if (!calendarEl) return;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        events: function(info, successCallback, failureCallback) {
            fetch('api/calendar.php')
                .then(response => {
                    if (!response.ok) throw new Error('Failed to fetch calendar events');
                    return response.json();
                })
                .then(data => {
                    successCallback(data);
                })
                .catch(error => {
                    console.error('Error fetching calendar events:', error);
                    failureCallback(error);
                });
        },
        height: 'auto',
        contentHeight: 'auto',
        eventClick: function(info) {
            // Handle event click - could show modal with entry details
            console.log('Event clicked:', info.event);
        },
        datesSet: function(info) {
            // Calendar range has changed
            console.log('Calendar view changed:', info);
        }
    });

    // expose the instance for other scripts
    try {
        window.APP_CALENDAR = calendar;
    } catch (e) {
        console.warn('Could not attach calendar to window', e);
    }

    calendar.render();
}

// expose calendar so other scripts can add/remove events dynamically
window.APP_CALENDAR = window.APP_CALENDAR || null;
document.addEventListener('DOMContentLoaded', function() {
    // set reference after initializeCalendar runs
    const trySet = () => {
        const el = document.getElementById('calendar');
        if (!el) return;
        // if calendar instance exists in FullCalendar globals, find it
        if (window.FullCalendar) {
            // we stored calendar in closure; easiest is to find via dataset, but we will set when rendering
        }
    };
    // no-op; actual instance is attached inside initializeCalendar by replacing this module if needed
});