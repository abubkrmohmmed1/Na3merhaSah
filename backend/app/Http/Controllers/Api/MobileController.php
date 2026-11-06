<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Community\Models\UserAction;
use App\Domains\Spatial\Models\GeoFeature;
use App\Domains\Spatial\Models\MapLayer;
use App\Domains\Spatial\Services\TerritoryService;
use App\Domains\Community\Services\AchievementService;
use App\Domains\Community\Services\QuestService;
use App\Domains\Community\Services\TrustScoreService;
use App\Domains\Auth\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Http\Controllers\Api\MobileController;

class MobileController extends Controller
{
    // --- الفعاليات ---
    public function events(Request $request) {
        return (new \App\Http\Controllers\Admin\AdminEventsController())->mobileIndex($request);
    }

    // --- الخريطة والبحث ---
    public function mapFeatures(Request $request) {
        $this->recordDailyLogin();
        $lat = $request->query('lat');
        $lng = $request->query('lng');
        $radius = (int) ($request->radius ?? 100000); 
        $layer = $request->query('layer');
        $userId = Auth::id();

        $query = GeoFeature::select(
                'geo_features.id', 
                'map_layers.name as layer_name',
                DB::raw("geo_features.properties->>'name' as name"),
                DB::raw("ST_Y(ST_Centroid(geom)) as lat"),
                DB::raw("ST_X(ST_Centroid(geom)) as lng"),
                DB::raw("ST_GeometryType(geom) as type"),
                'geo_features.status'
            )
            ->join('map_layers', 'map_layers.id', '=', 'geo_features.layer_id')
            ->where(function($q) use ($userId) {
                $q->whereIn('geo_features.status', ['active', 'verified'])
                  ->orWhere(function($sub) use ($userId) {
                      $sub->where('geo_features.status', 'pending')
                          ->where('geo_features.user_id', $userId);
                  });
            });

        if ($layer && $layer !== 'الكل') $query->where('map_layers.name', $layer);

        if ($lat && $lng) {
            $query->addSelect(DB::raw("ST_Distance(geom::geography, ST_MakePoint($lng, $lat)::geography) as distance"));
            $query->whereRaw("ST_DWithin(geom::geography, ST_MakePoint(?, ?)::geography, ?)", [$lng, $lat, $radius]);
            $query->orderBy('distance');
        }
        $features = $query->limit(1000)->get();
        return response()->json(['status' => 'success', 'features' => $features]);
    }

    public function notifications()
    {
        $userId = Auth::id();
        $notifications = DB::table('app_notifications')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->take(50)
            ->get()
            ->map(function($n) {
                return [
                    'id' => $n->id,
                    'title' => $n->title,
                    'message' => $n->message,
                    'type' => $n->type,
                    'payload' => json_decode($n->payload),
                    'read_at' => $n->read_at,
                    'created_at' => Carbon::parse($n->created_at)->diffForHumans()
                ];
            });

        return response()->json([
            'status' => 'success',
            'notifications' => $notifications,
            'unread_count' => DB::table('app_notifications')->where('user_id', $userId)->whereNull('read_at')->count()
        ]);
    }

    public function markNotificationsRead()
    {
        DB::table('app_notifications')
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['status' => 'success']);
    }

    public function search(Request $request) {
        $q = $request->query('query');
        $lat = $request->query('lat');
        $lng = $request->query('lng');
        
        $places = GeoFeature::select('id', DB::raw("properties->>'name' as name"), DB::raw('ST_Y(ST_Centroid(geom)) as lat'), DB::raw('ST_X(ST_Centroid(geom)) as lng'))
            ->whereRaw("properties->>'name' ILIKE ?", ["%$q%"])
            ->limit(15)->get();
            
        return response()->json(['status' => 'success', 'results' => $places]);
    }

    // --- البروفايل والنشاط ---
    public function profile() {
        $this->recordDailyLogin();
        $user = Auth::user();
        $totalPoints = (int) UserAction::where('user_id', $user->id)->where('status', 'active')->sum('points');
        
        $lvlInfo = User::calculateLevel($totalPoints);
        $level = $lvlInfo['level'];
        $levelTitle = $lvlInfo['title'];
        $levelIcon = $lvlInfo['icon'];
        $nextLevelPoints = $lvlInfo['nextLevelPoints'];

        $rank = DB::table('user_actions')
            ->where('status', 'active')
            ->select('user_id', DB::raw('SUM(points) as total_pts'))
            ->groupBy('user_id')
            ->having(DB::raw('SUM(points)'), '>', $totalPoints)
            ->distinct()
            ->count('user_id') + 1;

        $trust = TrustScoreService::calculate($user->id);

        $totalAdds = UserAction::where('user_id', $user->id)->byType('add')->count();
        $totalConfirms = UserAction::where('user_id', $user->id)->byType('confirm')->count();
        $totalFlags = UserAction::where('user_id', $user->id)->byType('flag')->count();
        $totalVotes = UserAction::where('user_id', $user->id)->byType('vote')->count();

        $actions = UserAction::with('geoFeature')->where('user_id', $user->id)->orderBy('created_at', 'desc')->take(15)->get();

        return response()->json([
            'user' => ['name' => $user->name, 'phone' => $user->phone],
            'rank' => $rank,
            'trust_score' => $trust,
            'stats' => [
                'level' => $level,
                'level_title' => $levelTitle,
                'level_icon' => $levelIcon,
                'total_points' => $totalPoints,
                'next_level_points' => $nextLevelPoints,
                'streak_days' => $trust['streak_days'] ?? 0,
                'total_adds' => $totalAdds,
                'total_confirms' => $totalConfirms,
                'total_flags' => $totalFlags,
                'total_votes' => $totalVotes,
            ],
            'recent_actions' => $actions->map(function($a) {
                $props = $a->geoFeature?->properties ?? [];
                if (is_string($props)) { $props = json_decode($props, true) ?? []; }
                return [
                    'type' => $a->action_type,
                    'place_name' => $props['name'] ?? '—',
                    'points' => $a->points,
                    'created_at' => $a->created_at->diffForHumans()
                ];
            })
        ]);
    }

    public function feed() {
        $this->recordDailyLogin();
        $actions = UserAction::with('user', 'geoFeature')->orderBy('created_at', 'desc')->take(30)->get();
        return response()->json([
            'feed' => $actions->map(function($a) {
                $props = $a->geoFeature?->properties ?? [];
                if (is_string($props)) { $props = json_decode($props, true) ?? []; }
                return [
                    'user_name' => $a->user?->name ?? 'مجهول',
                    'action_type' => $a->action_type,
                    'place_name' => $props['name'] ?? '—',
                    'time_ago' => $a->created_at->diffForHumans()
                ];
            })
        ]);
    }
    

    public function votePlace(Request $request, string $id) {
        $request->validate([
            'rating' => 'required|integer|between:1,5',
        ]);

        $userId = Auth::id();

        $review = DB::table('reviews')
            ->where('user_id', $userId)
            ->where('geo_feature_id', $id)
            ->first();

        if ($review) {
            DB::table('reviews')
                ->where('id', $review->id)
                ->update([
                    'rating' => $request->rating,
                    'comment' => $request->comment,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('reviews')->insert([
                'id' => Str::uuid(),
                'user_id' => $userId,
                'geo_feature_id' => $id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add user action for voting if not already voted
        $exists = UserAction::where('user_id', $userId)
            ->where('geo_feature_id', $id)
            ->where('action_type', 'vote')
            ->exists();

        if (!$exists) {
            UserAction::create([
                'user_id' => $userId,
                'geo_feature_id' => $id,
                'action_type' => 'vote',
                'points' => 5,
                'status' => 'pending'
            ]);
        }

        return response()->json(['message' => 'تم التقييم']);
    }

    public function placeDetail(string $id) {
        $feature = GeoFeature::with('mapLayer')->findOrFail($id);
        
        $coords = DB::selectOne("
            SELECT ST_X(ST_Centroid(geom)) as lng, ST_Y(ST_Centroid(geom)) as lat 
            FROM geo_features 
            WHERE id = ?
        ", [$id]);

        $neighborhood = DB::selectOne("
            SELECT gf.properties->>'name' as name 
            FROM geo_features gf
            JOIN map_layers ml ON ml.id = gf.layer_id
            WHERE ml.name IN ('الأحياء', 'القرى والمدن')
              AND ST_Contains(gf.geom, ST_Centroid((SELECT geom FROM geo_features WHERE id = ?)))
            LIMIT 1
        ", [$id]);

        $locality = DB::selectOne("
            SELECT gf.properties->>'name' as name 
            FROM geo_features gf
            JOIN map_layers ml ON ml.id = gf.layer_id
            WHERE ml.name = 'المحليات'
              AND ST_Contains(gf.geom, ST_Centroid((SELECT geom FROM geo_features WHERE id = ?)))
            LIMIT 1
        ", [$id]);

        $reviews = DB::table('reviews')->where('geo_feature_id', $id)->get();
        
        $confirmations = UserAction::where('geo_feature_id', $id)
            ->where('action_type', 'confirm')
            ->count();

        $flags = UserAction::where('geo_feature_id', $id)
            ->where('action_type', 'flag')
            ->count();

        $properties = $feature->properties ?? [];
        if (is_string($properties)) {
            $properties = json_decode($properties, true) ?? [];
        }

        return response()->json([
            'id' => $feature->id,
            'name' => $properties['name'] ?? 'بدون اسم',
            'description' => $properties['description'] ?? '',
            'layer' => $feature->mapLayer?->name ?? 'معلم جغرافي',
            'status' => $feature->status,
            'neighborhood' => $neighborhood?->name ?? 'السودان',
            'locality' => $locality?->name ?? '',
            'lat' => $coords?->lat ?? 15.5871,
            'lng' => $coords?->lng ?? 32.5232,
            'confirmations' => $confirmations,
            'flags' => $flags,
            'avg_rating' => $reviews->isEmpty() ? '—' : round($reviews->avg('rating'), 1),
            'reviews' => $reviews
        ]);
    }

    // --- المناطق والحدود ---
    public function getLayers() {
        $ignored = ['4', '5', '6', 'administrative', 'hamlet'];
        $layers = MapLayer::where('is_public', true)->whereNotIn('name', $ignored)->get();
        return response()->json(['placeable_layers' => $layers]);
    }

    public function submitBoundaryChallenge(Request $request) {
        $wkt = "POLYGON((".implode(', ', array_map(fn($c) => "{$c['lng']} {$c['lat']}", $request->coordinates))."))";

        $feature = GeoFeature::create([
            'layer_id' => MapLayer::where('name', $request->layer)->first()->id,
            'user_id' => Auth::id(),
            'geometry_wkt' => $wkt,
            'properties' => [
                'name' => $request->name,
                'source' => 'boundary_challenge'
            ],
            'status' => 'pending'
        ]);

        UserAction::create([
            'user_id' => Auth::id(),
            'geo_feature_id' => $feature->id,
            'action_type' => 'add',
            'points' => UserAction::pointsForAction('add'),
            'status' => 'pending'
        ]);

        return response()->json(['message' => 'تم الإرسال للمراجعة بنجاح']);
    }

    // --- أخرى ---
    public function leaderboard(Request $request) {
        $user = Auth::user();
        $totalPoints = (int) UserAction::where('user_id', $user->id)->where('status', 'active')->sum('points');
        
        // Calculate actual user points
        $rows = UserAction::select('user_id', DB::raw('SUM(points) as total_points'), DB::raw('COUNT(*) as total_actions'))
            ->where('status', 'active')
            ->groupBy('user_id')
            ->orderByDesc('total_points')
            ->take(20)
            ->get();

        $leaderboard = $rows->map(function ($row, $i) {
            $user = User::find($row->user_id);
            $points = (int) $row->total_points;
            
            $lvlInfo = User::calculateLevel($points);
            $levelTitle = $lvlInfo['title'];
            $levelIcon = $lvlInfo['icon'];

            return [
                'rank' => $i + 1,
                'user_id' => $row->user_id,
                'name' => $user?->name ?? 'مستكشف نشط',
                'points' => $points,
                'actions' => (int) $row->total_actions,
                'level' => [
                    'icon' => $levelIcon,
                    'title' => $levelTitle,
                ],
                'avatar' => mb_substr($user?->name ?? 'م', 0, 1),
                'is_me' => $row->user_id === Auth::id(),
            ];
        })->toArray();

        // Mock data logic removed for brevity or kept as needed
        return response()->json(['leaderboard' => $leaderboard]);
    }

    public function territoryHighlights(Request $request) {
        $lat = $request->query('lat');
        $lng = $request->query('lng');
        return response()->json(['territories' => TerritoryService::territoryHighlights($lat, $lng)]);
    }

    public function territoryLeaderboard($id) { return response()->json(TerritoryService::neighborhoodLeaderboard($id)); }
    public function achievements() { return response()->json(['achievements' => AchievementService::getUserAchievements(Auth::id())]); }
    public function quests() { return response()->json(['quests' => QuestService::getUserQuests(Auth::id())]); }
    public function claimQuest($id) { return response()->json(QuestService::claimQuest(Auth::id(), $id)); }

    public function addPlace(Request $request) {
        $layer = MapLayer::firstOrCreate(['name' => $request->layer]);

        // 1. DEDUPLICATION CHECK
        if (!$request->force_add) {
            $lng = $request->lng;
            $lat = $request->lat;

            if ($lat && $lng) {
                $existing = DB::table('geo_features')
                    ->select('id', DB::raw("properties->>'name' as name"), DB::raw("ST_Distance(geom::geography, ST_MakePoint($lng, $lat)::geography) as distance"))
                    ->where('layer_id', $layer->id)
                    ->whereIn('status', ['active', 'verified', 'pending'])
                    ->whereRaw("ST_DWithin(geom::geography, ST_MakePoint(?, ?)::geography, 100)", [$lng, $lat])
                    ->get();

                foreach ($existing as $place) {
                    $name1 = mb_strtolower(trim($request->name));
                    $name2 = mb_strtolower(trim($place->name));
                    similar_text($name1, $name2, $percent);
                    if ($percent > 50 || str_contains($name1, $name2) || str_contains($name2, $name1)) {
                        return response()->json([
                            'status' => 'duplicate_check',
                            'existing_place' => [
                                'id' => $place->id,
                                'name' => $place->name,
                                'distance' => round($place->distance, 1)
                            ],
                            'message' => 'وجدنا مكاناً مشابهاً قريباً جداً منك. هل تقصد هذا المكان؟'
                        ], 200);
                    }
                }
            }
        }

        $createData = [
            'layer_id' => $layer->id,
            'user_id' => Auth::id(),
            'properties' => [
                'name' => $request->name,
                'description' => $request->description,
                'icon' => $request->icon,
            ],
            'status' => 'pending'
        ];

        if ($request->filled('lat') && $request->filled('lng')) {
            $createData['geom'] = DB::raw("ST_GeomFromText('POINT({$request->lng} {$request->lat})', 4326)");
        }

        if ($request->filled('geometry_wkt')) {
            $createData['geom'] = DB::raw("ST_GeomFromText('{$request->geometry_wkt}', 4326)");
        }

        $feature = GeoFeature::create($createData);

        UserAction::create([
            'user_id' => Auth::id(),
            'geo_feature_id' => $feature->id,
            'action_type' => 'add',
            'points' => UserAction::pointsForAction('add'),
            'status' => 'pending'
        ]);

        return response()->json(['message' => 'تم الإضافة بنجاح وبانتظار مراجعة المشرف']);
    }

    public function confirmPlace(Request $request, string $id) {
        $request->validate([
            'user_lat' => 'nullable|numeric',
            'user_lng' => 'nullable|numeric',
        ]);

        $userId = Auth::id();
        $exists = UserAction::where('user_id', $userId)->where('geo_feature_id', $id)->where('action_type', 'confirm')->where('created_at', '>=', now()->subDay())->exists();

        if ($exists) {
            return response()->json(['message' => 'لقد قمت بتأكيد هذا المكان بالفعل مؤخراً'], 422);
        }

        UserAction::create([
            'user_id' => $userId,
            'geo_feature_id' => $id,
            'action_type' => 'confirm',
            'points' => UserAction::pointsForAction('confirm'),
            'status' => 'pending',
            'payload' => json_encode(['user_lat' => $request->user_lat, 'user_lng' => $request->user_lng])
        ]);

        return response()->json(['message' => 'تم إرسال التأكيد بنجاح وسيتم إضافة النقاط بعد مراجعة المشرف!']);
    }

    public function flagPlace(Request $request, string $id) {
        $request->validate(['reason' => 'required|string|max:500']);
        $userId = Auth::id();
        $exists = UserAction::where('user_id', $userId)->where('geo_feature_id', $id)->where('action_type', 'flag')->exists();

        if ($exists) {
            return response()->json(['message' => 'لقد قمت بالإبلاغ عن هذا المكان بالفعل'], 422);
        }

        UserAction::create([
            'user_id' => $userId,
            'geo_feature_id' => $id,
            'action_type' => 'flag',
            'points' => UserAction::pointsForAction('flag'),
            'status' => 'pending',
            'payload' => json_encode(['reason' => $request->reason])
        ]);

        return response()->json(['message' => 'تم تسجيل بلاغك وسيقوم المشرفون بمراجعته.']);
    }

    public function suggestEdit(Request $request, string $id) {
        $request->validate(['field' => 'required|string|in:name,description', 'new_value' => 'required|string|max:1000']);
        $feature = GeoFeature::findOrFail($id);
        $userId = Auth::id();
        $properties = $feature->properties ?? [];
        if (is_string($properties)) $properties = json_decode($properties, true) ?? [];
        $oldValue = $properties[$request->field] ?? null;

        DB::table('edit_suggestions')->insert([
            'id' => Str::uuid(),
            'geo_feature_id' => $id,
            'user_id' => $userId,
            'field' => $request->field,
            'old_value' => $oldValue,
            'new_value' => $request->new_value,
            'status' => 'pending',
            'confirmations_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'تم إرسال اقتراح التعديل بنجاح وسيتم مراجعته.']);
    }

    public function appConfig(Request $request) {
        $platform = $request->query('platform', 'all');
        $latestVersion = DB::table('app_versions')->where('is_active', true)->where(fn($q) => $q->where('platform', 'all')->orWhere('platform', $platform))->orderByDesc('version_code')->first();
        $announcements = DB::table('system_announcements')->where('is_active', true)->where('starts_at', '<=', now()->addHours(6))->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))->orderByDesc('created_at')->get();

        return response()->json(['latest_version' => $latestVersion, 'announcements' => $announcements]);
    }

    public function confirmSuggestion() { return response()->json([]); }

    private function recordDailyLogin() {
        $userId = Auth::id();
        if (!$userId) return;
        $todayStart = now()->startOfDay();
        $exists = UserAction::where('user_id', $userId)->where('action_type', 'login')->where('created_at', '>=', $todayStart)->exists();
        if (!$exists) {
            UserAction::create(['user_id' => $userId, 'action_type' => 'login', 'points' => UserAction::pointsForAction('login'), 'status' => 'active']);
        }
    }
}
