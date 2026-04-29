@extends('layouts.admin')

@section('title', 'عرض الشكاوى')
@section('page_title', 'عرض الشكاوى')

@section('styles')
<style>
    .table-container {
        background-color: white;
        border-radius: 16px;
        padding: 0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        text-align: center;
    }

    th {
        background-color: #F3F4F6;
        padding: 12px;
        font-size: 14px;
        color: #6B7280;
        font-weight: 700;
        border: 1px solid #E5E7EB;
    }

    td {
        padding: 12px;
        font-size: 14px;
        font-weight: 600;
        border: 1px solid #E5E7EB;
        vertical-align: middle;
    }

    tr:hover {
        background-color: #F9FAFB;
    }

    .clickable-row {
        cursor: pointer;
    }

    .priority-low { color: #10B981; }
    .priority-medium { color: #F59E0B; }
    .priority-high { color: #EF4444; }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        display: inline-block;
    }
    .status-started { background-color: #DBEAFE; color: #1E40AF; }
    .status-govt_received { background-color: #E0E7FF; color: #3730A3; }
    .status-surveyor_assigned, .status-site_visited { background-color: #FDE8C4; color: #92400E; }
    .status-engineering_phase, .status-bidding_phase, .status-execution, .status-admin_approval { background-color: #FEE2E2; color: #991B1B; }
    .status-resolved { background-color: #D1F0E0; color: #065F46; }
    .status-external_transfer { background-color: #F3F4F6; color: #374151; }

    /* Image thumbnail styles */
    .report-thumb-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }
    .report-thumb {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        object-fit: cover;
        border: 2px solid #E5E7EB;
        transition: transform 0.2s;
    }
    .report-thumb:hover {
        transform: scale(2.5);
        z-index: 10;
        position: relative;
        border-color: #3B82F6;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
    .img-count-badge {
        background: #3B82F6;
        color: white;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
    }
    .no-image {
        color: #9CA3AF;
        font-size: 12px;
    }

    /* Status Arabic labels */
    @php
    $statusLabels = [
        'started' => 'جديد',
        'govt_received' => 'تم الاستلام',
        'external_transfer' => 'تحويل خارجي',
        'surveyor_assigned' => 'تم تعيين مساح',
        'site_visited' => 'تمت الزيارة',
        'engineering_phase' => 'مرحلة هندسية',
        'bidding_phase' => 'مرحلة المناقصة',
        'execution' => 'قيد التنفيذ',
        'admin_approval' => 'اعتماد إداري',
        'resolved' => 'تم الحل',
    ];
    $categoryLabels = [
        1 => 'مياه',
        2 => 'كهرباء',
        3 => 'طرق',
        4 => 'صرف صحي',
        5 => 'مباني',
        6 => 'طوارئ',
    ];
    @endphp
</style>
@endsection

@section('content')
    <div style="margin-bottom: 20px; display: flex; gap: 10px; overflow-x: auto; padding-bottom: 10px;">
        @php
            $statuses = [
                'all' => 'الكل',
                'started' => 'جديد',
                'surveyor_assigned' => 'تحت المعاينة',
                'admin_approval' => 'بانتظار الاعتماد',
                'execution' => 'قيد التنفيذ',
                'resolved' => 'تم الحل'
            ];
        @endphp
        @foreach($statuses as $slug => $name)
            <a href="{{ route('admin.reports.index', ['status' => $slug]) }}" 
               style="text-decoration: none; padding: 10px 20px; border-radius: 12px; font-weight: 700; font-size: 14px; white-space: nowrap; transition: all 0.3s; 
                      {{ $currentStatus == $slug ? 'background: #4B7DF3; color: white; box-shadow: 0 4px 12px rgba(75, 125, 243, 0.3);' : 'background: white; color: #718096; border: 1px solid #E5E7EB;' }}">
                {{ $name }}
            </a>
        @endforeach
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>رقم الشكوى</th>
                    <th>الصورة</th>
                    <th>النوع</th>
                    <th>الوصف</th>
                    <th>الموقع</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reports as $report)
                    <tr class="clickable-row" onclick="window.location='{{ route('admin.reports.show', $report->id) }}'">
                        <td>{{ substr($report->id, 0, 5) }}</td>
                        <td>
                            @php
                                $images = is_array($report->images) ? $report->images : json_decode($report->images ?? '[]', true);
                            @endphp
                            @if(!empty($images))
                                <div class="report-thumb-wrapper">
                                    <img src="{{ $images[0] }}" alt="صورة البلاغ" class="report-thumb" onerror="this.style.display='none'">
                                    @if(count($images) > 1)
                                        <span class="img-count-badge">+{{ count($images) - 1 }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="no-image">بدون صور</span>
                            @endif
                        </td>
                        <td>{{ $categoryLabels[$report->category_id] ?? 'أخرى' }}</td>
                        <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ Str::limit($report->description, 40) }}
                        </td>
                        <td>{{ $report->address ? ($report->address->neighborhood ?? $report->address->address_str) : 'غير محدد' }}</td>
                        <td>
                            <span class="status-badge status-{{ $report->status }}">
                                {{ $statusLabels[$report->status] ?? $report->status }}
                            </span>
                        </td>
                        <td style="font-size: 12px; color: #6B7280;">{{ $report->created_at?->format('Y/m/d') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">لا توجد بلاغات لعرضها.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($reports->hasPages())
        <div style="padding: 20px; display: flex; justify-content: center;">
            {{ $reports->links() }}
        </div>
        @endif
    </div>
@endsection
