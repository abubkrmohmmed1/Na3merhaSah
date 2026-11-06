@extends('company.layout')

@section('title', 'أمثلة التكامل (Code Examples)')
@section('page_title', 'أمثلة التكامل (Integration Code)')

@section('content')
<div class="card" style="border-left: 4px solid #0078d4;">
    <h2 style="margin-top: 0; font-size: 18px; color: #323130;">ابدأ التكامل في دقائق</h2>
    <p style="color: #605e5c; margin-bottom: 0;">انسخ الكود المناسب للغة البرمجة التي تستخدمها وجرّب واجهة برمجة التطبيقات فوراً. مفتاح الـ API الخاص بك مدمج تلقائياً في الأمثلة.</p>
</div>

@php
$endpoints = [
    'الولايات (States)' => [
        'method' => 'GET',
        'url' => '/api/v1/b2b/spatial/states',
        'desc' => 'جلب قائمة الولايات (المستوى الإداري الأول) مع المعرفات.',
    ],
    'المحليات (Localities)' => [
        'method' => 'GET',
        'url' => '/api/v1/b2b/spatial/localities?state_id={state_id}',
        'desc' => 'جلب المحليات، يمكن تصفيتها بواسطة معرف الولاية.',
    ],
    'الأحياء (Neighborhoods)' => [
        'method' => 'GET',
        'url' => '/api/v1/b2b/spatial/neighborhoods?locality_id={locality_id}',
        'desc' => 'جلب الأحياء السكنية والقرى داخل محلية معينة.',
    ],
    'بحث جغرافي دائري (Radial Search)' => [
        'method' => 'GET',
        'url' => '/api/v1/b2b/spatial/radius?lat=15.5871&lng=32.5232&radius=1000&limit=10',
        'desc' => 'ابحث عن المعالم الجغرافية ضمن نطاق دائري محدد (بالأمتار).',
    ],
    'ترميز جغرافي عكسي (Reverse Geocoding)' => [
        'method' => 'GET',
        'url' => '/api/v1/b2b/addresses/reverse?lat=15.5871&lng=32.5232',
        'desc' => 'حول الإحداثيات المكانية إلى عنوان مقروء مع توليد S2 Token.',
    ],
    'تصدير البيانات (GeoJSON Export)' => [
        'method' => 'GET',
        'url' => '/api/v1/b2b/export?layer=الأحياء',
        'desc' => 'تصدير طبقة مكانية كاملة بصيغة GeoJSON القياسية.',
    ],
];
@endphp

<div class="grid grid-cols-1 gap-4">
@foreach($endpoints as $name => $ep)
<div class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 16px 20px; border-bottom: 1px solid #edebe9; background: #faf9f8;">
        <h3 style="margin: 0 0 4px; display: flex; align-items: center; gap: 8px; font-size: 15px; color: #323130;">
            <span style="padding: 2px 6px; border-radius: 2px; font-size: 11px; background: #0078d4; color: white; font-weight: 600;">{{ $ep['method'] }}</span>
            {{ $name }}
        </h3>
        <p style="color: #605e5c; font-size: 13px; margin: 0;">{{ $ep['desc'] }}</p>
    </div>
    
    <div style="background: #1b1b1b;">
        <div style="border-bottom: 1px solid #323130; padding: 0 16px; display: flex; gap: 16px;" id="tabs-{{ $loop->index }}">
            <!-- Tabs injected by JS -->
        </div>
        <div style="padding: 16px; overflow-x: auto;">
            <pre id="code-body-{{ $loop->index }}" style="color: #d4d4d4; font-size: 13px; direction: ltr; margin: 0; font-family: 'Consolas', monospace;"></pre>
        </div>
    </div>
</div>
@endforeach
</div>

<script>
const apiKey = '{{ $apiKey->key }}';
const baseUrl = window.location.origin;
const endpoints = @json(array_values($endpoints));
const languages = ['cURL', 'PHP (Guzzle)', 'JavaScript (Fetch)', 'Python (Requests)'];

const examples = {
    'cURL': (url, method) => `curl -X ${method} "${baseUrl}${url}" \\
  -H "X-API-Key: ${apiKey}"`,
    'PHP (Guzzle)': (url, method) => `$client = new \\GuzzleHttp\\Client();
$response = $client->request('${method}', '${baseUrl}${url}', [
    'headers' => [
        'X-API-Key' => '${apiKey}'
    ]
]);
$data = json_decode($response->getBody(), true);`,
    'JavaScript (Fetch)': (url, method) => `fetch('${baseUrl}${url}', {
    method: '${method}',
    headers: {
        'X-API-Key': '${apiKey}'
    }
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));`,
    'Python (Requests)': (url, method) => `import requests

url = "${baseUrl}${url}"
headers = {
    "X-API-Key": "${apiKey}"
}

response = requests.${method === 'GET' ? 'get' : 'post'}(url, headers=headers)
data = response.json()
print(data)`,
};

document.querySelectorAll('[id^="tabs-"]').forEach((el, i) => {
    el.innerHTML = languages.map(lang =>
        `<button onclick="event.preventDefault();showLang(${i},'${lang}')" 
            style="background:transparent; border:none; border-bottom: 2px solid transparent; color:#8a8886; padding:12px 0; cursor:pointer; font-size:12px; font-family:inherit; transition:all 0.1s;" 
            data-lang="${lang}">${lang}</button>`
    ).join('');
    showLang(i, 'cURL');
});

function showLang(i, lang) {
    const ep = endpoints[i];
    const code = examples[lang](ep.url, ep.method);
    
    const tabs = document.querySelectorAll(`#tabs-${i} button`);
    tabs.forEach(t => {
        t.style.color = '#8a8886';
        t.style.borderColor = 'transparent';
    });
    
    const activeTab = document.querySelector(`#tabs-${i} button[data-lang="${lang}"]`);
    activeTab.style.color = '#ffffff';
    activeTab.style.borderColor = '#0078d4';
    
    document.getElementById(`code-body-${i}`).textContent = code;
}
</script>
@endsection
