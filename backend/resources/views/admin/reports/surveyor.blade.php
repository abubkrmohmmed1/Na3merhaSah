@extends('layouts.admin')

@section('title', 'تقرير المساح')
@section('page_title', 'تقرير المساح: ' . substr($report->id, 0, 5))

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
        height: 100%;
        min-height: 400px;
        border-radius: 12px;
        overflow: hidden;
    }

    .map-container img {
        width: 100%;
        height: 100%;
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

    .input-group {
        display: flex;
        align-items: center;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        background-color: #FAFCFE;
        overflow: hidden;
    }

    .input-group input {
        border: none;
        background: none;
        flex-grow: 1;
        padding: 12px 15px;
        outline: none;
    }

    .input-group .addon {
        padding: 12px 15px;
        color: #6B7280;
        font-weight: 600;
        background-color: #F3F4F6;
        border-right: 1px solid #E5E7EB;
    }

    .images-grid {
        display: flex;
        gap: 15px;
        margin-top: 15px;
        margin-bottom: 25px;
    }

    .images-grid img {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        object-fit: cover;
    }

    .add-image-btn {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        border: 2px dashed #E5E7EB;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4B7DF3;
        font-size: 24px;
        cursor: pointer;
        background-color: #FAFCFE;
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
    @if(session('success'))
        <div style="background-color: #D1F0E0; color: #065F46; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 700; text-align: center;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background-color: #FEE2E2; color: #991B1B; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <ul style="margin: 0; padding-right: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="details-grid">
        <!-- Map Column (Left side) -->
        <div class="card-box" style="padding: 10px;">
            <div class="map-container" id="surveyorMap">
            </div>
        </div>

        <!-- Surveyor Report Form Column (Right side) -->
        <div class="card-box">
            <form id="surveyorForm" action="{{ route('admin.reports.surveyor.update', $report->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <div class="form-label" style="text-align: center;">قرار المساح:</div>
                    <div class="radio-group">
                        <div class="radio-item">
                            <input type="radio" id="immediate_repair" name="surveyor_decision" value="immediate">
                            <label for="immediate_repair">اصلاح فوري</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="create_modification" name="surveyor_decision" value="modification" checked>
                            <label for="create_modification">تم استكمال الفاتورة انشاء التعديل الاتي</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-label">المساحة المقترحة للتعديل:</div>
                    <div class="input-group">
                        <input type="number" name="area" value="120" placeholder="120">
                        <span class="addon">متر</span>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-label">تقرير المساح:</div>
                    <textarea class="form-textarea" name="report" placeholder="ملاحظات...">تم كشف التسرب في خط المياه الرئيسي ويجب تغييره بالكامل</textarea>
                </div>

                <div class="form-group">
                    <div class="form-label">ارفاق صور:</div>
                    <div class="images-grid" id="imagePreviewContainer">
                        @if($report->surveyor_images)
                            @foreach($report->surveyor_images as $image)
                                <img src="{{ $image }}" alt="Existing Image">
                            @endforeach
                        @endif
                        <label for="surveyor_images" class="add-image-btn" style="display: flex;">
                            <span>+</span>
                            <input type="file" id="surveyor_images" name="surveyor_images[]" multiple accept="image/*" style="display: none;">
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-primary" id="submitBtn">ارسال التقرير</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form Loading State
        const form = document.getElementById('surveyorForm');
        const submitBtn = document.getElementById('submitBtn');
        
        form.addEventListener('submit', function() {
            submitBtn.innerHTML = 'جاري الإرسال...';
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.7';
            submitBtn.style.cursor = 'not-allowed';
        });
        var lat = {{ $report->location_lat ?? 15.6545 }};
        var lng = {{ $report->location_lng ?? 32.4831 }};

        var map = L.map('surveyorMap').setView([lat, lng], 15);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
        }).addTo(map);

        L.marker([lat, lng]).addTo(map)
            .bindPopup("<b>الشكوى: {{ substr($report->id, 0, 5) }}</b>").openPopup();

        // Image Preview Script
        const imageInput = document.getElementById('surveyor_images');
        const previewContainer = document.getElementById('imagePreviewContainer');
        const addButton = previewContainer.querySelector('label');

        imageInput.addEventListener('change', function() {
            // Remove existing previews (optional: keep old ones if desired, but here we replace)
            const existingPreviews = previewContainer.querySelectorAll('.new-preview');
            existingPreviews.forEach(p => p.remove());

            if (this.files) {
                Array.from(this.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.classList.add('new-preview');
                        img.style.width = '80px';
                        img.style.height = '80px';
                        img.style.borderRadius = '8px';
                        img.style.objectFit = 'cover';
                        previewContainer.insertBefore(img, addButton);
                    }
                    reader.readAsDataURL(file);
                });
            }
        });
    });
</script>
@endsection
