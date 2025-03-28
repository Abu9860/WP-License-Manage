jQuery(document).ready(function($) {
    // Hide all tab content initially except first
    $('.tab-content').not(':first').hide();

    // Tab switching functionality
    $('.nav-tab').click(function(e) {
        e.preventDefault();
        var target = $(this).attr('href');
        
        // Update active tab
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Show selected content
        $('.tab-content').hide();
        $(target).show();
    });
});
