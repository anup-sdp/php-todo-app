<?php
// add.php
require 'db.php';

$title = trim($_POST['title'] ?? '');

if ($title !== '') {
    $stmt = $db->prepare("INSERT INTO todos (title) VALUES (:title)");
    $stmt->execute(['title' => $title]);
}

header('Location: index.php');
exit;