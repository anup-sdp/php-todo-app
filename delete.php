<?php
// delete.php
require 'db.php';

$id = (int)($_POST['id'] ?? 0);

$stmt = $db->prepare("DELETE FROM todos WHERE id = :id");
$stmt->execute(['id' => $id]);

header('Location: index.php');
exit;