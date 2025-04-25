/**
 * Language Switcher
 * This script handles the language switching and persists user preferences
 */
$(function () {
    // Check for saved language preference or use browser default
    const currentLang = localStorage.getItem('language') || 'en';
    const currentDir = localStorage.getItem('direction') || 'ltr';
    
    // Function to apply language settings
    const applyLanguage = (lang) => {
        // Store language preference
        localStorage.setItem('language', lang);
        
        // Apply RTL for Persian language 
        if (lang === 'fa') {
            $('body').addClass('rtl');
            // Add RTL stylesheet
            if (!$('link[href*="rtl.css"]').length) {
                $('head').append('<link rel="stylesheet" href="/css/rtl.css">');
            }
            // Set direction attribute
            $('html').attr('dir', 'rtl');
            localStorage.setItem('direction', 'rtl');
        } else {
            $('body').removeClass('rtl');
            // Remove RTL stylesheet if exists
            $('link[href*="rtl.css"]').remove();
            // Set direction attribute
            $('html').attr('dir', 'ltr');
            localStorage.setItem('direction', 'ltr');
        }
        
        // Update language flag icon in dropdown
        const $langDropdown = $('.language-dropdown');
        if ($langDropdown.length) {
            // Update active class on language items
            $langDropdown.find('.dropdown-item').removeClass('active');
            $langDropdown.find(`.dropdown-item[data-lang="${lang}"]`).addClass('active');
            
            // Update dropdown button flag
            const flagClass = getLangFlagClass(lang);
            const flagName = getLangName(lang);
            $langDropdown.find('.dropdown-toggle .flag-icon').removeClass().addClass('flag-icon ' + flagClass);
            $langDropdown.find('.dropdown-toggle .lang-name').text(flagName);
        }
    };
    
    // Helper function to get flag class based on language code
    const getLangFlagClass = (langCode) => {
        const flagMap = {
            'en': 'flag-icon-us',
            'fa': 'flag-icon-ir',
            'ar': 'flag-icon-sa',
            'de': 'flag-icon-de',
            'es': 'flag-icon-es',
            'fr': 'flag-icon-fr',
            'it': 'flag-icon-it',
            'ru': 'flag-icon-ru'
        };
        return flagMap[langCode] || 'flag-icon-us';
    };
    
    // Helper function to get language name based on language code
    const getLangName = (langCode) => {
        const langMap = {
            'en': 'English',
            'fa': 'فارسی',
            'ar': 'العربية',
            'de': 'Deutsch',
            'es': 'Español',
            'fr': 'Français',
            'it': 'Italiano',
            'ru': 'Русский'
        };
        return langMap[langCode] || 'English';
    };
    
    // Apply saved language settings on page load
    applyLanguage(currentLang);
    
    // Handle language selector click
    $(document).on('click', '.language-dropdown .dropdown-item', function(e) {
        e.preventDefault();
        const lang = $(this).data('lang');
        
        // Send AJAX request to change language
        $.ajax({
            url: '/change-language',
            type: 'POST',
            data: {
                lang: lang,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Apply language change
                    applyLanguage(lang);
                    // Reload page to update content
                    window.location.reload();
                }
            },
            error: function(xhr) {
                console.error('Error changing language:', xhr.responseText);
            }
        });
    });
}); 