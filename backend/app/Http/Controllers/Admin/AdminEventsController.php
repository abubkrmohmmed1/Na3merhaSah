<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class AdminEventsController extends Controller
{
    /**
     * لوحة تحكم الفعاليات والأنشطة
     */
    public function index()
    {
        $events = DB::table('app_events')
            ->select('*', DB::raw('ST_X(geom::geometry) as longitude'), DB::raw('ST_Y(geom::geometry) as latitude'))
            ->orderByDesc('starts_at')
            ->get();
        
        $pendingCount = DB::table('geo_features')
            ->join('map_layers', 'map_layers.id', '=', 'geo_features.layer_id')
            ->whereIn('map_layers.name', ['الأحياء', 'القرى والمدن'])
            ->where('geo_features.status', 'pending')
            ->whereRaw("geo_features.properties->>'source' = 'boundary_challenge'")
            ->count();

        return view('admin.events.index', compact('events', 'pendingCount'));
    }

    public function create()
    {
        $states = DB::table('geo_features')
            ->join('map_layers', 'map_layers.id', '=', 'geo_features.layer_id')
            ->where('map_layers.name', 'الولايات')
            ->select('geo_features.id', DB::raw("properties->>'name' as name"))
            ->orderBy('name')
            ->get();

        $mapLayers = DB::table('map_layers')->orderBy('name')->get();

        return view('admin.events.form', [
            'event' => null,
            'states' => $states,
            'mapLayers' => $mapLayers
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'type'        => 'required|in:competition,challenge,community,announcement',
            'execution_mode' => 'required|in:manual,walking_gps,auto_sum',
            'resolution_method' => 'required|in:manual,top_contributor,goal_reached',
            'rules'       => 'nullable',
            'starts_at'   => 'required|date',
            'ends_at'     => 'nullable|date|after:starts_at',
            'reward_points' => 'nullable|integer|min:0',
            'target_layer'  => 'nullable|string|max:100',
            'target_action' => 'nullable|string|max:50',
            'is_active'     => 'boolean',
            'status'        => 'required|in:draft,active,resolved,cancelled',
            'image_url'     => 'nullable|url',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'scope_type'    => 'required|in:national,state,locality,neighborhood',
            'scope_id'      => 'nullable|uuid',
        ]);

        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;
        unset($data['latitude'], $data['longitude']);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['created_by'] = auth()->id();
        $data['created_at'] = now();
        $data['updated_at'] = now();

        if ($latitude && $longitude) {
            $data['geom'] = DB::raw("ST_GeomFromText('POINT($longitude $latitude)', 4326)");
        }

        DB::table('app_events')->insert($data);

        return redirect()->route('admin.events.index')->with('success', '✅ تم إنشاء الفعالية بنجاح');
    }

    public function edit(int $id)
    {
        $event = DB::table('app_events')
            ->select('*', DB::raw('ST_X(geom::geometry) as longitude'), DB::raw('ST_Y(geom::geometry) as latitude'))
            ->where('id', $id)
            ->first();

        if (!$event) abort(404);

        $states = DB::table('geo_features')
            ->join('map_layers', 'map_layers.id', '=', 'geo_features.layer_id')
            ->where('map_layers.name', 'الولايات')
            ->select('geo_features.id', DB::raw("properties->>'name' as name"))
            ->orderBy('name')
            ->get();

        $mapLayers = DB::table('map_layers')->orderBy('name')->get();
        
        return view('admin.events.form', compact('event', 'states', 'mapLayers'));
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'type'        => 'required|in:competition,challenge,community,announcement',
            'starts_at'   => 'required|date',
            'ends_at'     => 'nullable|date|after:starts_at',
            'reward_points' => 'nullable|integer|min:0',
            'target_layer'  => 'nullable|string|max:100',
            'target_action' => 'nullable|string|max:50',
            'is_active'     => 'boolean',
            'image_url'     => 'nullable|url',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'scope_type'    => 'required|in:national,state,locality,neighborhood',
            'scope_id'      => 'nullable|uuid',
        ]);

        $latitude = $data['latitude'];
        $longitude = $data['longitude'];
        unset($data['latitude'], $data['longitude']);

        $data['is_active'] = $request->boolean('is_active', false);
        $data['updated_at'] = now();

        if ($latitude && $longitude) {
            $data['geom'] = DB::raw("ST_GeomFromText('POINT($longitude $latitude)', 4326)");
        } else {
            $data['geom'] = null;
        }

        DB::table('app_events')->where('id', $id)->update($data);

        return redirect()->route('admin.events.index')->with('success', '✅ تم تحديث الفعالية');
    }

    /**
     * AJAX: جلب الميزات الجغرافية حسب الطبقة (للمحليات والأحياء)
     */
    public function getFeaturesByLayer(Request $request)
    {
        $layerName = $request->query('layer');
        $parentId = $request->query('parent_id');

        $query = DB::table('geo_features')
            ->join('map_layers', 'map_layers.id', '=', 'geo_features.layer_id')
            ->where('map_layers.name', $layerName);

        if ($parentId) {
            // نستخدم ST_Contains إذا كان الـ parent موجوداً
            $parentGeom = DB::table('geo_features')->where('id', $parentId)->value('geom');
            if ($parentGeom) {
                $query->whereRaw("ST_Contains(?, geom)", [$parentGeom]);
            }
        }

        $features = $query->select(
                'geo_features.id', 
                DB::raw("properties->>'name' as name"),
                DB::raw('ST_X(ST_Centroid(geom)) as lng'),
                DB::raw('ST_Y(ST_Centroid(geom)) as lat')
            )
            ->orderBy('name')
            ->get();

        return response()->json($features);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        DB::table('app_events')->where('id', $id)->delete();
        return redirect()->route('admin.events.index')->with('success', 'تم حذف الفعالية');
    }

    /**
     * API للموبايل: جلب الفعاليات مع فلترة المسافة والنطاق
     */
    public function mobileIndex(Request $request)
    {
        $lat = $request->query('lat');
        $lng = $request->query('lng');
        $tab = $request->query('tab', 'active'); // active, past

        $query = DB::table('app_events')
            ->select('*', 
                DB::raw('ST_X(geom::geometry) as longitude'), 
                DB::raw('ST_Y(geom::geometry) as latitude')
            );

        if ($tab === 'active') {
            $query->where('is_active', true)
                  ->where(function($q) {
                      $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                  });
        } else {
            $query->where(function($q) {
                $q->where('is_active', false)->orWhere('ends_at', '<', now());
            })->orderByDesc('ends_at');
        }

        if ($lat && $lng) {
            // Calculate containing IDs for spatial filtering
            $stateId = DB::table('geo_features')->join('map_layers', 'map_layers.id', '=', 'geo_features.layer_id')->where('map_layers.name', 'الولايات')->whereRaw("ST_Contains(geom::geometry, ST_SetSRID(ST_MakePoint(?, ?), 4326))", [$lng, $lat])->value('geo_features.id');
            $localityId = DB::table('geo_features')->join('map_layers', 'map_layers.id', '=', 'geo_features.layer_id')->where('map_layers.name', 'المحليات')->whereRaw("ST_Contains(geom::geometry, ST_SetSRID(ST_MakePoint(?, ?), 4326))", [$lng, $lat])->value('geo_features.id');
            $neighborhoodId = DB::table('geo_features')->join('map_layers', 'map_layers.id', '=', 'geo_features.layer_id')->whereIn('map_layers.name', ['الأحياء', 'القرى والمدن'])->whereRaw("ST_Contains(geom::geometry, ST_SetSRID(ST_MakePoint(?, ?), 4326))", [$lng, $lat])->value('geo_features.id');

            $query->where(function($q) use ($stateId, $localityId, $neighborhoodId, $lng, $lat) {
                $q->where('scope_type', 'national')
                  ->orWhere(function($sub) use ($stateId) {
                      $sub->where('scope_type', 'state');
                      if ($stateId) { $sub->where('scope_id', $stateId); } else { $sub->whereRaw('1 = 0'); }
                  })
                  ->orWhere(function($sub) use ($localityId) {
                      $sub->where('scope_type', 'locality');
                      if ($localityId) { $sub->where('scope_id', $localityId); } else { $sub->whereRaw('1 = 0'); }
                  })
                  ->orWhere(function($sub) use ($neighborhoodId) {
                      $sub->where('scope_type', 'neighborhood');
                      if ($neighborhoodId) { $sub->where('scope_id', $neighborhoodId); } else { $sub->whereRaw('1 = 0'); }
                  })
                  ->orWhere(function($sub) use ($lng, $lat) {
                      $sub->whereNotNull('geom')
                          ->whereRaw("ST_DWithin(geom::geography, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, 15000)", [$lng, $lat]);
                  });
            });

            $query->addSelect(DB::raw("ST_Distance(geom, ST_GeomFromText('POINT($lng $lat)', 4326), true) as distance"));
            if ($tab === 'active') $query->orderBy('distance');
        } else {
            if ($tab === 'active') $query->where('scope_type', 'national')->orderByDesc('starts_at');
        }

        $events = $query->limit(50)->get();

        return response()->json(['status' => 'success', 'events' => $events]);
    }

    /**
     * مراجعة تحديات الحدود
     */
    public function boundaryChallenges()
    {
        $challenges = DB::table('geo_features as gf')
            ->join('map_layers as ml', 'ml.id', '=', 'gf.layer_id')
            ->whereIn('ml.name', ['الأحياء', 'القرى والمدن'])
            ->where('gf.status', 'pending')
            ->whereRaw("gf.properties->>'source' = 'boundary_challenge'")
            ->select(
                'gf.id', 
                'gf.status',
                DB::raw("gf.properties->>'name' as name"),
                DB::raw("gf.properties->>'description' as description"),
                'ml.name as layer_name',
                'gf.created_at'
            )
            ->orderByDesc('gf.created_at')
            ->get();

        return view('admin.events.boundary_challenges', compact('challenges'));
    }

    public function approveChallenge(string $id)
    {
        $feature = DB::table('geo_features')->where('id', $id)->first();
        if (!$feature) abort(404);

        DB::table('geo_features')->where('id', $id)->update([
            'status'     => 'verified',
            'updated_at' => now(),
        ]);

        // Activate points
        DB::table('user_actions')
            ->where('geo_feature_id', $id)
            ->where('status', 'pending')
            ->update([
                'status' => 'active',
                'updated_at' => now(),
            ]);

        // Notify user
        if ($feature->user_id) {
            $props = json_decode($feature->properties, true);
            $name = $props['name'] ?? 'منطقة';
            \App\Domains\Community\Services\NotificationService::send(
                $feature->user_id,
                '✅ تم قبول رسم الحدود!',
                "تمت الموافقة على رسمك لـ '$name' ونشره على الخريطة بنجاح. شكراً لمساهمتك!",
                'success',
                ['place_id' => $id]
            );
        }

        return redirect()->back()->with('success', '✅ تم قبول التحدي وتحديث الخريطة وتفعيل نقاط المشاركين بنجاح!');
    }

    public function rejectChallenge(string $id)
    {
        DB::table('geo_features')->where('id', $id)->update([
            'status'     => 'rejected',
            'updated_at' => now(),
        ]);

        // Delete associated pending user actions since the challenge was rejected
        DB::table('user_actions')
            ->where('geo_feature_id', $id)
            ->delete();

        return redirect()->back()->with('success', 'تم رفض التحدي وإلغاء نقاط المشاركين.');
    }

    public function showResolveForm(int $id)
    {
        $event = DB::table('app_events')
            ->select('*', DB::raw('ST_X(geom::geometry) as longitude'), DB::raw('ST_Y(geom::geometry) as latitude'))
            ->where('id', $id)
            ->first();

        if (!$event) abort(404);

        $eventGeom = DB::table('app_events')->where('id', $id)->value('geom');

        $query = DB::table('user_actions')
            ->join('users', 'users.id', '=', 'user_actions.user_id')
            ->join('geo_features', 'geo_features.id', '=', 'user_actions.geo_feature_id')
            ->where('user_actions.status', 'active')
            ->whereBetween('user_actions.created_at', [$event->starts_at, $event->ends_at ?? now()]);

        if ($event->target_layer) {
            $query->join('map_layers', 'map_layers.id', '=', 'geo_features.layer_id')
                  ->where('map_layers.name', $event->target_layer);
        }

        if (isset($event->target_action) && $event->target_action) {
            $query->where('user_actions.action_type', $event->target_action);
        }

        // Resolution Metric logic
        $metricSql = 'COUNT(user_actions.id)';
        $orderBySql = 'contributions_count';

        if ($event->execution_mode === 'walking_gps') {
            $metricSql = "SUM((geo_features.properties->>'area_sqm')::numeric)";
            $orderBySql = 'total_area';
        }

        $leaderboard = $query->select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw('COUNT(user_actions.id) as contributions_count'),
                DB::raw("SUM((geo_features.properties->>'area_sqm')::numeric) as total_area"),
                DB::raw("SUM((geo_features.properties->>'perimeter_m')::numeric) as total_perimeter"),
                DB::raw('SUM(user_actions.points) as base_points')
            )
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc($orderBySql)
            ->limit(15)
            ->get();

        return view('admin.events.resolve', compact('event', 'leaderboard'));
    }

    public function resolve(Request $request, int $id)
    {
        $event = DB::table('app_events')->where('id', $id)->first();
        if (!$event || $event->is_resolved) {
            return redirect()->route('admin.events.index')->with('error', 'الفعالية غير صالحة أو تم إقفالها مسبقاً.');
        }

        $orderBySql = ($event->execution_mode === 'walking_gps') ? 'total_area' : 'contributions_count';

        $query = DB::table('user_actions')
            ->join('users', 'users.id', '=', 'user_actions.user_id')
            ->join('geo_features', 'geo_features.id', '=', 'user_actions.geo_feature_id')
            ->where('user_actions.status', 'active')
            ->whereBetween('user_actions.created_at', [$event->starts_at, $event->ends_at ?? now()]);

        if ($event->target_layer) {
            $query->join('map_layers', 'map_layers.id', '=', 'geo_features.layer_id')
                  ->where('map_layers.name', $event->target_layer);
        }

        $leaderboard = $query->select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw('COUNT(user_actions.id) as contributions_count'),
                DB::raw("SUM((geo_features.properties->>'area_sqm')::numeric) as total_area")
            )
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc($orderBySql)
            ->limit(3)
            ->get();

        if ($leaderboard->isEmpty()) {
            return redirect()->back()->with('error', 'لا يوجد مشاركون في هذه الفعالية لتتويجهم.');
        }

        $winners = [];
        $rewards = [1.0, 0.5, 0.25];

        foreach ($leaderboard as $index => $winner) {
            $multiplier = $rewards[$index] ?? 0;
            $bonus = (int) ($event->reward_points * $multiplier);

            if ($bonus > 0) {
                DB::table('user_actions')->insert([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'user_id' => $winner->id,
                    'geo_feature_id' => null,
                    'action_type' => 'quest_reward',
                    'points' => $bonus,
                    'status' => 'active',
                    'payload' => json_encode(['event_id' => $id, 'rank' => $index + 1, 'event_title' => $event->title]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Notify winner
                $rankLabels = ['الأول 🥇', 'الثاني 🥈', 'الثالث 🥉'];
                $rankText = $rankLabels[$index] ?? "رقم " . ($index + 1);
                \App\Domains\Community\Services\NotificationService::send(
                    $winner->id,
                    '🏆 مبروك الفوز في التحدي!',
                    "لقد حققت المركز $rankText في فعالية '{$event->title}' وكسبت $bonus نقطة إضافية!",
                    'reward',
                    ['event_id' => $id, 'rank' => $index + 1]
                );
            }

            $winners[] = [
                'rank' => $index + 1,
                'user_id' => $winner->id,
                'name' => $winner->name,
                'metric_value' => ($event->execution_mode === 'walking_gps') ? $winner->total_area : $winner->contributions_count,
                'reward_points' => $bonus
            ];
        }

        DB::table('app_events')->where('id', $id)->update([
            'is_resolved' => true,
            'is_active' => false,
            'winners_data' => json_encode($winners),
            'updated_at' => now()
        ]);

        $topWinnerName = $winners[0]['name'] ?? 'مستكشف مجهول';
        $metricLabel = ($event->execution_mode === 'walking_gps') ? "بإجمالي مساحة {$winners[0]['metric_value']} متر مربع" : "بمجموع {$winners[0]['metric_value']} مساهمة";

        DB::table('system_announcements')->insert([
            'title' => '🏆 انتهاء التحدي وتتويج الأبطال!',
            'message' => "📢 تم بنجاح إنهاء فعالية '{$event->title}' وتوزيع النقاط والمكافآت على الفائزين! فاز بالمركز الأول البطل '{$topWinnerName}' {$metricLabel}. مبارك للجميع ونعمرها صح! 🇸🇩",
            'type' => 'success',
            'event_id' => $id,
            'is_active' => true,
            'starts_at' => now(),
            'ends_at' => now()->addDays(7),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->route('admin.events.index')->with('success', '🏆 تم بنجاح تتويج الفائزين وتوزيع النقاط ونشر الإعلان العام بالتطبيق!');
    }

    public function mobileLeaderboard(int $id)
    {
        $event = DB::table('app_events')->where('id', $id)->first();
        if (!$event) return response()->json(['message' => 'الفعالية غير موجودة'], 404);

        if ($event->is_resolved && $event->winners_data) {
            return response()->json([
                'status' => 'resolved',
                'winners' => json_decode($event->winners_data),
                'event' => $event
            ]);
        }

        $orderBySql = ($event->execution_mode === 'walking_gps') ? 'total_area' : 'contributions_count';

        $query = DB::table('user_actions')
            ->join('users', 'users.id', '=', 'user_actions.user_id')
            ->join('geo_features', 'geo_features.id', '=', 'user_actions.geo_feature_id')
            ->where('user_actions.status', 'active')
            ->whereBetween('user_actions.created_at', [$event->starts_at, $event->ends_at ?? now()]);

        if ($event->target_layer) {
            $query->join('map_layers', 'map_layers.id', '=', 'geo_features.layer_id')
                  ->where('map_layers.name', $event->target_layer);
        }

        $leaderboard = $query->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(user_actions.id) as contributions_count'),
                DB::raw("COALESCE(SUM((geo_features.properties->>'area_sqm')::numeric), 0) as total_area")
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc($orderBySql)
            ->limit(20)
            ->get()
            ->map(function($row, $i) use ($event) {
                return [
                    'rank' => $i + 1,
                    'name' => $row->name,
                    'value' => ($event->execution_mode === 'walking_gps') ? $row->total_area : $row->contributions_count,
                    'unit' => ($event->execution_mode === 'walking_gps') ? 'م²' : 'مساهمة',
                    'is_me' => $row->id === auth()->id()
                ];
            });

        return response()->json([
            'status' => 'active',
            'leaderboard' => $leaderboard,
            'event' => $event
        ]);
    }
}
