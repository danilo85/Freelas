<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BibleController extends Controller
{
    /**
     * Show the public standalone Bible reading interface.
     */
    public function publicIndex()
    {
        return view('bible.index');
    }

    /**
     * Proxy books list.
     */
    public function proxyBooks()
    {
        $response = Http::get('https://www.daniloamiguel.com/api-public/veredas/livros');
        return response()->json($response->json(), $response->status());
    }

    /**
     * Proxy versions list.
     */
    public function proxyVersions()
    {
        $response = Http::get('https://www.daniloamiguel.com/api-public/veredas/versoes');
        return response()->json($response->json(), $response->status());
    }

    /**
     * Proxy chapter content.
     */
    public function proxyChapter(Request $request)
    {
        $response = Http::get('https://www.daniloamiguel.com/api-public/veredas/capitulo', $request->all());
        return response()->json($response->json(), $response->status());
    }

    /**
     * Proxy search query.
     */
    public function proxySearch(Request $request)
    {
        $response = Http::get('https://www.daniloamiguel.com/api-public/veredas/pesquisa', $request->all());
        return response()->json($response->json(), $response->status());
    }

    /**
     * Proxy contextual study content.
     */
    public function proxyContext(Request $request)
    {
        $response = Http::get('https://www.daniloamiguel.com/api-public/veredas/contexto', $request->all());
        return response()->json($response->json(), $response->status());
    }
}
