<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;

class LanguageController extends Controller
{
    /**
     * Display the language management page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $languages = $this->getAvailableLanguages();
        $languageFiles = $this->getLanguageFiles();
        
        return view('admin.language.index', compact('languages', 'languageFiles'));
    }
    
    /**
     * Change the application language.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeLanguage(Request $request)
    {
        $locale = $request->locale;
        
        // Make sure the locale is valid
        if (in_array($locale, $this->getAvailableLanguages())) {
            App::setLocale($locale);
            Session::put('locale', $locale);
            
            // Set RTL direction for certain languages
            $rtlLocales = ['fa', 'ar', 'he', 'ur'];
            Session::put('rtl', in_array($locale, $rtlLocales));
        }
        
        return redirect()->back();
    }
    
    /**
     * Display the language editor for a specific language file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Request $request)
    {
        $lang = $request->lang ?? 'en';
        $file = $request->file ?? 'auth';
        
        // Get all translations from the file
        $path = lang_path($lang.'/'.$file.'.php');
        
        if (!File::exists($path)) {
            return redirect()->route('admin.language.index')->with('error', 'Language file not found.');
        }
        
        $translations = require $path;
        
        return view('admin.language.edit', compact('translations', 'lang', 'file'));
    }
    
    /**
     * Update translations for a language file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $lang = $request->lang;
        $file = $request->file;
        $translations = $request->translations;
        
        $path = lang_path($lang.'/'.$file.'.php');
        
        if (!File::exists($path)) {
            return redirect()->route('admin.language.index')->with('error', 'Language file not found.');
        }
        
        // Build the PHP file content
        $content = "<?php\n\nreturn [\n";
        
        foreach ($translations as $key => $value) {
            $content .= "    '".$key."' => '".addslashes($value)."',\n";
        }
        
        $content .= "];\n";
        
        // Write the file
        File::put($path, $content);
        
        return redirect()->route('admin.language.edit', ['lang' => $lang, 'file' => $file])
            ->with('success', 'Translations updated successfully.');
    }
    
    /**
     * Auto translate missing strings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function autoTranslate(Request $request)
    {
        $sourceLang = $request->source_lang ?? 'en';
        $targetLang = $request->target_lang;
        $file = $request->file;
        
        // This is a placeholder for actual translation API integration
        // In a real implementation, you would use a translation service like Google Translate API
        
        return redirect()->route('admin.language.edit', ['lang' => $targetLang, 'file' => $file])
            ->with('info', 'Auto-translation feature requires API integration.');
    }
    
    /**
     * Get the list of available languages in the application.
     *
     * @return array
     */
    private function getAvailableLanguages()
    {
        $langDirs = File::directories(lang_path());
        
        $languages = [];
        foreach ($langDirs as $dir) {
            $languages[] = basename($dir);
        }
        
        return $languages;
    }
    
    /**
     * Get the list of language files.
     *
     * @param  string  $lang
     * @return array
     */
    private function getLanguageFiles($lang = 'en')
    {
        $files = File::files(lang_path($lang));
        
        $languageFiles = [];
        foreach ($files as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $languageFiles[] = $filename;
        }
        
        return $languageFiles;
    }
} 