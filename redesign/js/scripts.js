(function() {
    $(function() {
        var n, e, t;
        return n = function() {
            setTimeout(function() {
                return $("body").removeClass("loading")
            }, 1e3)
        }, 
        $(window).on("load", function() {
            return window.innerWidth > 620 ? n() : $("body").removeClass("loading")
        })
    })
}).call(this);