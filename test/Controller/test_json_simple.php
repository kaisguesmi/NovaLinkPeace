<?php
// Vider tous les buffers
while (ob_get_level()) ob_end_clean();

header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Test réussi']);
exit();
?>
