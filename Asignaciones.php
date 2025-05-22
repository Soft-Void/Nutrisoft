<?php
// =========================================
// asignaciones.php – Asignación de pacientes a nutriólogos
// =========================================
session_start();

// 1) Conexión
$conexion = new mysqli("localhost","root","","test");
if ($conexion->connect_error) die("Error de conexión: ".$conexion->connect_error);

// 2) Determinar nutriólogo (por GET o por defecto 1)
$id_n = isset($_GET['id_nutriologo']) 
      ? (int)$_GET['id_nutriologo'] 
      : 1;

// 3) Procesar POST de asignaciones
if ($_SERVER['REQUEST_METHOD']==='POST') {
    // Elimina previas
    $conexion->query("DELETE FROM nutriologo_paciente WHERE id_nutriologo=$id_n");
    // Inserta nuevas
    if (!empty($_POST['paciente_ids'])) {
      $stmt = $conexion->prepare(
        "INSERT INTO nutriologo_paciente (id_nutriologo,id_paciente) VALUES (?, ?)"
      );
      foreach ($_POST['paciente_ids'] as $pid) {
          $stmt->bind_param("ii", $id_n, $pid);
          $stmt->execute();
      }
      $stmt->close();
    }
    header("Location: asignaciones.php?id_nutriologo=$id_n");
    exit;
}
session_start();
$id_n = $_SESSION['id_nutriologo'] ?? 1;

// Sacamos solo el nombre del nutriólogo
$stmt = $conexion->prepare("SELECT nombre FROM nutriologos WHERE id_nutriologo = ?");
$stmt->bind_param("i", $id_n);
$stmt->execute();
$stmt->bind_result($nombreNut);
$stmt->fetch();
$stmt->close();
// 4) Datos para la vista
$asigRes = $conexion->query(
  "SELECT id_paciente 
     FROM nutriologo_paciente 
    WHERE id_nutriologo=$id_n"
);
$asignados = [];
while ($r = $asigRes->fetch_assoc()) {
    $asignados[] = $r['id_paciente'];
}
$pats = $conexion->query("SELECT id_paciente,nombre FROM pacientes ORDER BY nombre");
$nut  = $conexion->query(
  "SELECT nombre 
     FROM nutriologos 
    WHERE id_nutriologo=$id_n"
)->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Asignaciones – <?= htmlspecialchars($nut['nombre']) ?></title>
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <!-- Navbar -->
  <div class="navbar">
  <div class="logo">
    <i class="fas fa-apple-alt"></i><span>NutriSalud</span>
  </div>
  <div class="profile">
    <a href="configuracion.php?id_nutriologo=<?= $id_n ?>">
      <i class="fas fa-user-md"></i>
      <span><?= htmlspecialchars($nombreNut) ?></span>
    </a>
  </div>
</div>

  <!-- Toggle sidebar -->
  <button class="toggle-sidebar"><i class="fas fa-bars"></i></button>
  <!-- Sidebar -->
  <aside class="sidebar">
    <nav class="menu">
      <div class="menu-item"><a href="index.php"><i class="fas fa-home"></i><span>Inicio</span></a></div>
      <div class="menu-item"><a href="pacientes.php"><i class="fas fa-users"></i><span>Pacientes</span></a></div>
      <div class="menu-item"><a href="consultas.php?id_paciente=0"><i class="fas fa-notes-medical"></i><span>Consultas</span></a></div>
      <div class="menu-item"><a href="nutriologos.php"><i class="fas fa-user-md"></i><span>Nutriólogos</span></a></div>
      <div class="menu-item active"><a href="asignaciones.php?id_nutriologo=<?= $id_n ?>"><i class="fas fa-chart-line"></i><span>Asignaciones</span></a></div>
    </nav>
  </aside>

  <main class="main-content asignaciones">
    <h1 class="page-title">Asignar pacientes a <?= htmlspecialchars($nut['nombre']) ?></h1>
    <form method="POST" action="asignaciones.php?id_nutriologo=<?= $id_n ?>">
      <ul>
        <?php while ($p = $pats->fetch_assoc()): ?>
          <li>
            <label>
              <input type="checkbox"
                     name="paciente_ids[]"
                     value="<?= $p['id_paciente'] ?>"
                     <?= in_array($p['id_paciente'], $asignados) ? 'checked' : '' ?>>
              <?= htmlspecialchars($p['nombre']) ?>
            </label>
          </li>
        <?php endwhile; ?>
      </ul>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Guardar asignaciones</button>
        <a href="nutriologos.php" class="btn btn-secondary">Volver a Nutriólogos</a>
      </div>
    </form>
  </main>

  <script src="script.js"></script>
</body>
</html>
