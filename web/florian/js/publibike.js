var data = {stations:[{id:0,name:"Rivier",latitude:46.521978,longitude:6.564896,trips:[311,2179,189,200,175,223,73,363,32,71],other_incomings:605,other_outgoings:653},{id:1,name:"Quartier nord",latitude:46.5224054,longitude:6.5655126,trips:[2157,519,646,269,576,597,127,476,59,93],other_incomings:719,other_outgoings:676},{id:2,name:"Place Alan Turing",latitude:46.518951,longitude:6.562162,trips:[166,619,271,19,78,135,43,138,14,18],other_incomings:220,other_outgoings:254},{id:3,name:"Quartier de l'innovation",latitude:46.517153,longitude:6.561272,trips:[178,298,28,182,35,57,8,65,5,6],other_incomings:116,other_outgoings:112},{id:4,name:"Colladon",latitude:46.518108,longitude:6.564945,trips:[196,367,93,59,410,263,72,439,27,50],other_incomings:505,other_outgoings:456},{id:5,name:"Piccard",latitude:46.520723,longitude:6.568276,trips:[272,585,151,59,247,547,74,688,48,111],other_incomings:793,other_outgoings:704},{id:6,name:"Sorge",latitude:46.521043,longitude:6.573253,trips:[88,127,29,3,78,112,170,190,81,164],other_incomings:342,other_outgoings:268},{id:7,name:"Centre Sportif UNIL EPFL",latitude:46.519805,longitude:6.579676,trips:[315,584,79,55,279,585,165,705,367,464],other_incomings:949,other_outgoings:1106},{id:8,name:"UNIL Centre",latitude:46.521668,longitude:6.581345,trips:[53,83,13,4,18,57,115,304,229,79],other_incomings:405,other_outgoings:394},{id:9,name:"Dorigny",latitude:46.522591,longitude:6.584961,trips:[115,104,31,7,31,109,123,397,94,370],other_incomings:518,other_outgoings:548}]};

var stations = data.stations;

var minLatitude = Number.POSITIVE_INFINITY, maxLatitude = Number.NEGATIVE_INFINITY, minLongitude = Number.POSITIVE_INFINITY, maxLongitude = Number.NEGATIVE_INFINITY;

for(var i = 0; i < stations.length; i++) {
    var station = stations[i];
    minLatitude = Math.min(minLatitude, station.latitude);
    maxLatitude = Math.max(maxLatitude, station.latitude);
    minLongitude = Math.min(minLongitude, station.longitude);
    maxLongitude = Math.max(maxLongitude, station.longitude);
}

var latitudeRange = maxLatitude - minLatitude, longitudeRange = maxLongitude - minLongitude;
var rangeRatio = longitudeRange / (1.5 * latitudeRange); // TODO critical!

var totalTripsInside = 0, totalTrips = 0;
for(i = 0; i < stations.length; i++) {
    station = stations[i];
    var exiting = 0, arriving = 0;
    for(var j = 0; j < station.trips.length; j++) {
        exiting += station.trips[j];
        arriving += stations[j].trips[i];
    }
    totalTripsInside += exiting;
    exiting += station.other_outgoings;
    arriving += station.other_incomings;
    station.totalExiting = exiting;
    station.totalArriving = arriving;
    totalTrips += exiting;
}

var minValue = Number.POSITIVE_INFINITY, maxValue = 0;
var minGlobal = Number.POSITIVE_INFINITY, maxGlobal = 0;
for(i = 0; i < stations.length; i++) {
    from = stations[i];
    for (j = i + 1; j < stations.length; j++) {
        to = stations[j];
        var sum = from.trips[j] + to.trips[i];
        minValue = Math.min(sum, minValue);
        maxValue = Math.max(sum, maxValue);
        minGlobal = Math.min(from.trips[j], to.trips[i], from.other_incomings, from.other_outgoings, minGlobal);
        maxGlobal = Math.max(from.trips[j], to.trips[i], from.other_incomings, from.other_outgoings, maxGlobal);
    }
}

var totalDepartures = [0], totalArrivals = [0];
for(i = 0; i < stations.length; i++) {
    totalDepartures[0] += stations[i].other_incomings;
    totalArrivals[0] += stations[i].other_outgoings;
}
for(i = 0; i < stations.length; i++) {
    var sumDepartures = stations[i].other_outgoings, sumArrivals = stations[i].other_incomings;
    for (j = 0; j < stations.length; j++) {
        sumDepartures += stations[i].trips[j];
        sumArrivals += stations[j].trips[i];
    }
    totalDepartures.push(sumDepartures);
    totalArrivals.push(sumArrivals);
}


function colorGradient(value, min, max) {
    var norm = Math.log(value - min + 1) / Math.log(max - min + 1);
    return "rgb(" + Math.round(255 * (1 - 0.6 * norm)) + "," + 0 + "," + 50 + ")";
}

function percentageFormat(v) {
    var p = 100 * v;
    return p.toLocaleString(undefined, { maximumFractionDigits: 2, minimumFractionDigits: 1 }) + "%";
}

function drawGraph() {
    var allowedWidth = $('div.content')[0].clientWidth * 0.8, allowedHeight = window.innerHeight * 0.8;
    var allowedRatio = allowedWidth / allowedHeight;

    var width, height;

    if(rangeRatio >= allowedRatio) { // Horizontal overflow
        width = allowedWidth;
        height = allowedWidth / rangeRatio;
    } else { // Vertical overflow
        width = allowedHeight * rangeRatio;
        height = allowedHeight;
    }

    var circleRadius = width / 80;

    var realWidth = width, realHeight = height * 1.6;

    for(i = 0; i < stations.length; i++) {
        station = stations[i];
        station.x = (station.longitude - minLongitude) / longitudeRange * width + (realWidth - width) / 2;
        station.y = (maxLatitude - station.latitude) / latitudeRange * height + (realHeight - height) / 2;
    }

    width = realWidth;
    height = realHeight;

    var svg = d3.select("#graph").append("svg")
        .attr("width", width)
        .attr("height", height);

    var tooltipOffset = -50;

    for(i = 0; i < stations.length; i++) {
        for(j = 0; j < stations.length; j++) {
            let from = stations[i], to = stations[j];
            var total = from.trips[j] + to.trips[i];
            let ratio = total / totalTrips;
            var temp;

            if(j % 2 === 0) { // Randomize
                temp = from;
                from = to;
                to = temp;
            }
            if(i > j) { // No self loops for now
                var vc = -0.3; // Curvature
                var vx = to.x - from.x, vy = to.y - from.y;
                var mx = (from.x + to.x) / 2, my = (from.y + to.y) / 2;
                var len = Math.sqrt(vx * vx + vy * vy);
                //vc *= 0.3 * width / len;
                var tx = mx + vy * vc, ty = my - vx * vc;

                var path = "M" + from.x + "," + from.y + " Q" + tx + "," + ty + " " + to.x + "," + to.y;
                var strokeWidth = 0.18 * width * ratio;

                var parent = svg.append("g")
                    .attr("fill", "none")
                    .attr("class", ["edge", "station-" + i, "station-" + j].join(" "))
                    .on("mouseover", function() {
                        $(".edge").addClass("unselected").removeClass("selected");
                        $(this).addClass("selected").removeClass("unselected");
                        $("#tip").addClass("tooltip-visible").removeClass("tooltip-hidden");
                        $("#tip-line1").text(from.name + " ↔ " + to.name);
                        $("#tip-line2").text(percentageFormat(ratio) + " des trajets");
                    })
                    .on("mouseout", function() {
                        $(".edge").addClass("selected").removeClass("unselected");
                        $("#tip").addClass("tooltip-hidden").removeClass("tooltip-visible");
                    })
                    .on("mousemove", function () {
                        var pos = d3.mouse(svg.node());
                        tip.attr("transform", "translate(" + pos[0] + "," + (pos[1] + tooltipOffset) + ")");
                    });

                function createElement(parent, path, color, width) {
                    return parent.append("path")
                        .attr("d", path)
                        .attr("stroke", color)
                        .attr("stroke-width", width)

                }

                createElement(parent, path, "#ffffff00", circleRadius);
                createElement(parent, path, colorGradient(total, minValue, maxValue), strokeWidth);
            }
        }
    }

    svg.selectAll("circle")
        .data(stations)
        .enter()
        .append("circle")
        .attr("cx", function(d) { return d.x; })
        .attr("cy", function(d) { return d.y; })
        .attr("r", function(d) { return circleRadius; })
        .attr("fill", "#12316b")
        .attr("stroke", "white")
        .attr("stroke-width", circleRadius / 3)
        .attr("data-name", function(d) { return d.name; })
        .attr("class", "station")
        .on("mouseover", function(d) {
            $(".edge").addClass("unselected").removeClass("selected");
            $(".edge.station-" + d.id).addClass("selected").removeClass("unselected");
            $("#tip").addClass("tooltip-visible").removeClass("tooltip-hidden")
                .attr("transform", "translate(" + parseFloat($(this).attr("cx")) + "," + (parseFloat($(this).attr("cy")) + tooltipOffset) + ")");
            $("#tip-line1").text("Station " + d.name);
            $("#tip-line2").text(percentageFormat((d.totalExiting + d.totalArriving) / totalTrips) + " d'utilisation");
        })
        .on("mouseout", function() {
            $(".edge").addClass("selected").removeClass("unselected");
            $("#tip").addClass("tooltip-hidden").removeClass("tooltip-visible");
        });


    var tip = svg.append("g")
        .attr("id", "tip")
        .attr("class", "tip tooltip-hidden")
        .attr("dominant-baseline", "central")
        .attr("text-anchor", "middle")
        .style("pointer-events", "none");

    tip.append("text").attr("id", "tip-line1");
    tip.append("text").attr("id", "tip-line2").attr("y", 25);
}


function drawMatrix() {

    var allowedWidth = $('div.content')[0].clientWidth * 0.8, allowedHeight = window.innerHeight * 0.8;
    var width = Math.min(allowedWidth, allowedHeight), height = stations.length / (stations.length + 1) * width;

    var svg = d3.select("#matrix").append("svg")
        .attr("width", width)
        .attr("height", height);

    function polygonPath(coordinates) {
        var str = "";
        for(let i = 0; i < coordinates.length; i++) {
            str += coordinates[i].x + "," + coordinates[i].y;
            if(i < coordinates.length - 1)
                str += " ";
        }
        return str;
    }

    function generateTriangle(array, value, totalFrom, totalTo, from, to) {
        var color = colorGradient(value, minGlobal, maxGlobal);
        let val = value / totalTrips, vFrom = value / totalFrom, vTo = value / totalTo;
        svg.append("polygon")
            .attr("points", polygonPath(array))
            .attr("stroke", color)
            .attr("stroke-width", 0.85) // Same trick over and over again
            .attr("fill", color)
            .attr("class", "station-" + from.id + " station-" + to.id)
            .on("mouseover", function(d) {
                var that = $(this);
                $("polygon").addClass("triangle-unselected").removeClass("triangle-selected");
                that.addClass("triangle-selected").removeClass("triangle-unselected");
                $("#tip2").addClass("tooltip-visible").removeClass("tooltip-hidden");
                $("#tip2-line1").text(from.name + " → " + to.name);
                $("#tip2-line2").text(percentageFormat(val) + " des trajets");
                $("#tip2-line3").text(percentageFormat(vFrom) + " des départs");
                $("#tip2-line4").text(percentageFormat(vTo) + " des arrivées");
            })
            .on("mouseout", function() {
                $("polygon").addClass("triangle-selected").removeClass("triangle-unselected");
                $("#tip2").addClass("tooltip-hidden").removeClass("tooltip-visible");
            })
            .on("mousemove", function() {
                var pos = d3.mouse(svg.node());
                $("#tip2")
                    .attr("transform", "translate(" + pos[0] + "," + (pos[1] + -100) + ")");
            });
    }

    var textOut = "Hors campus";
    var step = height / (stations.length + 1);
    for(let i = 0; i < stations.length + 1; i++) { // Very confusing, I'm aware of that
        if(i !== 0) {
            var index = stations.length - i;
            station = stations[index];
            var trips = station.trips[index];
            generateTriangle(
                [{x: (stations.length - i) * step, y: i * step}, {x: (stations.length - i + 1) * step, y: i * step}, {x: (stations.length - i) * step, y: (i + 1) * step}],
                trips, totalDepartures[index + 1], totalDepartures[index + 1], station, station) // Purposefully did not use `totalArrivals`
        }
        for (let j = 0; j < stations.length - i; j++) {
            let top, bottom;
            let totalD, totalA;
            let a, b;
            if(i === 0) {
                top = stations[j].other_incomings;
                bottom = stations[j].other_outgoings;
                totalD = totalArrivals[0];
                totalA = totalDepartures[j + 1];
                a = {id: "out", name: textOut};
                b = stations[j];
            } else {
                top = stations[stations.length - i].trips[j];
                bottom = stations[j].trips[stations.length - i];
                totalD = totalDepartures[stations.length - i + 1];
                totalA = totalArrivals[j + 1];
                a = stations[stations.length - i];
                b = stations[j];
            }

            var array = [{x: j * step, y: i * step}, {x: (j + 1) * step, y: (i + 1) * step}];

            var a1 = array.slice(0), a2 = array.slice(0);
            a1.push({x: (j + 1) * step, y: i * step}); // Top
            a2.push({x: j * step, y: (i + 1) * step}); // Bottom

            generateTriangle(a1, top, totalD, totalA, a, b); // Top
            generateTriangle(a2, bottom, totalA, totalD, b, a); // Bottom
        }

        var name = i === 0 ? textOut : stations[stations.length - i].name;
        svg.append("text")
            .attr("x", ( i > 0 ? step / 2 : 0) + (stations.length - i + 0.2) * step)
            .attr("y", i * step + step / 2)
            .attr("dominant-baseline", i === 0 ? "middle" : "hanging")
            .attr("font-size", step / 3)
            .text((i === 0 ? "" : "┚") + name)
            .on("mouseover", function(d) {
                $("polygon").addClass("unselected").removeClass("selected");
                $(".station-" + (i === 0 ? "out" : (stations.length - i))).addClass("selected").removeClass("unselected");
            })
            .on("mouseout", function() {
                $("polygon").removeClass("unselected").addClass("selected");
            });
    }

    var tip = svg.append("g")
        .attr("id", "tip2")
        .attr("class", "tip tooltip-hidden")
        .attr("dominant-baseline", "central")
        .attr("text-anchor", "middle")
        .style("pointer-events", "none");

    tip.append("text").attr("id", "tip2-line1");
    tip.append("text").attr("id", "tip2-line2").attr("y", 25);
    tip.append("text").attr("id", "tip2-line3").attr("y", 50);
    tip.append("text").attr("id", "tip2-line4").attr("y", 75);
}

drawGraph();
drawMatrix();