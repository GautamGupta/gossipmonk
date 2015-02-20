// Yoast uses __gaTracker for some reason, while general GA code uses ga
ga = __gaTracker;

jQuery(document).ready(function($) {
    /* Adblock Notice http://www.labnol.org/internet/alternate-content-for-adblock/28602/ */
    var ad = $("ins.adsbygoogle");
    if (ad && ad.html().replace(/\s/g, "").length == 0) {
        ad.attr('style', 'display: block !important;');
        ad.html('<a class="aye-haye-adblock" rel="nofollow" href="http://gossipmonk.com/whitelist-gossipmonk-adblock/"> <h2 class="savaal">Using AdBlock?</h2> <h1 class="bheekh">Online Ads Help Pay Our Bills</h1> <h2 class="daya-karo">Please whitelist *.gossipmonk<wbr>.com.<span class="wut">?</span></h2></div>');
        /* ga('send', 'event', 'adblock', 'blocked', 'true');
    } else {
        ga('send', 'event', 'adblock', 'unblocked', 'false'); */
    }

    /* Fixed sidebar */
    var $sidebar = $('#sidebar-inside');
    if ($sidebar.length > 0) {
        var $footer  = $('body.single .et_pb_blog_grid_wrapper').length > 0 ? $('body.single .et_pb_blog_grid_wrapper') : $('#main-footer');
        var ad_height = $('#sidebar-ad').length > 0 ? $sidebar.offset().top : 0;

        function restore_sidebar() {
            $sidebar.css({
                position: 'relative',
                left: 0,
                top: 0,
                bottom: 'auto'
            });
        }

        $(window).resize(function() {
            restore_sidebar();
            $(window).scroll();
        });

        $(window).scroll(function() {
            if ($(window).width() <= 980 || $('#content-area').outerHeight() <= $('#sidebar').outerHeight()) {
                // Restore sidebar if it was fixed on small screen
                restore_sidebar();
            } else {
                var pos       = parseInt($(window).scrollTop()),
                    winheight = parseInt($(window).height()),
                    margin    = parseInt($('.et_pb_widget').css('marginBottom')),
                    height    = parseInt($sidebar.outerHeight()) + margin,
                    origpos   = parseInt($('#sidebar-ad').length > 0 ? $('#sidebar-ad').offset().top + $('#sidebar-ad').outerHeight() : $("#content-area").offset().top),
                    footTop   = parseInt($footer.offset().top),
                    posleft   = parseInt($sidebar.offset().left);

                if (pos > (height + margin + origpos) - winheight) {
                    $sidebar.css({
                        'position': 'fixed',
                        'left': posleft
                    });

                    if (pos + winheight >= footTop)
                        $sidebar.css({'top': 'auto', 'bottom' : winheight - (footTop - pos) + margin});
                    else
                        $sidebar.css({'top' : (winheight - height), 'bottom': 'auto'});
                } else {
                    restore_sidebar();
                }
            }
        });
    } // if sidebar
});

/* Infinite Scroll + Masonry + ImagesLoaded */
(function($, undefined) {
    var $container = $('.et_pb_blog_grid');

    $container.imagesLoaded(function(){
        $container.masonry();
        // Set opacity to 0.6 of playbuttons, otherwise they're at top due to position relative
        $('.playbutton').show();
    });

    // Infinite Scroll if we've the required width
    $container.infinitescroll(
        {
            // selector for the paged navigation (it will be hidden)
            navSelector  : ".et_pb_blog_grid_wrapper .pagination",
            // selector for the NEXT link (to page 2)
            nextSelector : ".et_pb_blog_grid_wrapper .pagination .alignleft a",
            // selector for all items you'll retrieve
            itemSelector : ".et_pb_blog_grid_wrapper article.post",
            bufferPx: 640,
            loading : {
                msgText : "<em style='padding-left:5px;'>Loading...</em>",
                finishedMsg : "<em>That's all we've for now!</em>"
            }
        },

        // Trigger Masonry as a callback
        function( newElements, opts ) {
            $('.infscr-loading').show();
            $('.infscr-loading img').show();
            // hide new items while they are loading
            var $newElems = $( newElements ).css({ opacity: 0 });
            // ensure that images load before adding to masonry layout
            $newElems.imagesLoaded(function(){
                // Set opacity to 0.6 of playbuttons, otherwise they're at top due to posi$
                $('.playbutton').show();

                // show elems now they're ready
                $newElems.animate({ opacity: 1 }, 50);
                $container.masonry( 'appended', $newElems, true );

                $('.infscr-loading').hide();
                $('.infscr-loading img').hide();
            });

            // Log infinite scroll events in GA
            ga('send', { 'hitType': 'event', 'eventCategory': 'Scroll Depth', 'eventAction': 'Infinite Scroll', 'eventLabel': 'Page', 'eventValue': opts.state.currPage, 'nonInteraction': 1});
        }
    );

    // Logging amount scrolled
    $.scrollDepth({
        elements: ['body.single .et_pb_blog_grid_wrapper'],
    });

    if ($(window).width() <= 980) {
        // Pause Infinite Scroll on small screen
        $container.infinitescroll('pause');

        // Resume Infinite Scroll
        $('.et_pb_blog_grid_wrapper .pagination .alignleft a').click(function(){
            $container.infinitescroll('resume');
            $container.infinitescroll('retrieve');
            return false;
        });
    }
})(jQuery);
