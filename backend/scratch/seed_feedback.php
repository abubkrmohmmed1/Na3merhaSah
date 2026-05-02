<?php

use App\Domains\Reporting\Models\Report;

// Find resolved reports and add sample feedback
$reports = Report::where('status', 'resolved')->get();

if ($reports->isEmpty()) {
    echo "No resolved reports found to seed feedback.\n";
    return;
}

$qualities = ['ممتاز', 'جيد', 'ضعيف'];
$times = ['في الموعد', 'متأخر', 'مبكر'];
$behaviors = ['محترم', 'عادي', 'غير لائق'];
$cleans = ['نظيف', 'مقبول', 'سيء'];
$issues = ['تأخير', 'سوء تنفيذ', 'تعامل سيء', 'لم يتم الحل'];

foreach ($reports as $report) {
    $report->update([
        'user_rating' => rand(3, 5),
        'feedback_quality' => $qualities[array_rand($qualities)],
        'feedback_time' => $times[array_rand($times)],
        'feedback_behavior' => $behaviors[array_rand($behaviors)],
        'feedback_cleanliness' => $cleans[array_rand($cleans)],
        'user_feedback' => 'تم تجربة العمل وهو ممتاز جداً، شكراً لكم على سرعة الاستجابة.',
        'feedback_main_issue' => rand(0, 1) ? $issues[array_rand($issues)] : null,
    ]);
}

echo "Successfully seeded feedback for " . $reports->count() . " reports.\n";
