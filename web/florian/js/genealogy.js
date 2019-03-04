$.getJSON("../data/genealogy_data.json", function(nodeData) {

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
    var maxDepth = getMaxDepth(nodeData);

    var size = 800;
    var width = size;
    var height = size;
    var radius = Math.min(width, height) / 2;

    var colorMap = {
        "12": "#56f107", // Aveyron
        "13": "#ff9514", // Marseille
        "15": "#47f564", // Cantal
        "28": "#d8e1e3", // Eure-et-Loir
        "34": "#ffc532", // Hérault
        "39": "#d1fd02", // Jura
        "45": "#bdbc54", // Loiret
        "46": "#2ad010", // Lot
        "49": "#373dff", // Maine-et-Loire
        "50": "#c9ffe3", // Manche
        "54": "#f8f400", // Meurthe-et-Moselle
        "57": "#ffe80d", // Moselle
        "75": "#49deff", // Paris
        "89": "#c86363", // Yonne
        "94": "#24c2e5", // Val-de-Marne

        "lu": "#ff28cb", // Luxembourg

        "?": "#f4f5f7", // Unknown
        "other": "#f4f5f7" // Other
    };

    var getColor = function (code) {
        if(colorMap[code])
            return colorMap[code];
        else
            return colorMap.other;
    };

    // Root SVG element
    var g = d3.select('#wheel')
        .append('svg')
        .attr('width', width)
        .attr('height', height)
        .append('g')
        .attr('transform', 'translate(' + width / 2 + ',' + height / 2 + ')');

    var partition = d3.partition()
        .size([2 * Math.PI, radius]);

    nodeData.sosa = 1;
    var root = d3.hierarchy(nodeData)
        .each(function (d) { d.value = 1 << (maxDepth - d.depth); })
        .sort(function (a, b) {
            return a.data.sex === "M" ? -1 : 1;
        })
        .each(function(d) { if(!d.data.sosa) d.data.sosa = d.parent.data.sosa * 2; });

    partition(root);

    var arc = d3.arc()
        .startAngle(function (d) { return d.x0 + Math.PI })
        .endAngle(function (d) { return d.x1 + Math.PI })
        .innerRadius(function (d) { return d.y0 })
        .outerRadius(function (d) { return d.y1 });

    g.selectAll('path')
        .data(root.descendants())
        .enter().append('path')
        .attr("display", function (d) { return d.depth ? null : "none"; })
        .attr("d", arc)
        .style('stroke', '#fff')
        .style("fill", function (d) { return getColor(d.data.area); })
        .on("mouseover", function(d, i) {
            g.selectAll('path').attr("class", function (d1) {
                return d1.data.area === d.data.area || d.data.area === "?" ? "selected" : "unselected";
            })
        })
        .on("mouseout", function (d, i) {
            g.selectAll('path').attr("class", function (d1) { return "selected"; })
        });

});


