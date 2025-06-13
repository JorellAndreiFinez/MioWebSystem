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

      var $calendar = $("#calendar");


    $calendar.fullCalendar({
        header: {
            left: 'today,prev,next title',
            right: 'month,agendaWeek,agendaDay'
        },
        weekends: true,
        allDaySlot: true,
        events: @json($events), // ✅ Laravel injected
        eventClick: function(calEvent, jsEvent, view) {
            console.log("Event clicked:", calEvent);

            let overviewHtml = '';

            if (calEvent.type === 'global') {
                const data = calEvent.data;
                 overviewHtml += `<h3>${data.title}</h3>`;
                overviewHtml += `<p><strong>Code:</strong> ${data.schedule_code ?? 'N/A'}</p>`;
                overviewHtml += `<p><strong>Description:</strong> ${data.description ?? 'N/A'}</p>`;

                if (data.days && typeof data.days === 'object') {
                    for (const [day, times] of Object.entries(data.days)) {
                        overviewHtml += `<li>${day}: ${times.start_time} - ${times.end_time}</li>`;
                    }
                } else {
                    overviewHtml += `<li>No occurrences found.</li>`;
                }

                overviewHtml += `</ul>`;
            } else if (calEvent.type === 'subject') {
                const data = calEvent.data;
                overviewHtml += `<h3>${data.title}</h3>`;
                overviewHtml += `<p><strong>Subject Code:</strong> ${data.code}</p>`;
                overviewHtml += `<p><strong>Teacher:</strong> ${data.people[data.teacher_id]?.first_name || ''} ${data.people[data.teacher_id]?.last_name || ''}</p>`;
                overviewHtml += `<p><strong>Occurrences:</strong></p><ul>`;
                for (const [day, times] of Object.entries(data.schedule.occurrence)) {
                    overviewHtml += `<li>${day}: ${times.start} - ${times.end}</li>`;
                }
                overviewHtml += `</ul>`;

                // if (data.modules) {
                //     overviewHtml += `<p><strong>Modules:</strong></p><ul>`;
                //     for (const mod of Object.values(data.modules)) {
                //         overviewHtml += `<li>${mod.title} - ${mod.description}</li>`;
                //     }
                //     overviewHtml += `</ul>`;
                // }

                // if (data.announcements) {
                //     overviewHtml += `<p><strong>Announcements:</strong></p><ul>`;
                //     for (const ann of Object.values(data.announcements)) {
                //         overviewHtml += `<li><strong>${ann.title}</strong> (${ann.date_posted}): ${ann.description}</li>`;
                //     }
                //     overviewHtml += `</ul>`;
                // }
            }

            console.log("Event clicked:", calEvent);
            console.log("Data payload:", calEvent.data);


            document.getElementById('schedule-overview').innerHTML = overviewHtml;

            
        }
    });

function renderAgendaView() {

    if ($calendar.fullCalendar('getView') != 'agendaView') {
        //agenda View month 
        $calendar.fullCalendar('changeView', 'month');
        var newView = $calendar.fullCalendar('getView');
        newView.name = 'agendaView';

        $calendar.fullCalendar('changeView', 'agendaView');
    }

    // get current events in memory
    var events = $calendar.fullCalendar('clientEvents');

    // get current date
    var currentDate = $calendar.fullCalendar('getDate');

    $calendar.find(".fc-header").find(".fc-button-agendaView").siblings().removeClass('fc-state-active').end().addClass('fc-state-active');

    $calendar.data("view", 'agendaView');
    var agendaViewHtml = document.createElement('div');
    agendaViewHtml.setAttribute("id", "agendaView");
    var contents = "<table>" +
        "<thead><tr>" +
        "<th class='fc-widget-header fc-agendaView-event-start'>DateStart</th>" +
        "<th class='fc-widget-header fc-agendaView-event-end'>DateEnd</th>" +
        "<th class='fc-widget-header fc-agendaView-event-title'>Event</th>" +
        "</tr></thead>" +
        "<tbody>";

    for (key in events) {
        //  detect month range
        var monthRange = moment().range(moment(currentDate).startOf('month'), moment(currentDate).endOf('month'));
        var eventStart = moment(events[key].start).format("YYYY/MM/DD-H:mm:ss");
        var eventEnd = moment(events[key].end).format("YYYY/MM/DD-H:mm:ss");
        if (monthRange.contains(events[key].start) && monthRange.contains(events[key].end)) {
            var eventTitle = events[key].title;
            contents += '<tr>' +
                '<td class="fc-widget-content">' + eventStart + '</td>' +
                '<td class="fc-widget-content">' + eventEnd + '</td>' +
                '<td class="fc-widget-content">' + eventTitle + '</td>' +
                '</tr>';

        }
    }
    contents += "</tbody></table>";
    agendaViewHtml.innerHTML = contents;
    $calendar.find(".fc-content").html(agendaViewHtml);

    // console.log(events);
}
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

