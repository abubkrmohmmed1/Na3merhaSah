@extends('layouts.admin')

@section('title', 'تفاصيل الشكوى')
@section('page_title', 'تفاصيل الشكوى: ' . substr($report->id, 0, 5))

@section('styles')
<style>
    .details-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }

    .card-box {
        background-color: white;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .card-title {
        color: #2B3674;
        font-weight: 800;
        font-size: 16px;
        margin-bottom: 25px;
        text-align: right;
    }

    .info-row {
        display: flex;
        justify-content: flex-start;
        align-items: flex-start;
        margin-bottom: 15px;
    }

    .info-label {
        font-weight: 800;
        color: #2B3674;
        font-size: 14px;
        width: 120px;
        flex-shrink: 0;
    }

    .info-value {
        color: #2B3674;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.6;
    }

    .images-grid {
        display: flex;
        gap: 15px;
        margin-top: 15px;
    }

    .images-grid img {
        width: 100px;
        height: 100px;
        border-radius: 8px;
        object-fit: cover;
    }

    .radio-group {
        display: flex;
        flex-direction: column;
        gap: 15px;
        margin-bottom: 25px;
    }

    .radio-item {
        display: flex;
        align-items: center;
        gap: 10px;
        justify-content: flex-start;
    }

    .radio-item label {
        font-weight: 600;
        color: #2B3674;
        font-size: 14px;
        cursor: pointer;
    }

    .radio-item input[type="radio"] {
        accent-color: #4B7DF3;
        width: 18px;
        height: 18px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-weight: 800;
        color: #2B3674;
        font-size: 14px;
        margin-bottom: 10px;
    }

    .form-select, .form-textarea {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        font-family: 'Cairo', sans-serif;
        font-size: 14px;
        color: #2B3674;
        background-color: #FAFCFE;
        box-sizing: border-box;
    }

    .form-textarea {
        min-height: 120px;
        resize: vertical;
    }

    .btn-primary {
        background-color: #4B7DF3;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 12px 30px;
        font-family: 'Cairo', sans-serif;
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        width: 100%;
        transition: background-color 0.3s;
    }

    .btn-primary:hover {
        background-color: #3b66cf;
    }

</style>
@endsection

@section('content')
    <!-- Workflow Tracker -->
    <div class="card-box" style="margin-bottom: 25px; padding: 20px;">
        <div style="display: flex; justify-content: space-between; position: relative;">
            @php
                $steps = [
                    'started' => 'استلام البلاغ',
                    'surveyor_assigned' => 'المعاينة الفنية',
                    'admin_approval' => 'الاعتماد الإداري',
                    'execution' => 'مرحلة التنفيذ',
                    'resolved' => 'تم الحل'
                ];
                $currentStepIndex = array_search($report->status, array_keys($steps));
                if ($currentStepIndex === false) $currentStepIndex = 0;
            @endphp
            @foreach($steps as $status => $label)
                @php
                    $index = array_search($status, array_keys($steps));
                    $isCompleted = $index < $currentStepIndex || $report->status == 'resolved';
                    $isActive = $status == $report->status;
                @endphp
                <div style="flex: 1; text-align: center; position: relative; z-index: 1;">
                    <div style="width: 30px; height: 30px; border-radius: 50%; background: {{ $isCompleted ? '#10B981' : ($isActive ? '#4B7DF3' : '#E5E7EB') }}; color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-weight: bold; font-size: 14px;">
                        @if($isCompleted) ✓ @else {{ $index + 1 }} @endif
                    </div>
                    <div style="font-size: 12px; font-weight: 700; color: {{ $isActive ? '#4B7DF3' : '#718096' }};">{{ $label }}</div>
                </div>
            @endforeach
            <!-- Connector Line -->
            <div style="position: absolute; top: 15px; left: 10%; right: 10%; height: 2px; background: #E5E7EB; z-index: 0;"></div>
        </div>
    </div>

    <div class="details-grid">
        <!-- Review/Action Column (Left side) -->
        <div class="card-box">
            @if($report->status == 'started')
                <div class="card-title" style="text-align: center;">تحويل البلاغ للمرحلة القادمة:</div>
                <form action="{{ route('admin.reports.update', $report->id) }}" method="POST">
                    @csrf
                    <div class="radio-group">
                        <div class="radio-item">
                            <input type="radio" id="refer_surveyor" name="decision" value="surveyor" checked>
                            <label for="refer_surveyor">تحال للمساح لكتابة تقرير فني</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="refer_other" name="decision" value="other">
                            <label for="refer_other">تحال الى جهة خدمية اخرى (خارج الاختصاص)</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-label">ملاحظات إضافية:</div>
                        <textarea class="form-textarea" name="notes" placeholder="اكتب تعليماتك هنا..."></textarea>
                    </div>
                    <button type="submit" class="btn-primary">تأكيد التحويل</button>
                </form>
            @elseif($report->status == 'surveyor_assigned')
                <div style="text-align: center; padding: 40px 0;">
                    <div style="font-size: 40px; margin-bottom: 20px;">📋</div>
                    <h3 style="color: #2B3674;">بانتظار تقرير المساح</h3>
                    <p style="color: #718096; font-size: 14px;">تم تحويل البلاغ للمعاينة الميدانية. سيظهر التقرير هنا فور إرساله من قبل المساح.</p>
                    <a href="{{ route('admin.reports.surveyor', $report->id) }}" style="color: #4B7DF3; font-size: 13px; text-decoration: none; font-weight: 700;">عرض رابط المساح (للاختبار) ←</a>
                </div>
            @elseif($report->status == 'admin_approval')
                <div class="card-title" style="text-align: center;">مراجعة تقرير المعاينة:</div>
                <div style="background: #F8FAFC; padding: 15px; border-radius: 12px; margin-bottom: 20px;">
                    <div class="info-row">
                        <div class="info-label">قرار المساح:</div>
                        <div class="info-value" style="color: #10B981;">{{ $report->surveyor_decision == 'repair' ? 'تطلب إصلاح فوري' : 'بلاغ وهمي/غير موجود' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">وصف المشكلة:</div>
                        <div class="info-value">{{ $report->surveyor_notes }}</div>
                    </div>
                </div>
                <form action="{{ route('admin.reports.approval.update', $report->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <div class="form-label">قرار الاعتماد:</div>
                        <select class="form-select" name="decision">
                            <option value="approved">اعتماد وبدء التنفيذ</option>
                            <option value="rejected">رفض البلاغ وإغلاقه</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary">حفظ القرار</button>
                </form>
            @elseif($report->status == 'execution')
                <div style="text-align: center; padding: 40px 0;">
                    <div style="font-size: 40px; margin-bottom: 20px;">🏗️</div>
                    <h3 style="color: #2B3674;">البلاغ قيد التنفيذ</h3>
                    <p style="color: #718096; font-size: 14px;">العمل جاري الآن على حل المشكلة ميدانياً.</p>
                    <form action="{{ route('admin.reports.project.update', $report->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="resolved">
                        <button type="submit" class="btn-primary" style="background: #10B981;">تأكيد اكتمال الحل</button>
                    </form>
                </div>
            @elseif($report->status == 'resolved')
                <div class="card-title" style="text-align: center;">تقييم رضا المواطن:</div>
                @if($report->user_rating)
                    <div style="text-align: center;">
                        <div style="font-size: 30px; color: #F6AD55; margin-bottom: 10px;">
                            @for($i=1; $i<=5; $i++)
                                @if($i <= $report->user_rating) ★ @else ☆ @endif
                            @endfor
                        </div>
                        <div style="background: #F0FDF4; padding: 15px; border-radius: 12px; color: #166534; font-size: 14px; font-weight: 600;">
                            "{{ $report->user_feedback ?? 'تم حل المشكلة بنجاح، شكراً لكم' }}"
                        </div>
                    </div>
                @else
                    <p style="text-align: center; color: #718096; font-size: 14px;">لم يتم تقديم تقييم بعد.</p>
                @endif
            @endif
        </div>

        <!-- Complaint Data Column (Right side) -->
        <div class="card-box">
            <div class="card-title">بيانات الشكوى الأصلية:</div>
            
            <div class="info-row">
                <div class="info-label">نوع المشكلة:</div>
                <div class="info-value">@php
                    $categories = [1 => 'مياه', 2 => 'كهرباء', 3 => 'طرق', 4 => 'صرف صحي', 5 => 'مباني', 6 => 'طوارئ'];
                    echo $categories[$report->category_id] ?? 'أخرى';
                @endphp</div>
            </div>
            
            <div class="info-row">
                <div class="info-label">عنوان الموقع:</div>
                <div class="info-value">{{ $report->address ? $report->address->address_str : 'غير محدد' }}</div>
            </div>

            <div class="info-row">
                <div class="info-label">تاريخ البلاغ:</div>
                <div class="info-value">{{ $report->created_at ? $report->created_at->format('d M Y الساعة h:i a') : '-' }}</div>
            </div>

            <div class="info-row">
                <div class="info-label">وصف المبلغ:</div>
                <div class="info-value">{{ $report->description }}</div>
            </div>

            <div class="card-title" style="margin-top: 30px;">الصور المرفقة:</div>
            <div class="images-grid">
                @php $images = is_array($report->images) ? $report->images : json_decode($report->images ?? '[]', true); @endphp
                @foreach ($images as $image)
                    <img src="{{ $image }}" alt="Report Image" style="cursor: pointer;" onclick="window.open(this.src)">
                @endforeach
            </div>
        </div>
    </div>
@endsection
