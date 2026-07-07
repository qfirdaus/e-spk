"use strict";

function getJsonPLO() {
    var jsonobj = [];
    $("input[class=plo]").each(function () {
        jsonobj.push({
            kod_plo: $(this).attr("title"),
            percentage: $(this).val()
        });
    });
    return jsonobj;
}

function getJsonPEO() {
    var jsonobj = [];
    $("input[class=peo]").each(function () {
        jsonobj.push({
            kod_peo: $(this).attr("title"),
            percentage: $(this).val()
        });
    });
    return jsonobj;
}

$(document).ready(function () {

    var jsonPLO = JSON.parse(JSON.stringify(getJsonPLO()));
    var jsonPEO = JSON.parse(JSON.stringify(getJsonPEO()));

    var chart = AmCharts.makeChart("pie_peo", {
        "type": "pie",
        "theme": "none",
        "dataProvider": jsonPEO,
        "valueField": "percentage",
        "titleField": "kod_peo",
        "outlineAlpha": 0.4,
        "depth3D": 15,
        "balloonText": "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
        "angle": 30,
        "export": {
            "enabled": true
        }
    });
    var chart = AmCharts.makeChart("bar_peo", {
        "type": "serial",
        "theme": "light",
        "precision": 2,
        "valueAxes": [{
                "id": "v1",
                "fontSize": 0,
                "axisAlpha": 0,
                "lineAlpha": 0,
                "gridAlpha": 0,
                "position": "center",
                "autoGridCount": false,
                "labelFunction": function (value) {
                    return Math.round(value) + "%";
                }
            }],
        "graphs": [{
                "id": "g3",
                "valueAxis": "v1",
                "lineColor": "#008B8B",
                "fillColors": "#008B8B",
                "fillAlphas": 0.3,
                "type": "column",
                "valueField": "percentage",
                "columnWidth": 0.5,
                "legendValueText": "$[[value]]M",
                "balloonText": "[[title]]<br /><b style='font-size: 130%'>[[value]]%</b>"
            }],
        "chartCursor": {
            "pan": true,
            "valueLineEnabled": true,
            "valueLineBalloonEnabled": true,
            "cursorAlpha": 0,
            "valueLineAlpha": 0.2
        },
        "categoryField": "kod_peo",
        "categoryAxis": {
            "axisAlpha": 0,
            "lineAlpha": 0,
            "gridAlpha": 0,
            "minorGridEnabled": true,
        },
        "balloon": {
            "borderThickness": 1,
            "shadowAlpha": 0
        },
        "export": {
            "enabled": true
        },
        "dataProvider": jsonPEO
    });

    var chart = AmCharts.makeChart("pie_plo", {
        "type": "pie",
        "theme": "none",
        "dataProvider": jsonPLO,
        "valueField": "percentage",
        "titleField": "kod_plo",
        "outlineAlpha": 0.4,
        "depth3D": 15,
        "balloonText": "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
        "angle": 30,
        "export": {
            "enabled": true
        }
    });
    var chart = AmCharts.makeChart("bar_plo", {
        "type": "serial",
        "theme": "light",
        "precision": 2,
        "valueAxes": [{
                "id": "v1",
                "fontSize": 0,
                "axisAlpha": 0,
                "lineAlpha": 0,
                "gridAlpha": 0,
                "position": "center",
                "autoGridCount": false,
                "labelFunction": function (value) {
                    return Math.round(value) + "%";
                }
            }],
        "graphs": [{
                "id": "g3",
                "valueAxis": "v1",
                "lineColor": "#00008B",
                "fillColors": "#00008B",
                "fillAlphas": 0.3,
                "type": "column",
                "valueField": "percentage",
                "columnWidth": 0.5,
                "legendValueText": "$[[value]]M",
                "balloonText": "[[title]]<br /><b style='font-size: 130%'>[[value]]%</b>"
            }],
        "chartCursor": {
            "pan": true,
            "valueLineEnabled": true,
            "valueLineBalloonEnabled": true,
            "cursorAlpha": 0,
            "valueLineAlpha": 0.2
        },
        "categoryField": "kod_plo",
        "categoryAxis": {
            "axisAlpha": 0,
            "lineAlpha": 0,
            "gridAlpha": 0,
            "minorGridEnabled": true,
        },
        "balloon": {
            "borderThickness": 1,
            "shadowAlpha": 0
        },
        "export": {
            "enabled": true
        },
        "dataProvider": jsonPLO
    });
});

