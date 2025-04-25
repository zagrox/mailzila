/**
 * AdminLTE Dark Mode
 * This script handles the dark mode toggle and persists user preferences
 */
$(function () {
    // Check for saved theme preference or use dark mode if OS prefers it
    const currentTheme = localStorage.getItem('theme') || 
        (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    
    // Function to apply theme
    const applyTheme = (theme) => {
        if (theme === 'dark') {
            // Add dark mode to body
            $('body').addClass('dark-mode');
            
            // Update navbar
            $('.main-header').addClass('navbar-dark').removeClass('navbar-light navbar-white');
            
            // Update sidebar
            $('.main-sidebar').addClass('sidebar-dark-primary').removeClass('sidebar-light-primary');
            
            // Update sidebar menu labels
            $('.nav-sidebar .nav-link p').addClass('text-light');
            $('.nav-header').addClass('text-light');
            $('.brand-text').addClass('text-light');
            
            // Update cards and other elements
            $('.card').addClass('card-dark');
            $('.card-header').addClass('bg-dark');
            $('.card-title').addClass('text-light');
            
            localStorage.setItem('theme', 'dark');
        } else {
            // Remove dark mode from body
            $('body').removeClass('dark-mode');
            
            // Update navbar
            $('.main-header').removeClass('navbar-dark').addClass('navbar-light navbar-white');
            
            // Update sidebar
            $('.main-sidebar').removeClass('sidebar-dark-primary').addClass('sidebar-light-primary');
            
            // Update sidebar menu labels
            $('.nav-sidebar .nav-link p').removeClass('text-light');
            $('.nav-header').removeClass('text-light');
            $('.brand-text').removeClass('text-light');
            
            // Update cards and other elements
            $('.card').removeClass('card-dark');
            $('.card-header').removeClass('bg-dark');
            $('.card-title').removeClass('text-light');
            
            localStorage.setItem('theme', 'light');
        }
    };
    
    // Apply saved theme on page load
    applyTheme(currentTheme);
    
    // Handle dark mode toggle click
    $(document).on('click', '.nav-item.dropdown-dark-mode a, [data-widget="dark-mode"]', function(e) {
        e.preventDefault();
        const newTheme = $('body').hasClass('dark-mode') ? 'light' : 'dark';
        applyTheme(newTheme);
    });
}); 