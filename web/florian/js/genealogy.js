Math.seedrandom(42); // "A delicate pinch of determinism"

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
    var radius = 0.9 * Math.min(width, height) / 2;

    var localityData = {
        "04": {"name": "Alpes-de-Haute-Provence", "color": "#ff4252"},
        "08": {"name": "Ardennes", "color": "#edadd5"},
        "12": {"name": "Aveyron", "color": "#56f107"},
        "13": {"name": "Bouches-du-Rhône", "color": "#ff9514"},
        "15": {"name": "Cantal", "color": "#47f564"},
        "26": {"name": "Drôme", "color": "#e69747"},
        "28": {"name": "Eure-et-Loir", "color": "#e2ae89"},
        "30": {"name": "Gard", "color": "#ffc532"},
        "34": {"name": "Hérault", "color": "#ffa63c"},
        "35": {"name": "Ille-et-Vilaine", "color": "#0075ff"},
        "39": {"name": "Jura", "color": "#2bffb5"},
        "45": {"name": "Loiret", "color": "#bdbc54"},
        "46": {"name": "Lot", "color": "#2ad010"},
        "49": {"name": "Maine-et-Loire", "color": "#373dff"},
        "50": {"name": "Manche", "color": "#549dbf"},
        "53": {"name": "Mayenne", "color": "#3b2dbd"},
        "54": {"name": "Meurthe-et-Moselle", "color": "#f8f400"},
        "57": {"name": "Moselle", "color": "#ffde09"},
        "67": {"name": "Bas-Rhin", "color": "#d3d669"},
        "70": {"name": "Haute-Saône", "color": "#ceff97"},
        "75": {"name": "Paris", "color": "#49deff"},
        "88": {"name": "Vosges", "color": "#d1fd02"},
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

    var m = d3.select('#map');

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

    var fixAngle = function(d) {
        if(d.parent && d.parent.children.length === 1 && d.data.sex === "F")
            return 2 * Math.PI / (1 << d.depth);
        else
            return 0.0; // Already correct
    };

    var arc = d3.arc()
        .startAngle(function (d) { return d.x0 + Math.PI + fixAngle(d) })
        .endAngle(function (d) { return d.x1 + Math.PI + fixAngle(d) })
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
            var s = d.data.sosa;
            var sosa;
            if(s === 2)
                sosa = "Père";
            else if(s === 3)
                sosa = "Mère";
            else if(s < 8) {
                sosa = "G. " + (s % 2 === 0 ? "Père" : "Mère") + " " + (s < 6 ? "P." : "M.");
            } else
                sosa = "Sosa " + d.data.sosa;
            sosatip.text(sosa);
            areatip.text(getLocalityData(d.data.area).name);

            m.selectAll("path").attr("class", "unselected");
            m.selectAll("path#departement" + d.data.area).attr("class", "selected");
        })
        .on("mouseout", function () {
            g.selectAll('path').attr("class", "selected");
            m.selectAll('path').attr("class", "selected");
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
            //showGrid = !showGrid;
            //updateStroke();
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

    // Map

    d3.xml('../img/genealogy/departements.svg', function (error, data) {

        $('#map').append(data.documentElement);
        m.select("svg").attr("width", width).attr("height", width * 0.3);

        for(key in localityData) {
            if(key !== "?") {
                m.selectAll("path#departement" + key)
                    .attr("fill", localityData[key].color);
            }
        }
    });



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

var generateCelebrities = function(data) {
    var scale = 0.8;
    var size = getCanvasSize(scale);
    var width = size, height = size;

    var maxDistance = 0;
    for(var i = 0; i < data.children.length; i++) {
        maxDistance = Math.max(data.children[i].distance, maxDistance);
    }
    maxDistance++;

    var g = d3.select('#celebrities')
        .append('svg')
        .attr('width', width)
        .attr('height', height)
        .style('overflow', 'visible');

    var bubble = d3.pack(data)
        .size([width, height])
        .padding(1.5);

    var nodes = d3.hierarchy(data)
        .sum(function (d) {
            return maxDistance - d.distance;
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

    var getId = function(name) {
        return name.trim().replace(/\s/g, "-").toLowerCase();
    };

    node.append("circle")
        .attr("r", function (d) {
            return d.r;
        })
        // Add image
        .on("mouseover", function (d) {
            tip.attr("class", "tooltip-visible")
                .text(d.data.display_name + ", " + d.data.description.toLowerCase())
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
        })
        .style("fill", function(d) { return d.data.image_url ? ("url(#" + getId(d.data.name) + "-icon)") : "#aaaaaa";})
        .on("mousedown", function(d) {
            if(d.data.url)
                window.open(d.data.url, '_blank');
        });

    node.filter(function (d) { return d.data.image_url; })
        .append('defs')
        .append('pattern')
        .attr('id', function(d) { return (getId(d.data.name) + "-icon");})
        .attr('width', 1)
        .attr('height', 1)
        .attr('patternContentUnits', 'objectBoundingBox')
        .append("svg:image")
        .attr("xlink:xlink:href", function(d) { return (d.data.image_url);})
        .attr("height", 1)
        .attr("width", 1)
        .attr("preserveAspectRatio", "xMinYMin slice");

    node.filter(function (d) { return !d.data.image_url; })
        .append("text")
        .attr("dy", ".2em")
        .style("text-anchor", "middle")
        .text(function (d) {
            return d.data.display_name;
        })
        .attr("font-family", "sans-serif")
        .attr("font-size", function (d) {
            return d.r / 6;
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

    var hierarchyData = data.hierarchy, surnamesData = data.surnames, celebritiesData = data.celebrities;

    generateHierarchy(hierarchyData);
    generateBubbles({"children": surnamesData});
    generateCelebrities({"children": celebritiesData});

});




