<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Domains\Addressing\Models\AddressSubmission;
use App\Domains\Spatial\Models\BoundaryProposal;
use App\Domains\Addressing\Actions\SubmitAddressAction;
use App\Domains\Spatial\Actions\SubmitBoundaryProposalAction;
use OpenApi\Attributes as OA;

class SubmissionsController extends Controller
{
    public function __construct(
        private readonly SubmitAddressAction $submitAddress,
        private readonly SubmitBoundaryProposalAction $submitProposal,
    ) {}

    #[OA\Post(
        path: '/api/v1/submissions/address',
        operationId: 'submitAddress',
        summary: 'تقديم عنوان جديد',
        description: 'مستخدم يضيف عنوان شخصي أو عنوان خدمة جديد.',
        tags: ['Submissions']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'address_str', 'type', 'lat', 'lng'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'صيدلية النور'),
                new OA\Property(property: 'address_str', type: 'string', example: 'شارع الجامعة، أم درمان'),
                new OA\Property(property: 'neighborhood', type: 'string', example: 'الرياض'),
                new OA\Property(property: 'type', type: 'string', enum: ['personal', 'service']),
                new OA\Property(property: 'service_layer', type: 'string', example: 'الصيدليات'),
                new OA\Property(property: 'lat', type: 'number', example: 15.5871),
                new OA\Property(property: 'lng', type: 'number', example: 32.5232),
                new OA\Property(property: 'phone', type: 'string'),
                new OA\Property(property: 'notes', type: 'string')
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'تم استلام العنوان بنجاح')]
    #[OA\Response(response: 401, description: 'غير مصرح')]
    #[OA\Response(response: 422, description: 'بيانات غير صحيحة')]
    public function submitAddress(Request $request)
    {
        $submission = $this->submitAddress->execute($request);

        return response()->json([
            'message' => 'تم استلام العنوان بنجاح وهو قيد المراجعة',
            'submission_id' => $submission->id,
        ], 201);
    }

    #[OA\Post(
        path: '/api/v1/submissions/neighborhood',
        operationId: 'submitNeighborhood',
        summary: 'اقتراح حي جديد',
        tags: ['Submissions']
    )]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['name', 'lat', 'lng'],
        properties: [
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'lat', type: 'number'),
            new OA\Property(property: 'lng', type: 'number'),
            new OA\Property(property: 'description', type: 'string'),
            new OA\Property(property: 'phone', type: 'string')
        ]
    ))]
    #[OA\Response(response: 201, description: 'تم استلام الاقتراح')]
    public function submitNeighborhood(Request $request)
    {
        $proposal = $this->submitProposal->submitNeighborhood($request);

        return response()->json([
            'message' => 'تم استلام اقتراح الحي بنجاح وهو قيد المراجعة',
            'proposal_id' => $proposal->id,
        ], 201);
    }

    #[OA\Post(
        path: '/api/v1/submissions/village',
        operationId: 'submitVillage',
        summary: 'اقتراح قرية جديدة',
        tags: ['Submissions']
    )]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ['name', 'lat', 'lng'],
        properties: [
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'lat', type: 'number'),
            new OA\Property(property: 'lng', type: 'number'),
            new OA\Property(property: 'description', type: 'string'),
            new OA\Property(property: 'phone', type: 'string')
        ]
    ))]
    #[OA\Response(response: 201, description: 'تم استلام الاقتراح')]
    public function submitVillage(Request $request)
    {
        $proposal = $this->submitProposal->submitVillage($request);

        return response()->json([
            'message' => 'تم استلام اقتراح القرية بنجاح وهو قيد المراجعة',
            'proposal_id' => $proposal->id,
        ], 201);
    }

    #[OA\Get(
        path: '/api/v1/submissions/my',
        operationId: 'mySubmissions',
        summary: 'تقديماتي',
        tags: ['Submissions']
    )]
    #[OA\Response(response: 200, description: 'قائمة التقديمات')]
    public function mySubmissions(Request $request)
    {
        $userId = Auth::id();

        return response()->json([
            'addresses' => AddressSubmission::where('user_id', $userId)
                ->orderBy('created_at', 'desc')->get(),
            'proposals' => BoundaryProposal::where('user_id', $userId)
                ->orderBy('created_at', 'desc')->get(),
        ]);
    }

    #[OA\Post(
        path: '/api/v1/admin/submissions/address/{id}/approve',
        operationId: 'approveSubmission',
        summary: 'الموافقة على عنوان',
        description: 'الموافقة على تقديم عنوان وإضافته إلى قاعدة البيانات الجغرافية.',
        tags: ['Admin Submissions'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'معرف التقديم', schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'تمت الموافقة على العنوان')]
    #[OA\Response(response: 403, description: 'غير مصرح')]
    #[OA\Response(response: 404, description: 'لم يتم العثور على التقديم')]
    #[OA\Response(response: 422, description: 'تم مراجعة هذا العنوان سابقاً')]
    public function approveSubmission(string $id)
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && $user->role !== 'surveyor') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $submission = AddressSubmission::findOrFail($id);

        if ($submission->status !== 'pending') {
            return response()->json(['message' => 'تم مراجعة هذا العنوان سابقاً'], 422);
        }

        $address = $this->submitAddress->approve($submission);

        return response()->json([
            'message' => 'تمت الموافقة على العنوان',
            'address_id' => $address->id,
        ]);
    }

    #[OA\Post(
        path: '/api/v1/admin/submissions/address/{id}/reject',
        operationId: 'rejectSubmission',
        summary: 'رفض عنوان',
        description: 'رفض تقديم عنوان مع ذكر سبب الرفض.',
        tags: ['Admin Submissions'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'معرف التقديم', schema: new OA\Schema(type: 'string'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['reason'],
            properties: [
                new OA\Property(property: 'reason', type: 'string', example: 'معلومات ناقصة')
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'تم رفض العنوان')]
    #[OA\Response(response: 403, description: 'غير مصرح')]
    #[OA\Response(response: 404, description: 'لم يتم العثور على التقديم')]
    public function rejectSubmission(Request $request, string $id)
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && $user->role !== 'surveyor') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $request->validate(['reason' => 'required|string|max:500']);

        $submission = AddressSubmission::findOrFail($id);

        if ($submission->status !== 'pending') {
            return response()->json(['message' => 'تم مراجعة هذا العنوان سابقاً'], 422);
        }

        $this->submitAddress->reject($submission, $request->reason);

        return response()->json(['message' => 'تم رفض العنوان']);
    }

    #[OA\Post(
        path: '/api/v1/admin/submissions/proposal/{id}/approve',
        operationId: 'approveProposal',
        summary: 'الموافقة على اقتراح (حي/قرية)',
        description: 'الموافقة على اقتراح حدود حي أو قرية.',
        tags: ['Admin Submissions'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'معرف الاقتراح', schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'تمت الموافقة على الاقتراح')]
    #[OA\Response(response: 403, description: 'غير مصرح')]
    #[OA\Response(response: 404, description: 'لم يتم العثور على الاقتراح')]
    public function approveProposal(string $id)
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && $user->role !== 'surveyor') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $proposal = BoundaryProposal::findOrFail($id);

        if ($proposal->status !== 'pending') {
            return response()->json(['message' => 'تم مراجعة هذا الاقتراح سابقاً'], 422);
        }

        $feature = $this->submitProposal->approve($proposal);

        return response()->json([
            'message' => 'تمت الموافقة على الاقتراح',
            'feature_id' => $feature->id,
        ]);
    }

    #[OA\Post(
        path: '/api/v1/admin/submissions/proposal/{id}/reject',
        operationId: 'rejectProposal',
        summary: 'رفض اقتراح (حي/قرية)',
        description: 'رفض اقتراح حدود حي أو قرية مع ذكر سبب الرفض.',
        tags: ['Admin Submissions'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'معرف الاقتراح', schema: new OA\Schema(type: 'string'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['reason'],
            properties: [
                new OA\Property(property: 'reason', type: 'string', example: 'البيانات غير كافية')
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'تم رفض الاقتراح')]
    #[OA\Response(response: 403, description: 'غير مصرح')]
    #[OA\Response(response: 404, description: 'لم يتم العثور على الاقتراح')]
    public function rejectProposal(Request $request, string $id)
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && $user->role !== 'surveyor') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $request->validate(['reason' => 'required|string|max:500']);

        $proposal = BoundaryProposal::findOrFail($id);

        if ($proposal->status !== 'pending') {
            return response()->json(['message' => 'تم مراجعة هذا الاقتراح سابقاً'], 422);
        }

        $this->submitProposal->reject($proposal, $request->reason);

        return response()->json(['message' => 'تم رفض الاقتراح']);
    }

    #[OA\Get(
        path: '/api/v1/admin/submissions/pending/count',
        operationId: 'pendingCount',
        summary: 'عدد التقديمات بانتظار المراجعة',
        tags: ['Admin Submissions']
    )]
    #[OA\Response(response: 200, description: 'Counts')]
    #[OA\Response(response: 403, description: 'غير مصرح')]
    public function pendingCount()
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && $user->role !== 'surveyor') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        return response()->json([
            'pending_addresses' => AddressSubmission::pending()->count(),
            'pending_neighborhoods' => BoundaryProposal::pending()->neighborhoods()->count(),
            'pending_villages' => BoundaryProposal::pending()->villages()->count(),
        ]);
    }
}