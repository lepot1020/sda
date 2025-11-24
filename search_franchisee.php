<?php
require 'db.php';

$q = $_GET['q'] ?? '';

$sql = $mysqli->prepare("SELECT id, franchisee_name, area 
                         FROM franchisees 
                         WHERE franchisee_name LIKE CONCAT('%', ?, '%') 
                         ORDER BY franchisee_name ASC LIMIT 10");
$sql->bind_param("s", $q);
$sql->execute();
$res = $sql->get_result();

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode($data);
