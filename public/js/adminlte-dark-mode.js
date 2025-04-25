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
            $('body').addClass('dark-mode');
            $('.main-header').addClass('navbar-dark').removeClass('navbar-light');
            $('.main-sidebar').addClass('sidebar-dark-primary').removeClass('sidebar-light-primary');
            localStorage.setItem('theme', 'dark');
        } else {
            $('body').removeClass('dark-mode');
            $('.main-header').removeClass('navbar-dark').addClass('navbar-light');
            $('.main-sidebar').removeClass('sidebar-dark-primary').addClass('sidebar-light-primary');
            localStorage.setItem('theme', 'light');
        }
    };
    
    // Apply saved theme on page load
    applyTheme(currentTheme);
    
    // Handle dark mode toggle click
    $(document).on('click', '.nav-item.dropdown-dark-mode a', function(e) {
        e.preventDefault();
        const newTheme = $('body').hasClass('dark-mode') ? 'light' : 'dark';
        applyTheme(newTheme);
    });
}); 