<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    public function autocomplete(Request $request)
    {
        $apiKey = $request->get('api_key');
        $company = $request->get('api_company');
        $apiBase = $request->getSchemeAndHttpHost();
        return view('company.widget_autocomplete', compact('apiBase', 'apiKey', 'company'));
    }

    public function autocompleteJs(Request $request)
    {
        $apiBase = $request->getSchemeAndHttpHost();
        $content = view('company.widget_autocomplete_js', compact('apiBase'))->render();
        return response($content)
            ->header('Content-Type', 'application/javascript')
            ->header('Access-Control-Allow-Origin', '*');
    }
}
