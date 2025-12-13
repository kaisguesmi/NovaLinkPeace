<?php
// Simulate GET request parameters
$_GET['page'] = 1;
$_GET['per_page'] = 5;

// Adjust include path so that list.php finds ../Model/db.php
// list.php expects to be in Controller/, so ../Model works.
// We are in NovaInteractive/ (root of app logic), so Controller/list.php is correct relevant to us,
// BUT list.php has `include_once '../Model/db.php'`.
// If we run from NovaInteractive/, `include 'Controller/list.php'` runs inside this context.
// Inside list.php, `../Model/db.php` is resolved relative to the *current working directory* OR the file directory depending on how include works in this version/config.
// Best to manually include the specific files with absolute-ish paths to mock the environment.

chdir('Controller'); // Move into Controller dir so relative paths inside list.php work
ob_start();
include 'list.php';
$json = ob_get_clean();

$data = json_decode($json, true);

echo "Record Count: " . count($data['records']) . "\n";
if (count($data['records']) > 0) {
    $first = $data['records'][0];
    echo "First Record Keys:\n";
    print_r(array_keys($first));
    echo "\nValues for AI fields:\n";
    echo "AI Score: " . (isset($first['ai_score']) ? $first['ai_score'] : 'MISSING') . "\n";
    echo "AI Analysis: " . (isset($first['ai_analysis']) ? $first['ai_analysis'] : 'MISSING') . "\n";
} else {
    echo "No records found.\n";
}
?>
