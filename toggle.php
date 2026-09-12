<?php
// toggle.php
require 'db.php';

$id = (int)($_POST['id'] ?? 0);

$stmt = $db->prepare("UPDATE todos SET done = 1 - done WHERE id = :id");
$stmt->execute(['id' => $id]);

header('Location: index.php');
exit;