<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\Spatial\Models\GeoFeature;
use App\Domains\Spatial\Models\MapLayer;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class GeoFeatureController extends Controller
{
    /**
     * Display a listing of geofeatures with filtering.
     */
    public function index(Request $request): View
    {
        $query = GeoFeature::with('mapLayer')->select(
            '*',
            DB::raw('ST_Y(ST_Centroid(geom::geometry)) as latitude'),
            DB::raw('ST_X(ST_Centroid(geom::geometry)) as longitude')
        )->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('layer_id')) {
            $query->where('layer_id', $request->layer_id);
        } elseif ($request->filled('admin_layer_id')) {
            $query->where('layer_id', $request->admin_layer_id);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->whereRaw("properties->>'name' ILIKE ?", ['%' . $request->search . '%'])
                  ->orWhere('plus_code', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->boolean('unknown_only')) {
            $query->where(function($q) {
                $q->whereRaw("properties->>'name' IN ('مجهول', 'Unknown')")
                  ->orWhereNull(DB::raw("properties->>'name'"));
            });
        }

        $features = $query->paginate(25)->withQueryString();
        $layers = MapLayer::orderBy('name')->get();

        $adminLayerNames = ['الولايات', 'المحليات', 'الأحياء', 'الحدود الإدارية'];
        $adminLayers = $layers->whereIn('name', $adminLayerNames);
        $serviceLayers = $layers->whereNotIn('name', $adminLayerNames);

        // --- Hierarchical Filtering Logic ---
        $states = GeoFeature::whereHas('mapLayer', fn($q) => $q->where('name', 'الولايات'))
            ->select('id', 'properties->name as name')->get();
        
        $localities = collect();
        $neighbourhoods = collect();

        // 1. If State selected, find Localities inside it
        if ($request->filled('state_id')) {
            $stateGeom = GeoFeature::find($request->state_id)?->geom;
            if ($stateGeom) {
                $localities = GeoFeature::whereHas('mapLayer', fn($q) => $q->where('name', 'المحليات'))
                    ->whereRaw("ST_Contains(?, geom)", [$stateGeom])
                    ->select('id', 'properties->name as name')->get();
                
                // If no locality selected, filter main query by state
                if (!$request->filled('locality_id')) {
                    $query->whereRaw("ST_Contains(?, geom)", [$stateGeom]);
                }
            }
        }

        // 2. If Locality selected, find Neighbourhoods inside it
        if ($request->filled('locality_id')) {
            $locGeom = GeoFeature::find($request->locality_id)?->geom;
            if ($locGeom) {
                $neighbourhoods = GeoFeature::whereHas('mapLayer', fn($q) => $q->where('name', 'الأحياء'))
                    ->whereRaw("ST_Contains(?, geom)", [$locGeom])
                    ->select('id', 'properties->name as name')->get();
                
                // If no neighbourhood selected, filter main query by locality
                if (!$request->filled('neighbourhood_id')) {
                    $query->whereRaw("ST_Contains(?, geom)", [$locGeom]);
                }
            }
        }

        // 3. If Neighbourhood selected, filter main query
        if ($request->filled('neighbourhood_id')) {
            $hoodGeom = GeoFeature::find($request->neighbourhood_id)?->geom;
            if ($hoodGeom) {
                $query->whereRaw("ST_Contains(?, geom)", [$hoodGeom]);
            }
        }

        // Re-paginate with spatial filters applied
        $features = $query->paginate(25)->withQueryString();

        return view('admin.spatial.index', compact(
            'features', 'layers', 'adminLayers', 'serviceLayers', 
            'states', 'localities', 'neighbourhoods'
        ));
    }

    /**
     * Show the edit form for a geofeature.
     */
    public function edit($id): View
    {
        $feature = GeoFeature::select(
            '*',
            DB::raw('ST_Y(ST_Centroid(geom::geometry)) as latitude'),
            DB::raw('ST_X(ST_Centroid(geom::geometry)) as longitude')
        )->findOrFail($id);

        $layers = MapLayer::all();
        return view('admin.spatial.edit', compact('feature', 'layers'));
    }

    /**
     * Update the geofeature in storage.
     */
    public function update(Request $request, $id)
    {
        $feature = GeoFeature::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'status' => 'required|in:pending,verified,archived'
        ]);

        $properties = $feature->properties;
        $properties['name'] = $request->name;
        
        $feature->latitude = $request->latitude;
        $feature->longitude = $request->longitude;
        $feature->status = $request->status;
        $feature->properties = $properties;
        $feature->save();

        return redirect()->route('admin.spatial.index')->with('success', 'تم تحديث المعلم الجغرافي بنجاح');
    }

    /**
     * Quickly verify a feature.
     */
    public function verify($id)
    {
        $feature = GeoFeature::findOrFail($id);
        $feature->update(['status' => 'verified']);
        return back()->with('success', 'تم توثيق المعلم بنجاح');
    }

    public function importForm(): \Illuminate\View\View
    {
        $layers = MapLayer::orderBy('name')->get();
        return view('admin.spatial.import', compact('layers'));
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
            'layer' => 'nullable|string|max:100',
            'create_layer' => 'nullable|boolean',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $delimiter = ',';
        $createLayer = $request->boolean('create_layer');
        $layerName = $request->layer;

        $rows = array_map(fn($line) => str_getcsv($line, $delimiter), file($path));
        $header = array_shift($rows);

        if (!$header) {
            return back()->with('error', 'الملف فارغ أو التنسيق غير صحيح');
        }

        $header = array_map('trim', $header);
        $nameCol = array_search('name', $header);
        $latCol = array_search('lat', $header) ?? array_search('latitude', $header);
        $lngCol = array_search('lng', $header) ?? array_search('lon', $header) ?? array_search('longitude', $header);
        $layerCol = array_search('layer', $header) ?? array_search('type', $header);
        $descCol = array_search('description', $header) ?? array_search('desc', $header);

        if ($nameCol === false || $latCol === false || $lngCol === false) {
            return back()->with('error', 'الملف يجب أن يحتوي على أعمدة: name, lat, lng');
        }

        $layer = null;
        if ($layerName) {
            $layer = MapLayer::where('name', $layerName)->first();
            if (!$layer && $createLayer) {
                $layer = MapLayer::create(['name' => $layerName, 'description' => 'مستورد', 'type' => 'points', 'is_public' => true]);
            }
        }

        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $row = array_map('trim', $row);
            $name = $row[$nameCol] ?? '';
            $lat = (float) ($row[$latCol] ?? 0);
            $lng = (float) ($row[$lngCol] ?? 0);

            $rowLayer = $layer;
            if (!$rowLayer && $layerCol !== false && !empty($row[$layerCol])) {
                $rowLayer = MapLayer::firstOrCreate(
                    ['name' => $row[$layerCol]],
                    ['description' => 'مستورد', 'type' => 'points', 'is_public' => true]
                );
            }

            if (empty($name) || $lat == 0 || $lng == 0 || !$rowLayer) {
                $skipped++;
                continue;
            }

            GeoFeature::create([
                'layer_id' => $rowLayer->id,
                'geometry_wkt' => "POINT($lng $lat)",
                'properties' => [
                    'name' => $name,
                    'description' => ($descCol !== false && isset($row[$descCol])) ? $row[$descCol] : null,
                    'source' => 'csv_import_admin',
                ],
                'status' => 'verified',
            ]);
            $imported++;
        }

        return redirect()->route('admin.spatial.index')
            ->with('success', "✅ تم استيراد $imported نقاط" . ($skipped > 0 ? " (تخطي $skipped)" : ""));
    }

    public function importCity(Request $request)
    {
        $request->validate(['city' => 'required|string|max:100']);

        $artisan = \Illuminate\Support\Facades\Artisan::class;
        $exitCode = \Illuminate\Support\Facades\Artisan::call('map:import-city', [
            'city' => $request->city,
        ]);

        $output = \Illuminate\Support\Facades\Artisan::output();

        if ($exitCode === 0) {
            // Extract the final summary line
            $lines = explode("\n", trim($output));
            $summary = collect($lines)->filter(fn($l) => str_contains($l, '✅'))->last() ?? 'تم الاستيراد';

            return redirect()->route('admin.spatial.index')
                ->with('success', "🏙️ $summary");
        }

        return back()->with('error', 'فشل استيراد المدينة');
    }

    /**
     * Remove the geofeature.
     */
    public function destroy($id)
    {
        $feature = GeoFeature::findOrFail($id);
        $feature->delete();
        return back()->with('success', 'تم حذف المعلم بنجاح');
    }
}
