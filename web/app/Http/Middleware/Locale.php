<?php

namespace App\Http\Middleware;
use App\Models\Language;

use Closure;

class Locale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $supportedLanguages = Language::pluck('locale')->toArray();
        if($request->header('Content-Language')){
            $locale = $request->header('Content-Language');
            if(!in_array($locale, $supportedLanguages)){
                $locale = 'en';
            }
            app()->setLocale($locale);
        }
        return $next($request);
    }
}
