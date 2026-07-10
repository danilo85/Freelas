<?php

namespace App\Http\Controllers;

use App\Models\BrandGuideline;
use Illuminate\Http\Request;

class PublicBrandController extends Controller
{
    public function show($token)
    {
        $guideline = BrandGuideline::where('share_token', $token)
            ->where('is_active', true)
            ->with(['client', 'user'])
            ->firstOrFail();

        return view('brand-guidelines.public', compact('guideline'));
    }
}
