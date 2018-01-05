(function ($) {
    $(document).ready(function($) {

        function init() {
            $('div.toc ul li span.section')
                .css('cursor', function(){
                    $(this).css("cursor","pointer");
                })
                .click(function(){
                    $(this).parent().find('ul').toggle();
                });
        }

        init();
    });
})(jQuery);
