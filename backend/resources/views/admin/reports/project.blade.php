@extends('layouts.admin')

@section('title', 'تقارير المشروع')
@section('page_title', 'تقارير المشروع للشكوى : ' . substr($report->id, 0, 5))

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

    .map-container {
        width: 100%;
        height: 200px;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .map-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
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

    .progress-section {
        margin-bottom: 30px;
    }

    .progress-label {
        font-weight: 800;
        color: #2B3674;
        font-size: 14px;
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .progress-bar {
        width: 100%;
        height: 10px;
        background-color: #E5E7EB;
        border-radius: 5px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background-color: #4B7DF3;
        width: 70%; /* Dynamic */
        border-radius: 5px;
    }

    .tasks-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
        margin-bottom: 30px;
    }

    .task-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .task-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .task-left input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #10B981;
    }

    .task-left label {
        font-weight: 600;
        color: #2B3674;
        font-size: 14px;
    }

    .task-right {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .task-date {
        color: #6B7280;
        font-size: 12px;
        font-weight: 600;
        width: 60px;
    }

    .btn-attachment {
        background-color: white;
        color: #6B7280;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        padding: 4px 15px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
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
        text-align: right;
    }

    .form-textarea {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        font-family: 'Cairo', sans-serif;
        font-size: 14px;
        color: #2B3674;
        background-color: #FAFCFE;
        box-sizing: border-box;
        min-height: 100px;
        resize: vertical;
    }

    .btn-complete {
        background-color: #10B981;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 12px 0;
        font-family: 'Cairo', sans-serif;
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        width: 100%;
        text-align: center;
    }

    .btn-complete:hover {
        background-color: #059669;
    }
</style>
@endsection

@section('content')
    <div class="details-grid">
        <!-- Project Info Column (Left side) -->
        <div class="card-box">
            <div class="map-container" id="projectMap">
            </div>

            <div style="margin-top: 30px;">
                <div class="info-row">
                    <div class="info-label">نوع المشروع:</div>
                    <div class="info-value">{{ $report->category_id == 1 ? 'استبدال خط مياه' : 'مشروع صيانة' }}</div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">تكلفة المشروع:</div>
                    <div class="info-value">450,000 SDG</div>
                </div>

                <div class="info-row">
                    <div class="info-label">الاولوية:</div>
                    <div class="info-value">عالي</div>
                </div>

                <div style="margin-top: 40px;">
                    <div class="info-row">
                        <div class="info-label">المهندس المسؤول:</div>
                        <div class="info-value">م. محمد علي</div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">مهندس الاعتماد:</div>
                        <div class="info-value">م. حسن عبدالله</div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">مشرف الموقع:</div>
                        <div class="info-value">م. أحمد علي</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Column (Right side) -->
        <div class="card-box">
            <div class="progress-section">
                <div class="progress-label">
                    <span>نسبة انجاز المشروع</span>
                    <span>70%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
            </div>

            <div class="tasks-list">
                <div class="task-item">
                    <div class="task-left">
                        <input type="checkbox" id="task1" checked>
                        <label for="task1">تجهيز الموقع</label>
                    </div>
                    <div class="task-right">
                        <span class="task-date">23 ابريل</span>
                        <button class="btn-attachment">مرفقات</button>
                    </div>
                </div>

                <div class="task-item">
                    <div class="task-left">
                        <input type="checkbox" id="task2" checked>
                        <label for="task2">استلام المواد</label>
                    </div>
                    <div class="task-right">
                        <span class="task-date">26 ابريل</span>
                        <button class="btn-attachment">مرفقات</button>
                    </div>
                </div>

                <div class="task-item">
                    <div class="task-left">
                        <input type="checkbox" id="task3" checked>
                        <label for="task3">استبدال الخط</label>
                    </div>
                    <div class="task-right">
                        <span class="task-date">12 مايو</span>
                        <button class="btn-attachment">مرفقات</button>
                    </div>
                </div>

                <div class="task-item">
                    <div class="task-left">
                        <input type="checkbox" id="task4">
                        <label for="task4">اختبار الخط الجديد</label>
                    </div>
                    <div class="task-right">
                        <span class="task-date">16 مايو</span>
                        <button class="btn-attachment">مرفقات</button>
                    </div>
                </div>

                <div class="task-item">
                    <div class="task-left">
                        <input type="checkbox" id="task5">
                        <label for="task5">التسليم النهائي</label>
                    </div>
                    <div class="task-right">
                        <span class="task-date">21 مايو</span>
                        <button class="btn-attachment">مرفقات</button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="form-label">التقرير النهائي</div>
                <textarea class="form-textarea"></textarea>
            </div>

            <button type="button" class="btn-complete">مكتمل</button>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var lat = {{ $report->location_lat ?? 15.6545 }};
        var lng = {{ $report->location_lng ?? 32.4831 }};

        var map = L.map('projectMap').setView([lat, lng], 15);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
        }).addTo(map);

        L.marker([lat, lng]).addTo(map)
            .bindPopup("<b>الشكوى: {{ substr($report->id, 0, 5) }}</b>").openPopup();
    });
</script>
@endsection
