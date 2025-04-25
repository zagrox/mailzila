<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Change the application language
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeLanguage(Request $request)
    {
        $validated = $request->validate([
            'language' => 'required|string|size:2',
        ]);

        $lang = $validated['language'];
        
        // Set locale
        App::setLocale($lang);
        Session::put('locale', $lang);
        
        // If it's Persian, set RTL
        if ($lang === 'fa') {
            Session::put('direction', 'rtl');
        } else {
            Session::put('direction', 'ltr');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Language changed successfully',
            'lang' => $lang,
            'direction' => Session::get('direction', 'ltr')
        ]);
    }
} 