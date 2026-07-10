<?php

namespace App\Http\Controllers;

use App\Models\BrandGuideline;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BrandGuidelineController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $guidelines = BrandGuideline::where('user_id', $userId)
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalCount = $guidelines->count();
        $activeCount = $guidelines->where('is_active', true)->count();
        $inactiveCount = $guidelines->where('is_active', false)->count();

        return view('brand-guidelines.index', compact('guidelines', 'totalCount', 'activeCount', 'inactiveCount'));
    }

    public function create()
    {
        $clients = auth()->user()->clients()->orderBy('name')->get();
        return view('brand-guidelines.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'brand_name' => 'required|string|max:255',
            'client_id' => 'nullable|exists:clients,id',
        ]);

        $guideline = new BrandGuideline();
        $guideline->user_id = auth()->id();
        $guideline->brand_name = $request->brand_name;
        $guideline->client_id = $request->client_id;
        $guideline->share_token = Str::random(40);
        $guideline->is_active = $request->has('is_active');
        $guideline->save();

        return redirect()->route('revisoes.brand-guidelines.edit', $guideline->id)
            ->with('success', 'Marca criada! Agora gerencie as seções individualmente.');
    }

    public function edit($id)
    {
        $guideline = BrandGuideline::where('user_id', auth()->id())->findOrFail($id);
        $clients = auth()->user()->clients()->orderBy('name')->get();
        return view('brand-guidelines.edit', compact('guideline', 'clients'));
    }

    public function update(Request $request, $id)
    {
        $guideline = BrandGuideline::where('user_id', auth()->id())->findOrFail($id);
        $stage = $request->input('stage');

        if ($stage === 'general') {
            $request->validate([
                'brand_name' => 'required|string|max:255',
                'client_id' => 'nullable|exists:clients,id',
            ]);
            $guideline->brand_name = $request->brand_name;
            $guideline->client_id = $request->client_id;
            $guideline->is_active = $request->has('is_active');
        } 
        
        elseif ($stage === 'colors') {
            $colors = [];
            if ($request->has('colors')) {
                foreach ($request->colors as $color) {
                    if (!empty($color['hex'])) {
                        $colors[] = [
                            'name' => $color['name'] ?? 'Cor',
                            'hex' => $color['hex'],
                            'rgb' => $color['rgb'] ?? '',
                            'cmyk' => $color['cmyk'] ?? '',
                            'note' => $color['note'] ?? ''
                        ];
                    }
                }
            }
            $guideline->color_palette = $colors;
        } 
        
        elseif ($stage === 'fonts') {
            $typography = $guideline->typography ?? [];

            // Add new font
            if ($request->has('new_font')) {
                $fontFamily = $request->input('new_font.font_family');
                $usage = $request->input('new_font.usage', 'Geral');
                $specimenText = $request->input('new_font.specimen_text', 'Abc123');
                $fontFilePath = null;

                if ($request->hasFile('new_font.font_file')) {
                    $fontFilePath = $request->file('new_font.font_file')->store('brand_guidelines/fonts', 'public');
                }

                if (!empty($fontFamily)) {
                    $typography[] = [
                        'font_family' => $fontFamily,
                        'usage' => $usage,
                        'specimen_text' => $specimenText,
                        'font_file' => $fontFilePath
                    ];
                }
            }

            // Remove font index
            if ($request->has('remove_font_index')) {
                $idx = $request->input('remove_font_index');
                if (isset($typography[$idx])) {
                    if (!empty($typography[$idx]['font_file'])) {
                        Storage::disk('public')->delete($typography[$idx]['font_file']);
                    }
                    unset($typography[$idx]);
                }
                $typography = array_values($typography);
            }

            $guideline->typography = $typography;
        } 
        
        elseif ($stage === 'social') {
            $socialMedia = $guideline->social_media ?? [];
            $networks = ['instagram', 'facebook', 'linkedin', 'youtube', 'tiktok', 'whatsapp'];
            
            foreach ($networks as $network) {
                if (!isset($socialMedia[$network])) {
                    $socialMedia[$network] = ['avatar' => null, 'cover' => null];
                }
                if ($request->hasFile("social_media_{$network}_avatar")) {
                    if (!empty($socialMedia[$network]['avatar'])) {
                        Storage::disk('public')->delete($socialMedia[$network]['avatar']);
                    }
                    $socialMedia[$network]['avatar'] = $request->file("social_media_{$network}_avatar")->store('brand_guidelines/social', 'public');
                }
                if ($request->hasFile("social_media_{$network}_cover")) {
                    if (!empty($socialMedia[$network]['cover'])) {
                        Storage::disk('public')->delete($socialMedia[$network]['cover']);
                    }
                    $socialMedia[$network]['cover'] = $request->file("social_media_{$network}_cover")->store('brand_guidelines/social', 'public');
                }
            }
            $guideline->social_media = $socialMedia;
        } 
        
        elseif ($stage === 'logos') {
            if ($request->hasFile('logo_primary')) {
                if ($guideline->logo_primary) Storage::disk('public')->delete($guideline->logo_primary);
                $guideline->logo_primary = $request->file('logo_primary')->store('brand_guidelines/logos', 'public');
            }
            if ($request->hasFile('logo_secondary')) {
                if ($guideline->logo_secondary) Storage::disk('public')->delete($guideline->logo_secondary);
                $guideline->logo_secondary = $request->file('logo_secondary')->store('brand_guidelines/logos', 'public');
            }
            if ($request->hasFile('logo_symbol')) {
                if ($guideline->logo_symbol) Storage::disk('public')->delete($guideline->logo_symbol);
                $guideline->logo_symbol = $request->file('logo_symbol')->store('brand_guidelines/logos', 'public');
            }
            $guideline->logo_description = $request->input('logo_description');
            $guideline->logo_horizontal_desc = $request->input('logo_horizontal_desc');
            $guideline->logo_vertical_desc = $request->input('logo_vertical_desc');
            $guideline->logo_symbol_desc = $request->input('logo_symbol_desc');
        } 
        
        elseif ($stage === 'stationery') {
            $stationery = $guideline->stationery ?? [];

            if ($request->has('remove_stationery_indexes')) {
                foreach ($request->remove_stationery_indexes as $remIdx) {
                    if (isset($stationery[$remIdx])) {
                        Storage::disk('public')->delete($stationery[$remIdx]['path']);
                        unset($stationery[$remIdx]);
                    }
                }
                $stationery = array_values($stationery);
            }

            if ($request->hasFile('stationery_files')) {
                $files = $request->file('stationery_files');
                $names = $request->input('stationery_names', []);
                foreach ($files as $index => $file) {
                    $path = $file->store('brand_guidelines/stationery', 'public');
                    $stationery[] = [
                        'name' => $names[$index] ?? $file->getClientOriginalName(),
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName()
                    ];
                }
            }
            $guideline->stationery = $stationery;
        } 
        
        elseif ($stage === 'package') {
            if ($request->hasFile('final_package_file')) {
                if ($guideline->final_package) {
                    Storage::disk('public')->delete($guideline->final_package);
                }
                $guideline->final_package = $request->file('final_package_file')->store('brand_guidelines/packages', 'public');
            }
        }

        $guideline->save();

        return response()->json([
            'success' => true,
            'guideline' => $guideline,
            'message' => 'Etapa atualizada com sucesso!'
        ]);
    }

    public function destroy($id)
    {
        $guideline = BrandGuideline::where('user_id', auth()->id())->findOrFail($id);

        if ($guideline->logo_primary) Storage::disk('public')->delete($guideline->logo_primary);
        if ($guideline->logo_secondary) Storage::disk('public')->delete($guideline->logo_secondary);
        if ($guideline->logo_symbol) Storage::disk('public')->delete($guideline->logo_symbol);
        if ($guideline->final_package) Storage::disk('public')->delete($guideline->final_package);

        if ($guideline->social_media) {
            foreach ($guideline->social_media as $net) {
                if (!empty($net['avatar'])) Storage::disk('public')->delete($net['avatar']);
                if (!empty($net['cover'])) Storage::disk('public')->delete($net['cover']);
            }
        }

        if ($guideline->typography) {
            foreach ($guideline->typography as $typo) {
                if (!empty($typo['font_file'])) Storage::disk('public')->delete($typo['font_file']);
            }
        }

        if ($guideline->stationery) {
            foreach ($guideline->stationery as $item) {
                Storage::disk('public')->delete($item['path']);
            }
        }

        $guideline->delete();

        return redirect()->route('revisoes.brand-guidelines.index')
            ->with('success', 'Manual de Identidade Visual excluído com sucesso!');
    }

    public function toggleActive($id)
    {
        $guideline = BrandGuideline::where('user_id', auth()->id())->findOrFail($id);
        $guideline->is_active = !$guideline->is_active;
        $guideline->save();

        return response()->json([
            'success' => true,
            'is_active' => $guideline->is_active
        ]);
    }

    public function downloadZip($id)
    {
        $guideline = BrandGuideline::where('user_id', auth()->id())->findOrFail($id);
        
        $zip = new \ZipArchive();
        $zipFileName = str_replace(' ', '_', strtolower($guideline->brand_name)) . '_brand_assets.zip';
        $zipFilePath = storage_path('app/public/' . $zipFileName);

        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            // Add Logos
            if ($guideline->logo_primary && Storage::disk('public')->exists($guideline->logo_primary)) {
                $zip->addFile(Storage::disk('public')->path($guideline->logo_primary), 'logos/logo_principal.' . pathinfo($guideline->logo_primary, PATHINFO_EXTENSION));
            }
            if ($guideline->logo_secondary && Storage::disk('public')->exists($guideline->logo_secondary)) {
                $zip->addFile(Storage::disk('public')->path($guideline->logo_secondary), 'logos/logo_alternativa.' . pathinfo($guideline->logo_secondary, PATHINFO_EXTENSION));
            }
            if ($guideline->logo_symbol && Storage::disk('public')->exists($guideline->logo_symbol)) {
                $zip->addFile(Storage::disk('public')->path($guideline->logo_symbol), 'logos/simbolo.' . pathinfo($guideline->logo_symbol, PATHINFO_EXTENSION));
            }
            // Add social media
            if ($guideline->social_media) {
                foreach ($guideline->social_media as $network => $assets) {
                    if (!empty($assets['avatar']) && Storage::disk('public')->exists($assets['avatar'])) {
                        $zip->addFile(Storage::disk('public')->path($assets['avatar']), 'redes_sociais/' . $network . '_avatar.' . pathinfo($assets['avatar'], PATHINFO_EXTENSION));
                    }
                    if (!empty($assets['cover']) && Storage::disk('public')->exists($assets['cover'])) {
                        $zip->addFile(Storage::disk('public')->path($assets['cover']), 'redes_sociais/' . $network . '_capa.' . pathinfo($assets['cover'], PATHINFO_EXTENSION));
                    }
                }
            }
            // Add stationery
            if ($guideline->stationery) {
                foreach ($guideline->stationery as $item) {
                    if (Storage::disk('public')->exists($item['path'])) {
                        $zip->addFile(Storage::disk('public')->path($item['path']), 'papelaria/' . $item['original_name']);
                    }
                }
            }
            // Add fonts
            if ($guideline->typography) {
                foreach ($guideline->typography as $typo) {
                    if (!empty($typo['font_file']) && Storage::disk('public')->exists($typo['font_file'])) {
                        $zip->addFile(Storage::disk('public')->path($typo['font_file']), 'fontes/' . basename($typo['font_file']));
                    }
                }
            }
            $zip->close();
        }

        if (file_exists($zipFilePath)) {
            return response()->download($zipFilePath)->deleteFileAfterSend(true);
        }
        
        return back()->with('error', 'Nenhum asset cadastrado para gerar o pacote.');
    }
}
