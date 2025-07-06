<?php
session_start();
if (isset($_SESSION['usuario'])) {
  header("Location: {$_SESSION['usuario']['tipo']}/dashboard.php");
} else {
  header('Location: login.php');
}
exit;
