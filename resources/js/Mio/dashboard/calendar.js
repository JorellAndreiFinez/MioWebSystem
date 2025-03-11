var $calendar = $("#calendar");
$calendar.fullCalendar({
    header: {
        left: ' today,prev,next,  title',
        right: 'month,agendaWeek,agendaDay '
    },
    weekends: true,
    allDaySlot: true,
    droppable: true,
    /* 允許拖入 */
    eventAfterAllRender: function(view) {
        var header = $calendar.find(".fc-header");
        // trigger current view
        header.find('.fc-header-right').find('.fc-button').off('mouseup').on('mouseup', function() {
            if (!$(this).hasClass('fc-button-agendaView')) {
                $calendar.data("view", '');
            }
        });

        //用來辨別是否在agendaView，是則重繪，否則移除
        if ($calendar.data("view") != 'agendaView') {
            header.find(".fc-button-agendaView").removeClass('fc-state-active active');
            $("#agendaView").remove();

        } else {
            renderAgendaView();
        }



    },
    eventRender: function(event, e) {

        currentView = $('#calendar').fullCalendar('getView').name; /* 用來偵測哪個view進行什麼樣的處理用 */
    },
    dayClick: function(date, jsEvent, view) {
        /* 點選日期觸發 */


    },
    eventClick: function(calEvent, jsEvent, view) {
        console.log("===== eventClick =====");
        console.log(calEvent);


    }
});

var headerRight = $calendar.find(".fc-header").find(".fc-header-right");

var agendaBtn = headerRight.find(".fc-corner-right").removeClass('fc-corner-right')
    .clone().addClass('fc-corner-right fc-button-agendaView').removeClass('fc-button-agendaDay').text("agenda");

agendaBtn.on('click', function() {
    renderAgendaView();
});

headerRight.find(".fc-header-space").before(agendaBtn);

// test event data
var date = new Date();
var d = date.getDate();
var m = date.getMonth();
var y = date.getFullYear();

var newEvent = {
    title: 'NEW EVENT',
    start: new Date(y, m, d, 10),
    end: new Date(y, m, d, 15),
    editable: true
};

// add event to calendar
var event = $('#calendar').fullCalendar('renderEvent', newEvent, 'stick');



function renderAgendaView() {

    if ($calendar.fullCalendar('getView') != 'agendaView') {
        // 複製原本的month為agendaView ，這樣可解決切換為agenda View時 month 無法點之問題
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