$(window).on("load", function() {

    //load_reddit();

    mobile_menu_init();

});

function feed_website() {
    $.ajax({
    url: 'https://www.rssdog.com/index.php?url=https%3A%2F%2Fgetlynx.io%2Ffeed%2F&amp;mode=javascript&amp;showonly=&amp;maxitems=0&amp;showdescs=1&amp;desctrim=1&amp;descmax=0&amp;tabwidth=100%25&amp;excltitle=1&amp;showdate=1&amp;nofollow=1&amp;utf8=1&amp;linktarget=_blank&amp;textsize=small&amp;bordercol=transparent&amp;headbgcol=transparent&amp;headtxtcol=%23ffffff&amp;titlebgcol=transparent&amp;titletxtcol=%23ffffff&amp;itembgcol=transparent&amp;itemtxtcol=%23336699&amp;ctl=0',
    success: function(data) {
        $('#feed_website .feed-box').html(data);
    }
    });
}

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