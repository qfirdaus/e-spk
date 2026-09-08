$(document).ready(function() {

    setTimeout(function() {
        if ($('.select2').length && typeof $.fn.select2 !== 'undefined') {
            $('.select2').select2({ width: '100%' });
        }
    }, 500);

    if (typeof AmCharts !== 'undefined') {
        
        // CARTA PEO
        if (typeof PEO_DATA !== 'undefined' && PEO_DATA.length > 0) {
            
            // PIE CHART
            AmCharts.makeChart("pie_peo", {
                "type": "pie",
                "theme": "light",
                "colors": ["#5470c6", "#91cc75", "#fac858", "#ee6666", "#73c0de", "#3ba272", "#fc8452"],
                "dataProvider": PEO_DATA,
                "valueField": "percentage",
                "titleField": "kod_peo",
                "outlineAlpha": 1,
                "outlineColor": "#ffffff",
                "outlineThickness": 2, 
                "depth3D": 12,        
                "angle": 25,
                "balloonText": "[[title]]<br><span style='font-size:14px'><b>[[value]]%</b></span>",
                "export": { "enabled": true }
            });

            // BAR CHART
            AmCharts.makeChart("bar_peo", {
                "type": "serial",
                "theme": "light",
                "dataProvider": PEO_DATA,
                "categoryField": "kod_peo",
                "graphs": [{
                    "valueField": "percentage",
                    "type": "column",
                    "lineColor": "#7d5bd2",  
                    "fillColors": "#7d5bd2", 
                    "fillAlphas": 0.9,
                    "columnWidth": 0.5,      
                    "balloonText": "[[category]]<br /><b style='font-size: 130%'>[[value]]%</b>"
                }],
                "export": { "enabled": true }
            });
        }

        // CARTA PLO
        if (typeof PLO_DATA !== 'undefined' && PLO_DATA.length > 0) {
            
            AmCharts.makeChart("pie_plo", {
                "type": "pie",
                "theme": "light",
                "colors": ["#14b8a6", "#f59e0b", "#f43f5e", "#6366f1", "#8b5cf6", "#ec4899", "#10b981"],
                "dataProvider": PLO_DATA,
                "valueField": "percentage",
                "titleField": "kod_plo",
                "outlineAlpha": 1,
                "outlineColor": "#ffffff",
                "outlineThickness": 2,
                "depth3D": 12,
                "angle": 25,
                "balloonText": "[[title]]<br><span style='font-size:14px'><b>[[value]]%</b></span>",
                "export": { "enabled": true }
            });

            // BAR CHART 
            AmCharts.makeChart("bar_plo", {
                "type": "serial",
                "theme": "light",
                "dataProvider": PLO_DATA,
                "categoryField": "kod_plo",
                "graphs": [{
                    "valueField": "percentage",
                    "type": "column",
                    "lineColor": "#db5555",  
                    "fillColors": "#db5555", 
                    "fillAlphas": 0.9,
                    "columnWidth": 0.5,
                    "balloonText": "[[category]]<br /><b style='font-size: 130%'>[[value]]%</b>"
                }],
                "export": { "enabled": true }
            });
        }
    }
});