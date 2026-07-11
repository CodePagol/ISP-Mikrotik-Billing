<?php
// এটি অটোমেটিক আপনার সার্ভারের রুট পাথ (/home/username) বের করে নেবে
$home = dirname(__DIR__); 

$target = $home . '/master_core/storage/app/public';
$shortcut = $home . '/public_html/storage';

echo "Target path: " . $target . "<br>";
echo "Shortcut path: " . $shortcut . "<br><br>";

// পূর্বের কোনো লিংক থাকলে তা মুছে ফেলা
if (is_link($shortcut)) {
    unlink($shortcut);
}

// সিমলিংক তৈরি করা
if (symlink($target, $shortcut)) {
    echo "✅ Success! Storage link created in public_html.";
} else {
    echo "❌ Failed to create link. Make sure the 'public_html/storage' folder is deleted first.";
}
