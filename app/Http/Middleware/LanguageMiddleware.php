<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if there's a language set in the session
        if (Session::has('locale')) {
            $locale = Session::get('locale');
            App::setLocale($locale);
            
            // Set RTL direction for certain languages
            $rtlLocales = ['fa', 'ar', 'he', 'ur'];
            Session::put('rtl', in_array($locale, $rtlLocales));
        } else {
            // Set default language
            $locale = config('app.locale');
            App::setLocale($locale);
            Session::put('locale', $locale);
            
            // Set RTL direction for certain languages
            $rtlLocales = ['fa', 'ar', 'he', 'ur'];
            Session::put('rtl', in_array($locale, $rtlLocales));
        }
        
        return $next($request);
    }
} 