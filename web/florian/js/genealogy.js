var getCanvasSize = function (scale) {
    var div = $('div.content')[0];
    var computedStyle = getComputedStyle(div);
    return Math.min(div.clientWidth - parseFloat(computedStyle.paddingLeft) - parseFloat(computedStyle.paddingRight), window.innerHeight) * scale;
};

var generateHierarchy = function (data) {
    var getMaxDepth = function(node) {
        var explore = [node];
        var i = 0;
        while(explore.length > 0) {
            var next = [];
            for(var j = 0; j < explore.length; j++)
                if(explore[j].children)
                    for(var k = 0; k < explore[j].children.length; k++)
                        next.push(explore[j].children[k])
            explore = next;
            i++;
        }
        return i;
    };
    var maxDepth = getMaxDepth(data);

    var scale = 0.8;
    var size = getCanvasSize(scale);
    var width = size, height = size;
    var radius = Math.min(width, height) / 2;

    var localityData = {
        "12": {"name": "Aveyron", "color": "#56f107"},
        "13": {"name": "Bouches-du-Rhône", "color": "#ff9514"},
        "15": {"name": "Cantal", "color": "#47f564"},
        "28": {"name": "Eure-et-Loir", "color": "#d8e1e3"},
        "34": {"name": "Hérault", "color": "#ffc532"},
        "39": {"name": "Jura", "color": "#d1fd02"},
        "45": {"name": "Loiret", "color": "#bdbc54"},
        "46": {"name": "Lot", "color": "#2ad010"},
        "49": {"name": "Maine-et-Loire", "color": "#373dff"},
        "50": {"name": "Manche", "color": "#c9ffe3"},
        "54": {"name": "Meurthe-et-Moselle", "color": "#f8f400"},
        "57": {"name": "Moselle", "color": "#ffe80d"},
        "75": {"name": "Paris", "color": "#49deff"},
        "89": {"name": "Yonne", "color": "#c86363"},
        "94": {"name": "Val-de-Marne", "color": "#24c2e5"},

        "lu": {"name": "Luxembourg", "color": "#ff28cb"}, // Luxembourg

        "?": {"name": "Inconnu", "color": "#f4f5f7"}, // Unknown
        "other": {"name": "Autre", "color": "#f4f5f7"} // Other
    };

    var getLocalityData = function (code) {
        if(localityData[code])
            return localityData[code];
        else
            return localityData.other;
    };

    // Root SVG element
    var g = d3.select('#wheel')
        .append('svg')
        .attr('width', width)
        .attr('height', height)
        .style('overflow', 'visible')
        .append('g')
        .attr('transform', 'translate(' + width / 2 + ',' + height / 2 + ')');

    var partition = d3.partition()
        .size([2 * Math.PI, radius]);

    data.sosa = 1;
    var root = d3.hierarchy(data)
        .each(function (d) { d.value = 1 << (maxDepth - d.depth); })
        .sort(function (a, b) {
            return a.data.sex === "M" ? -1 : 1;
        })
        .each(function(d) { if(!d.data.sosa) d.data.sosa = d.parent.data.sosa * 2 + (d.data.sex === "M" ? 0 : 1); }) // TODO
        .each(function(d) { if(d.parent && d.data.area === "?") d.data.area = d.parent.data.area; }); // Propagate areas

    partition(root);

    var arc = d3.arc()
        .startAngle(function (d) { return d.x0 + Math.PI })
        .endAngle(function (d) { return d.x1 + Math.PI })
        .innerRadius(function (d) { return d.y0 })
        .outerRadius(function (d) { return d.y1 });

    var showGrid = false;

    var updateStroke = function() {
        g.selectAll('path').style('stroke', function(d) { return showGrid ? "#fff" : getLocalityData(d.data.area).color; });
    };

    g.selectAll('path')
        .data(root.descendants())
        .enter().append('path')
        .attr("display", function (d) { return d.depth ? null : "none"; })
        .attr("d", arc)
        .style("fill", function (d) { return getLocalityData(d.data.area).color; })
        .on("mouseover", function(d) {
            g.selectAll('path').attr("class", function (d1) {
                return d1.data.area === d.data.area || d.data.area === "?" ? "selected" : "unselected";
            });
            sosatip.text("Sosa " + d.data.sosa);
            areatip.text(getLocalityData(d.data.area).name);
        })
        .on("mouseout", function () {
            g.selectAll('path').attr("class", "selected");
            sosatip.text("");
            areatip.text("");
        })
        .on("mousemove", function () {
            var pos = d3.mouse(g.node());
            var offsetY = -20;
            areatip
                .attr("x", pos[0])
                .attr("y", pos[1] + offsetY);
        })
        .on("mousedown", function() {
            showGrid = !showGrid;
            updateStroke();
        });

    updateStroke();

    var sosatip = g.append("text")
        .attr("dominant-baseline", "central")
        .attr("text-anchor", "middle")
        .attr("font-weight", "bold");

    var areatip = g.append("text")
        .attr("class", "areatip")
        .attr("dominant-baseline", "central")
        .attr("text-anchor", "middle")
        .style("pointer-events", "none");
};

var generateBubbles = function(data) {

    var scale = 0.8;
    var size = getCanvasSize(scale);
    var width = size, height = size;

    var colorByRank = function (rank) {
        var c = Math.max(Math.min(Math.floor(Math.log(rank + 1) * 20 - 30), 255), 0);
        return "rgb(" + c + "," + c + "," + c + ")";
    };

    var g = d3.select('#bubbles')
        .append('svg')
        .attr('width', width)
        .attr('height', height)
        .style('overflow', 'visible');

    var color = d3.scaleOrdinal(d3.schemeCategory20c);

    var bubble = d3.pack(data)
        .size([width, height])
        .padding(1.5);

    var nodes = d3.hierarchy(data)
        .sum(function (d) {
            return d.weight;
        });

    var node = g.selectAll(".node")
        .data(bubble(nodes).descendants())
        .enter()
        .filter(function (d) {
            return !d.children
        })
        .append("g")
        .attr("class", "node")
        .attr("transform", function (d) {
            return "translate(" + d.x + "," + d.y + ")";
        });

    node.append("circle")
        .attr("r", function (d) {
            return d.r;
        })
        .style("fill", function (d) {
            return colorByRank(d.data.rank ? d.data.rank : 10000);
        })
        .on("mouseover", function (d) {
            tip.attr("class", "tooltip-visible")
                .text(d.data.rank ? "Rang n°" + d.data.rank + " en France" : "Aucune information disponible")
                .attr("transform", function () {
                    return "translate(" + (d.x - tip.attr("width") / 2) + "," + (d.y - tip.attr("height") - d.r - 20) + ")";
                });
            node.attr("class", function (d1) {
                return d1 === d ? "bubble-selected" : "bubble-unselected";
            });
        })
        .on("mouseout", function () {
            tip.attr("class", "tooltip-hidden");
            node.attr("class", "bubble-normal");
        });

    node.append("text")
        .attr("dy", ".2em")
        .style("text-anchor", "middle")
        .text(function (d) {
            return d.data.surname.toUpperCase();
        })
        .attr("font-family", "sans-serif")
        .attr("font-size", function (d) {
            return d.r / 3;
        })
        .attr("fill", "white")
        .style("stroke-width", 0)
        .style("pointer-events", "none")
        .attr("font-weight", "bold");

    var tip = g.append("text")
        .attr("id", "nametip")
        .attr("dy", ".2em")
        .style("text-anchor", "middle")
        .attr("font-family", "sans-serif")
        .attr("font-size", 30)
        .attr("fill", "black")
        .style("pointer-events", "none");

};

$.getJSON("../data/genealogy_data.json", function(data) {

    var hierarchyData = data.hierarchy, surnamesData = data.surnames;

    generateHierarchy(hierarchyData);
    generateBubbles({"children": surnamesData});

});


