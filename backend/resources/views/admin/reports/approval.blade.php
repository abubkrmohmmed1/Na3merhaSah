@extends('layouts.admin')

@section('title', 'اعتماد الادارة')
@section('page_title', 'اعتماد الادارة: الشكوى: ' . substr($report->id, 0, 5))

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
        align-items: center;
        margin-bottom: 15px;
    }

    .info-label {
        font-weight: 800;
        color: #2B3674;
        font-size: 14px;
        width: 150px;
        flex-shrink: 0;
    }

    .info-value {
        color: #2B3674;
        font-size: 14px;
        font-weight: 600;
    }

    .btn-small {
        background-color: #F3F4F6;
        color: #6B7280;
        border: 1px solid #E5E7EB;
        border-radius: 20px;
        padding: 4px 12px;
        font-size: 12px;
        font-weight: 600;
        margin-right: 10px;
        cursor: pointer;
    }

    .images-grid {
        display: flex;
        gap: 15px;
        margin-top: 15px;
    }

    .images-grid img {
        width: 80px;
        height: 80px;
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

    .form-input, .form-textarea {
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
        min-height: 100px;
        resize: vertical;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
    }

    .btn-approve {
        background-color: #10B981;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 12px 0;
        font-family: 'Cairo', sans-serif;
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        flex-grow: 1;
        text-align: center;
    }

    .btn-reject {
        background-color: #D1D5DB;
        color: #4B5563;
        border: none;
        border-radius: 8px;
        padding: 12px 0;
        font-family: 'Cairo', sans-serif;
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        flex-grow: 1;
        text-align: center;
    }

    .tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .tab {
        padding: 8px 20px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
    }

    .tab.active {
        background-color: #D3E2FF;
        color: #4B7DF3;
        border: 1px solid #4B7DF3;
    }

    .tab.inactive {
        background-color: white;
        color: #6B7280;
        border: 1px solid #E5E7EB;
    }

</style>
@endsection

@section('content')
    <div class="tabs" style="justify-content: flex-end;">
        <div class="tab inactive">الشكوى</div>
        <div class="tab active">تقرير المساح</div>
    </div>

    <div class="details-grid">
        <!-- Admin Decision (Left side) -->
        <div class="card-box">
            <div class="card-title" style="text-align: center;">قرار الادارة:</div>
            
            <form action="{{ route('admin.reports.approval.update', $report->id) }}" method="POST">
                @csrf
                <div class="radio-group">
                    <div class="radio-item">
                        <input type="radio" id="approve_immediate" name="admin_decision" value="immediate">
                        <label for="approve_immediate">اعتماد الحل الفوري</label>
                    </div>
                    <div class="radio-item">
                        <input type="radio" id="approve_project" name="admin_decision" value="project" checked>
                        <label for="approve_project">اعتماد المشروع المقترح</label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-label">التكلفة المعتمدة: <span style="color:red">*</span></div>
                    <input type="text" name="approved_cost" class="form-input" value="450,000 SDG">
                </div>

                <div class="form-group">
                    <div class="form-label">التاريخ المتوقع للانتهاء:</div>
                    <input type="date" name="estimated_completion" class="form-input" value="2026-06-02">
                </div>

                <div class="form-group">
                    <div class="form-label">ملاحظات الادارة:</div>
                    <textarea name="notes" class="form-textarea" placeholder="تمت الموافقة على المقترح مع مراعاة الانتهاء من التعديل قبل الطوارئ"></textarea>
                </div>

                <div class="action-buttons">
                    <button type="button" class="btn-reject" onclick="history.back()">رجوع</button>
                    <button type="submit" class="btn-approve">اعتماد</button>
                </div>
            </form>
        </div>

        <!-- Surveyor Report (Right side) -->
        <div class="card-box">
            <div class="card-title">تقرير المساح:</div>
            
            @php
                $decisionLabels = [
                    'immediate' => 'اصلاح فوري',
                    'modification' => 'تعديل كلي (إنشاء مشروع)',
                ];
            @endphp

            <div class="info-row">
                <div class="info-label">توصية المساح:</div>
                <div class="info-value">{{ $decisionLabels[$report->surveyor_decision] ?? 'غير محدد' }}</div>
            </div>
            
            <div class="info-row">
                <div class="info-label">المساحة المقترحة للتعديل:</div>
                <div class="info-value" style="display: flex; align-items: center;">
                    {{ $report->surveyor_area ?? '0' }} م
                    <span class="btn-small">مرفقات</span>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">ملاحظات المساح:</div>
                <div class="info-value">
                    {{ $report->surveyor_notes ?? 'لا توجد ملاحظات' }}
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">التاريخ:</div>
                <div class="info-value">{{ $report->first_response_at ? $report->first_response_at->format('d M Y - الساعة h:i a') : 'غير متوفر' }}</div>
            </div>

            <div class="card-title" style="margin-top: 30px; display: flex; justify-content: space-between;">
                الصور المرفقة (بواسطة المساح):
            </div>
            <div class="images-grid">
                @if(!empty($report->surveyor_images))
                    @foreach ($report->surveyor_images as $image)
                        <img src="{{ $image }}" alt="Surveyor Image">
                    @endforeach
                @else
                    <p style="font-size: 12px; color: #9CA3AF;">لم يتم إرفاق صور من قبل المساح.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
