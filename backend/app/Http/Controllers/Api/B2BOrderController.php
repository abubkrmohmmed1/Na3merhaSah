<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class B2BOrderController extends Controller
{
    #[OA\Post(
        path: '/api/v1/b2b/orders',
        operationId: 'createB2BOrder',
        summary: 'إنشاء طلب توثيق جديد (Batch)',
        description: 'يسمح للشركة برفع قائمة نقاط جغرافية لتوثيقها ميدانياً.',
        tags: ['طلبات الشركات B2B'],
        security: [['api_key' => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'items', type: 'array', items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'lat', type: 'number'),
                        new OA\Property(property: 'lng', type: 'number')
                    ]
                )),
                new OA\Property(property: 'payment_method', type: 'string', enum: ['bankak', 'cashi', 'bravo'])
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'تم إنشاء الطلب بنجاح')]
    public function store(Request $request)
    {
        $company = $request->get('api_company');
        
        $request->validate([
            'items' => 'required|array|min:1|max:500',
            'items.*.name' => 'required|string|max:255',
            'items.*.lat' => 'required|numeric',
            'items.*.lng' => 'required|numeric',
            'payment_method' => 'required|in:bankak,cashi,bravo',
        ]);

        $itemCount = count($request->items);
        $pricePerItem = config('namerha.verification_price', 150);
        $totalPrice = $itemCount * $pricePerItem;
        $orderId = Str::uuid();

        DB::beginTransaction();
        try {
            DB::table('verification_orders')->insert([
                'id' => $orderId,
                'company_id' => $company->id,
                'reference_no' => 'ORD-' . strtoupper(Str::random(8)),
                'status' => 'pending_payment',
                'total_items' => $itemCount,
                'total_price' => $totalPrice,
                'currency' => 'SDG',
                'payment_method' => $request->payment_method,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($request->items as $item) {
                DB::table('verification_items')->insert([
                    'id' => Str::uuid(),
                    'order_id' => $orderId,
                    'target_name' => $item['name'],
                    'target_geom' => DB::raw("ST_GeomFromText('POINT({$item['lng']} {$item['lat']})', 4326)"),
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            // Trigger Quest Bridge
            \App\Domains\Spatial\Services\QuestBridgeService::bridgeOrderToQuests($orderId);

            return response()->json([
                'status' => 'success',
                'order_id' => $orderId,
                'total_price' => $totalPrice,
                'message' => 'تم إنشاء طلب التوثيق. يرجى إتمام الدفع للبدء في المعالجة.'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'فشل إنشاء الطلب: ' . $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        $company = $request->get('api_company');
        $orders = DB::table('verification_orders')
            ->where('company_id', $company->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['status' => 'success', 'orders' => $orders]);
    }

    public function updateStatus(Request $request, string $id)
    {
        $request->validate(['status' => 'required|in:processing,completed,cancelled']);
        $company = $request->get('api_company');

        $order = DB::table('verification_orders')
            ->where('id', $id)
            ->where('company_id', $company->id)
            ->first();

        if (!$order) return response()->json(['message' => 'الطلب غير موجود'], 404);

        if ($request->status === 'processing' && $order->status === 'pending_payment') {
            DB::table('verification_orders')->where('id', $id)->update(['status' => 'processing']);
            // Trigger Quest Bridge
            \App\Domains\Spatial\Services\QuestBridgeService::bridgeOrderToQuests($id);
        }

        return response()->json(['status' => 'success', 'message' => 'تم تحديث حالة الطلب']);
    }
}
