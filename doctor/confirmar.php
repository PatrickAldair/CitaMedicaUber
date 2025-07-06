<?php
session_start(); if(!isset($_SESSION['usuario'])||$_SESSION['usuario']['tipo']!=='doctor') header('Location: ../login.php');
require '../db.php';
$id = (int)$_GET['id'];
$res = $_GET['res']==='aceptada'?'aceptada':'rechazada';
$stmt=$pdo->prepare("UPDATE citas SET estado=? WHERE id=? AND id_doctor=?");
$stmt->execute([$res,$id,$_SESSION['usuario']['id']]);
header('Location: dashboard.php');
exit;
