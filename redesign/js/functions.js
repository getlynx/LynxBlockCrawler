$(window).on("load", function() {

    //load_reddit();

    mobile_menu_init();

});

function load_reddit() {
    console.log("Getting LYNX Reddit RSS...");
    rssurl = "https://www.reddit.com/r/lynx/hot.rss";
    $.get(rssurl, function(data) {
        var $json = xml2json(data);
        var html = []
        console.log($json);
        
        $.each($json.feed.entry, function(index, element) {
            var $this = (element),
            item = {
                link: $this.link['@attributes']['href'],
                updated: $this.updated['#text'],
                title: $this.title['#text'],
                author_name: $this.author['name']['#text'],
                author_uri: $this.author['uri']['#text'],
                content: $this.content['#text']
            }
            html.push("<div>");
            html.push("<strong><a href=\""+item.link+"\">"+item.title+"</a></strong><br/>");
            html.push(item.updated);
            html.push("<br/>...");
            html.push("</div>");
        });
        html = html.join("");

        $("#feed_reddit .feed-box").html(html);

    });
};


function mobile_menu_init() {
    $("#mobile_menu_btn").click(function() {
        if ($("#mobile_menu").css("display") != "none") {
            $("#mobile_menu_btn").html("&#9660; Open Menu &#9660;")
            $("#mobile_menu").slideToggle();
        } else {
            $("#mobile_menu").slideToggle();
            $("#mobile_menu_btn").html("&#9650; Close Menu &#9650;")
        }
    });
}