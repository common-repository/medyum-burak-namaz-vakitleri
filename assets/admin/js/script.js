jQuery(document).ready(function($) {
    $('.color_field').each(function() {
        $(this).wpColorPicker({ palettes: true, hide: false });
    });
});