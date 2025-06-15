

<section class="home-section " style="margin-bottom: 3rem;">

<div class="text">Calendar</div>

<div class="grid-container">

  <!-- Begin Main-->
  <main class="main">

    <!--Begin Main Cards-->
    <div class="main-cards">
      <div class="card">
        <div id="calendar">

        </div>
      </div>
    </div>

    <div class="main-cards">
    <div class="card">
        <div id="schedule-overview"></div>
    </div>
    <div class="card">
        <h3>Agenda This Week</h3>
        <div id="agenda-this-week"></div>
    </div>
    <div class="card">
        <h3>Schedule Today</h3>
        <div id="schedule-today"></div>
    </div>
    </div>

  </main>

</div>

<br>
<br>
<br>

</section>


<script>
    var $calendar = $("#calendar");

    var globalEvents = @json($globalEvents);
    var subjectEvents = @json($subjectEvents);
    var allEvents = globalEvents.concat(subjectEvents);


    $calendar.fullCalendar({
        header: {
            left: 'today,prev,next title',
            right: 'month,agendaWeek,agendaDay'
        },
        weekends: true,
        allDaySlot: true,
         events: allEvents, // ✅ Laravel injected
        eventClick: function(calEvent, jsEvent, view) {
            console.log("Event clicked:", calEvent);

            let overviewHtml = '';

            if (calEvent.type === 'global') {
                const data = calEvent.data;
                overviewHtml += `<h3>${data.schedule_name ?? 'Unnamed Schedule'}</h3>`;
                overviewHtml += `<p><strong>Code:</strong> ${data.schedule_code ?? 'N/A'}</p>`;
                overviewHtml += `<p><strong>Description:</strong> ${data.description ?? 'N/A'}</p>`;

                const occurrences = data.occurrences ?? data.days ?? {};
                if (Object.keys(occurrences).length > 0) {
                    overviewHtml += `<p><strong>Occurrences:</strong></p><ul>`;
                    for (const [day, times] of Object.entries(occurrences)) {
                        const start = times.start_time ?? times.start;
                        const end = times.end_time ?? times.end;
                        overviewHtml += `<li>${day}: ${start} - ${end}</li>`;
                    }
                    overviewHtml += `</ul>`;
                } else {
                    overviewHtml += `<p>No occurrences found.</p>`;
                }
            }

            if (calEvent.type === 'subject') {
                const data = calEvent.data;
                const schedule = data.schedule ?? {};
                const occurrences = schedule.occurrence ?? {};
                overviewHtml += `<h3>${data.title ?? 'Unnamed Subject'}</h3>`;
                overviewHtml += `<p><strong>Subject Code:</strong> ${data.code ?? 'N/A'}</p>`;
                overviewHtml += `<p><strong>Grade Level:</strong> ${data.gradelevel ?? 'N/A'}</p>`;

                if (data.people && data.teacher_id && data.people[data.teacher_id]) {
                    const teacher = data.people[data.teacher_id];
                    overviewHtml += `<p><strong>Teacher:</strong> ${teacher.first_name} ${teacher.last_name}</p>`;
                }

                if (Object.keys(occurrences).length > 0) {
                    overviewHtml += `<p><strong>Occurrences:</strong></p><ul>`;
                    for (const [day, times] of Object.entries(occurrences)) {
                        const start = times.start ?? times.start_time ?? 'N/A';
                        const end = times.end ?? times.end_time ?? 'N/A';
                        overviewHtml += `<li>${day}: ${start} - ${end}</li>`;
                    }
                    overviewHtml += `</ul>`;
                } else {
                    overviewHtml += `<p>No schedule details available.</p>`;
                }
            }

            document.getElementById('schedule-overview').innerHTML = overviewHtml;
        }

    });


</script>

<script>
function populateAgendaAndToday() {
    const events = $calendar.fullCalendar('clientEvents');
    const today = moment().startOf('day');
    const endOfToday = moment().endOf('day');
    const startOfWeek = moment().startOf('isoWeek');
    const endOfWeek = moment().endOf('isoWeek');

    let agendaItems = [];
    let todayItems = [];

    events.forEach(event => {
        const start = moment(event.start);
        const end = moment(event.end);

        const eventDisplay = `<li><strong>${event.title}</strong><br>${start.format("ddd, MMM D, h:mm A")} - ${end.format("h:mm A")}</li>`;

        // This week
        if (start.isBetween(startOfWeek, endOfWeek, null, '[]')) {
            agendaItems.push(eventDisplay);
        }

        // Today
        if (start.isBetween(today, endOfToday, null, '[]')) {
            todayItems.push(eventDisplay);
        }
    });

    const agendaHtml = agendaItems.length > 0 ? `<ul>${agendaItems.join('')}</ul>` : "<p>No schedule this week.</p>";
    const todayHtml = todayItems.length > 0 ? `<ul>${todayItems.join('')}</ul>` : "<p>No schedule today.</p>";

    document.getElementById("agenda-this-week").innerHTML = agendaHtml;
    document.getElementById("schedule-today").innerHTML = todayHtml;
}

// Run this once calendar is ready
$(document).ready(function() {
    setTimeout(populateAgendaAndToday, 500); // delay to ensure calendar events are loaded
});
</script>

