<?php
session_start();

// 1) Conexión
$conexion = new mysqli("localhost","root","","test");
if ($conexion->connect_error) {
  die("Error de conexión: ".$conexion->connect_error);
}

// 2) Obtener id_nutriologo (fijo a 1 si no hay sesión ni GET)
if (isset($_SESSION['id_nutriologo'])) {
  $id_n = (int) $_SESSION['id_nutriologo'];
} elseif (isset($_GET['id_nutriologo'])) {
  $id_n = (int) $_GET['id_nutriologo'];
} else {
  // Por defecto usamos el nutriólogo con id=1
  $id_n = 1;
  // Si quieres, también puedes forzar la sesión:
  $_SESSION['id_nutriologo'] = $id_n;
}

// … resto idéntico al código anterior …


// 3) Procesar envío del formulario
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first    = $conexion->real_escape_string($_POST['nombre'] ?? '');
    $last     = $conexion->real_escape_string($_POST['apellido'] ?? '');
    $fullName = trim("$first $last");
    $prof     = $conexion->real_escape_string($_POST['profesion'] ?? '');
    $email    = $conexion->real_escape_string($_POST['email'] ?? '');
    $telefono = $conexion->real_escape_string($_POST['telefono'] ?? '');

    $sqlUpd = "
        UPDATE nutriologos SET
          nombre             = '$fullName',
          especialidad       = '$prof',
          email              = '$email',
          telefono           = '$telefono'
        WHERE id_nutriologo = $id_n
    ";
    if ($conexion->query($sqlUpd)) {
        $message = 'Perfil actualizado correctamente.';
    } else {
        $message = 'Error al guardar: ' . $conexion->error;
    }
}

// 4) Leer datos actuales
$res = $conexion->query("
    SELECT nombre, especialidad, email, telefono
    FROM nutriologos
    WHERE id_nutriologo = $id_n
");
if (! $res || $res->num_rows === 0) {
    die("Nutriólogo no encontrado.");
}
$nut = $res->fetch_assoc();

// 5) Separar nombre y apellido sin errores
$nameParts  = explode(' ', $nut['nombre'], 2);
$firstName  = $nameParts[0] ?? '';
$lastName   = $nameParts[1] ?? '';
$prof       = $nut['especialidad']    ?? '';
$emailUser  = $nut['email']           ?? '';
$phoneUser  = $nut['telefono']        ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>NutriSalud - Configuración</title>
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <!-- Navbar -->
  <div class="navbar">
    <div class="logo"><i class="fas fa-apple-alt"></i><span>NutriSalud</span></div>
    <div class="profile"><span><?= htmlspecialchars($firstName) ?></span></div>
  </div>

  <!-- Toggle sidebar -->
  <button class="toggle-sidebar"><i class="fas fa-bars"></i></button>

  <!-- Sidebar -->
  <aside class="sidebar">
    <nav class="menu">
      <div class="menu-item"><a href="index.html"><i class="fas fa-home"></i><span>Inicio</span></a></div>
      <div class="menu-item"><a href="pacientes.php"><i class="fas fa-users"></i><span>Pacientes</span></a></div>
      <div class="menu-item"><a href="citas.php"><i class="fas fa-calendar-alt"></i><span>Citas</span></a></div>
      <div class="menu-item"><a href="nutriologos.php"><i class="fas fa-user-md"></i><span>Nutriólogos</span></a></div>
      <div class="menu-item active"><a href="configuracion.php"><i class="fas fa-cog"></i><span>Configuración</span></a></div>
    </nav>
  </aside>

  <!-- Contenido principal -->
  <main class="main-content">
    <h1 class="page-title">Configuración del Sistema</h1>

    <?php if ($message): ?>
      <div class="alert success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="config-container stat-card">
      <div class="config-section">
        <h3>Información Personal</h3>
        <form method="POST" action="configuracion.php">
          <div class="form-row">
            <div class="form-group">
              <label for="nombre">Nombre</label>
              <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($firstName) ?>" class="form-input" required>
            </div>
            <div class="form-group">
              <label for="apellido">Apellido</label>
              <input type="text" id="apellido" name="apellido" value="<?= htmlspecialchars($lastName) ?>" class="form-input">
            </div>
          </div>
          <div class="form-group">
            <label for="profesion">Profesión</label>
            <input type="text" id="profesion" name="profesion" value="<?= htmlspecialchars($prof) ?>" class="form-input">
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($emailUser) ?>" class="form-input" required>
          </div>
          <div class="form-group">
            <label for="telefono">Teléfono</label>
            <input type="tel" id="telefono" name="telefono" value="<?= htmlspecialchars($phoneUser) ?>" class="form-input">
          </div>
          <div class="form-actions">
            <button type="submit" class="btn primary">Guardar Cambios</button>
            <button type="reset" class="btn secondary" id="btnCancelar">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
  </main>

  <script src="script.js"></script>
</body>
</html>
