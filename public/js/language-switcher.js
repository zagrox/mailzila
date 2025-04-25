/**
 * Language Switcher
 * This script handles the language switching and persists user preferences
 */
$(document).ready(function() {
    // Check if there's a saved language preference
    const savedLang = localStorage.getItem('mailzila_language') || 'en';
    const savedDirection = localStorage.getItem('mailzila_direction') || 'ltr';
    
    // Apply initial language settings
    updateLanguageUI(savedLang);
    applyDirection(savedDirection);
    
    // Handle clicks on language dropdown items
    $(document).on('click', '#language-dropdown .dropdown-item', function(e) {
        e.preventDefault();
        
        const langCode = $(this).data('lang');
        if (langCode) {
            changeLanguage(langCode);
        }
    });
    
    // Function to change language
    function changeLanguage(langCode) {
        // Send AJAX request to change language on server
        $.ajax({
            url: '/change-language',
            type: 'POST',
            data: {
                language: langCode,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Save language preference to local storage
                    localStorage.setItem('mailzila_language', langCode);
                    localStorage.setItem('mailzila_direction', response.direction);
                    
                    // Update UI with new language settings
                    updateLanguageUI(langCode);
                    applyDirection(response.direction);
                    
                    // Reload the page to apply changes
                    location.reload();
                }
            },
            error: function() {
                console.error('Failed to change language');
            }
        });
    }
    
    // Function to update language UI
    function updateLanguageUI(langCode) {
        // Update the language dropdown icon and text
        let flagClass, langText;
        
        switch(langCode) {
            case 'fa':
                flagClass = 'flag-icon-ir';
                langText = 'فارسی';
                break;
            case 'en':
            default:
                flagClass = 'flag-icon-us';
                langText = 'English';
                break;
        }
        
        // Update dropdown button
        const $dropdown = $('#language-dropdown');
        $dropdown.find('> a > i').removeClass().addClass('flag-icon ' + flagClass);
        
        // Highlight current language in dropdown
        $dropdown.find('.dropdown-item').removeClass('active');
        $dropdown.find('.dropdown-item[data-lang="' + langCode + '"]').addClass('active');
    }
    
    // Function to apply RTL/LTR direction
    function applyDirection(direction) {
        if (direction === 'rtl') {
            $('body').addClass('rtl');
            $('html').attr('dir', 'rtl');
            if (!$('link[href="/css/rtl.css"]').length) {
                $('head').append('<link rel="stylesheet" href="/css/rtl.css">');
            }
        } else {
            $('body').removeClass('rtl');
            $('html').attr('dir', 'ltr');
            $('link[href="/css/rtl.css"]').remove();
        }
    }
}); 