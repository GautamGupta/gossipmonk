jQuery( document ).ready(function( $ ) { 
    /* Adblock Notice http://www.labnol.org/internet/alternate-content-for-adblock/28602/ */
    var ad = $("ins.adsbygoogle");
    if (ad && ad.html().replace(/\s/g, "").length == 0) {
        ad.attr('style', 'display: block !important;'); 
        ad.html('<a class="aye-haye-adblock" rel="nofollow" href="http://gossipmonk.com/whitelist-gossipmonk-adblock/"> <h2 class="savaal">Using AdBlock?</h2> <h1 class="bheekh">Online Ads Help Pay Our Bills</h1> <h2 class="daya-karo">Please whitelist *.gossipmonk<wbr>.com.<span class="wut">?</span></h2></div>');
    }

    /* Fixed sidebar */
    var $sidebar = $('#sidebar');
    var $footer  = $('body.single .et_pb_blog_grid_wrapper') ? $('body.single .et_pb_blog_grid_wrapper') : $('#main-footer');
    var top      = $sidebar.offset().top - parseFloat($sidebar.css('marginTop').replace(/auto/, 0));
    var footTop  = $footer.offset().top - parseFloat($footer.css('marginTop').replace(/auto/, 0));
    var posLeft  = parseInt($sidebar.offset().left);
    var maxY     = footTop - $sidebar.outerHeight();

    $(window).scroll(function() {
        if ($(window).width() < 980) {
            // Restore sidebar if it was fixed
            $("#sidebar").css({
                "position": "relative",
                "left": 0,
                "top": 0
            });
        } else {
            /* var pos       = parseInt($(window).scrollTop()),
                winheight = parseInt($(window).height()),
                posleft   = parseInt($("#sidebar").offset().left), 
                height    = parseInt($("#sidebar").outerHeight()),
                origpos   = parseInt($("#content-area").offset().top);

            if (pos > (height + origpos) - winheight - 60) {
                $("#sidebar").css({
                    "position": "fixed",
                    "left": posleft,
                    "top": -(height - winheight + 10)
            });
            } else {
                $("#sidebar").css({
                    "position": "relative",
                    "left": 0,
                    "top": 0
                });
            } */

            var y = $(this).scrollTop();
            if (y > top) {
                if (y < maxY) {
                    $sidebar.addClass('fixed').removeAttr('style');
                } else {
                    $sidebar.removeClass('fixed').css({
                        position: 'absolute',
                        top: (maxY - top) + 'px',
                        left: posLeft + 'px'
                    });
                }
            } else {
                $sidebar.removeClass('fixed');
            }
        }
    });
});

/* Infinite Scroll + Masonry + ImagesLoaded */
(function($, undefined) {
    var $container = $('.et_pb_blog_grid');

    $container.imagesLoaded(function(){
        $container.masonry();
    });

    // Infinite Scroll if we've the required width
    if ($(window).width() >= 980) {
        $container.infinitescroll(
            {
                // selector for the paged navigation (it will be hidden)
                navSelector  : ".et_pb_blog_grid_wrapper .wp-pagenavi",
                // selector for the NEXT link (to page 2)
                nextSelector : ".et_pb_blog_grid_wrapper .wp-pagenavi .nextpostslink",
                // selector for all items you'll retrieve
                itemSelector : "article.post",

                loading : {
                    msgText : "<em style='padding-left:5px;'>Loading...</em>",
                    finishedMsg : "<em>That's all we've for now!</em>"
                }
            },

            // Trigger Masonry as a callback
            function( newElements ) {
                // hide new items while they are loading
                var $newElems = $( newElements ).css({ opacity: 0 });
                // ensure that images load before adding to masonry layout
                $newElems.imagesLoaded(function(){
                    // show elems now they're ready
                    $newElems.animate({ opacity: 1 });
                    $container.masonry( 'appended', $newElems, true );
                });
            }
        );
    }
})(jQuery);
