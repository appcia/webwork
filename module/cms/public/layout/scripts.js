$(document).ready(function () {
    // Globals
    $('.datepicker').datepicker();
    $('.wysiwyg-full').wysihtml5();
    $('.wysiwyg-mini').wysihtml5({
        "font-styles": false,
        "emphasis": true,
        "lists": true,
        "html": false,
        "image": false
    });
    $('.fancybox').fancybox({
        openEffect: 'elastic',
        closeEffect: 'elastic'
    });

    // Form support
    $('form input[type!=checkbox][type!=radio], form textarea').first().focus();
});