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

    calendar.render();
}