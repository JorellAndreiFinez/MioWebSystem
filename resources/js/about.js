var swiper = new Swiper('.blog-slider', {
    spaceBetween: 30,
    effect: 'fade',
    loop: true,
    mousewheel: {
        invert: false,
    },
    // autoHeight: true,
    pagination: {
        el: '.blog-slider__pagination',
        clickable: true,
    }
});

Highcharts.chart('orgchart', {

    chart: {
        height: 600,
        inverted: true
    },

    title: {
        text: 'Philippine Institute for the Deaf'
    },

    series: [{
        type: 'organization',
        name: 'Philippine Institute for the Deaf',
        keys: ['from', 'to'],
        data: [
            [26, 35],
            [26, 48],
            [26, 11],
            [26, 50],
            [48, 'Product'],
            [48, 'Web'],
            [11, 'Sales'],
            [11, 'vor'],
            [50, 'Market'],
            ['Web', 49]
        ],
        levels: [{
            level: 0,
            color: 'silver',
            dataLabels: {
                color: 'black'
            },
            height: 25
        }, {
            level: 1,
            color: 'silver',
            dataLabels: {
                color: 'black'
            },
            height: 25
        }, {
            level: 2,
            color: '#980104'
        }, {
            level: 4,
            color: '#359154'
        }],
        nodes: [{
                id: 26,
                title: 'Chairman of the Board',
                name: 'Cesar V. Campos',
                image: 'http://www.frgrisk.com/wp-content/uploads/2017/12/JohnBell.jpg'
            }, {
                id: 35,
                title: 'Partner - Sales',
                name: 'Mike Forno',
                column: 1,
                image: 'http://www.frgrisk.com/wp-content/uploads/2017/12/MichaelForno.jpg',
                layout: 'hanging'
            }, {
                id: 48,
                title: 'Partner - Solution Services',
                name: 'Tim Weeks',
                column: 1,
                image: 'http://www.frgrisk.com/wp-content/uploads/2017/12/TimWeeks.jpg',
                layout: 'hanging'
            }, {
                id: 11,
                title: 'Technology Director',
                name: 'Chuck Beck',
                column: 1,
                image: 'http://www.frgrisk.com/wp-content/uploads/2017/12/ChuckBeck.jpg',
                layout: 'hanging'
            }, {
                id: 50,
                title: 'Operations Manager',
                name: 'Wendy Cutler',
                column: 1,
                image: 'http://www.frgrisk.com/wp-content/uploads/2017/12/WendyCutler-1.jpg',
                layout: 'hanging'
            }, {
                id: 'Product',
                name: 'Data Team',
                column: 2
            }, {
                id: 'Web',
                name: 'Solution Services',
                column: 2
            }, {
                id: 'Sales',
                name: 'IT Department',
                column: 2
            }, {
                id: 'vor',
                name: 'VoR Platform',
                column: 2
            },
            {
                id: 'Market',
                name: 'Operations Team',
                column: 2
            },
            {
                id: 49,
                name: 'Valerie Cooper',
                column: 3,
                layout: 'hanging'
            }
        ],
        colorByPoint: false,
        color: '#007ad0',
        dataLabels: {
            color: 'white'
        },
        borderColor: 'white',
        nodeWidth: 65
    }],
    tooltip: {
        outside: true
    },
    exporting: {
        allowHTML: true,
        sourceWidth: 800,
        sourceHeight: 600
    }

});
