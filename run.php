<?php
function pr($arr){
	echo '<pre>';
	print_r($arr);
	echo '</pre>';
}
require_once 'Dedups.php';
require_once 'LeadRepository.php';
// Load input
$inputFile 	= 'leads.json';
$outputFile = 'leads_deduped.json';
$logFile 	= 'dedups_log.json';

$repo = new LeadRepository($inputFile,$outputFile);
$leadData = $repo->load();

$deduper = new Dedups($leadData);
$cleanLeads = $deduper->removeduplicate();
pr($cleanLeads);
$repo->save($cleanLeads);

$logs = $deduper->getLogs();

//Write output
file_put_contents($outputFile, json_encode(['leads' => $cleanLeads], JSON_PRETTY_PRINT));
file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT));

echo "Removed duplicate records. Output written to '{$outputFile}' and '{$logFile}'.\n";
?>