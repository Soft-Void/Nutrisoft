<?php
// =========================================
// pacientes.php – Gestión completa de Pacientes
// =========================================

// 1) Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "test");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// 2) Eliminar paciente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $idEliminar = (int) $_POST['delete_id'];
    $stmt = $conexion->prepare("DELETE FROM pacientes WHERE id_paciente = ?");
    $stmt->bind_param("i", $idEliminar);
    $stmt->execute();
    $stmt->close();
    header("Location: pacientes.php");
    exit;
}

// 3) Insertar nuevo paciente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['id']) || $_POST['id'] === '')) {
    $nombre               = $conexion->real_escape_string($_POST['nombre']);
    $edad                 = (int) $_POST['edad'];
    $sexo                 = $conexion->real_escape_string($_POST['sexo']);
    $telefono             = $conexion->real_escape_string($_POST['telefono']);
    $email                = $conexion->real_escape_string($_POST['email']);
    $direccion            = $conexion->real_escape_string($_POST['direccion']);
    $alergias             = $conexion->real_escape_string($_POST['alergias']);
    $enfermedades         = $conexion->real_escape_string($_POST['enfermedades']);
    $antecedentes         = $conexion->real_escape_string($_POST['antecedentes']);
    $metas_nutricionales  = $conexion->real_escape_string($_POST['metas_nutricionales']);

    $sqlins = "
        INSERT INTO pacientes
            (nombre, edad, sexo, telefono, email, direccion,
             alergias, enfermedades, antecedentes, metas_nutricionales)
        VALUES
            ('$nombre', $edad, '$sexo', '$telefono', '$email', '$direccion',
             '$alergias', '$enfermedades', '$antecedentes', '$metas_nutricionales')
    ";
    if (!$conexion->query($sqlins)) {
        die("Error al guardar: " . $conexion->error);
    }
    header("Location: pacientes.php");
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
// 5) Leer todos los pacientes
$sql = "
    SELECT
        id_paciente AS id,
        nombre,
        edad,
        sexo,
        telefono,
        email,
        direccion,
        alergias,
        enfermedades,
        antecedentes,
        metas_nutricionales,
        fecha_registro,
        fecha_actualizacion
    FROM pacientes
    ORDER BY id_paciente DESC
";
$res = $conexion->query($sql);
if (!$res) {
    die("Error en la consulta: " . $conexion->error);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>NutriSalud - Pacientes</title>
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <!-- Barra de navegación -->
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
      <div class="menu-item"><a href="index.html"><i class="fas fa-home"></i><span>Inicio</span></a></div>
      <div class="menu-item active"><a href="pacientes.php"><i class="fas fa-users"></i><span>Pacientes</span></a></div>
      <div class="menu-item"><a href="citas.php"><i class="fas fa-calendar-alt"></i><span>Citas</span></a></div>
      <div class="menu-item"><a href="nutriologos.php"><i class="fas fa-user-md"></i><span>Nutriólogos</span></a></div>
      <div class="menu-item"><a href="configuracion.php"><i class="fas fa-cog"></i><span>Configuración</span></a></div>
      
      
    </nav>
  </aside>

  <!-- Contenido principal -->
  <main class="main-content">
    <h1 class="page-title">Gestión de Pacientes</h1>

    <!-- Botón Nuevo Paciente -->
    <div class="action-buttons">
      <button class="btn btn-primary" id="miBotón"><i class="fas fa-plus"></i> Nuevo Paciente</button>
    </div>

    <!-- Tabla de Pacientes -->
    <div class="recent-table">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th><th>Nombre</th><th>Edad</th><th>Sexo</th>
            <th>Teléfono</th><th>Email</th><th>Dirección</th>
            <th>Registrado</th><th>Actualizado</th><th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($res->num_rows > 0): ?>
            <?php while ($p = $res->fetch_assoc()): ?>
              <tr
                data-id="<?= $p['id'] ?>"
                data-nombre="<?= htmlspecialchars($p['nombre'], ENT_QUOTES) ?>"
                data-edad="<?= htmlspecialchars($p['edad'], ENT_QUOTES) ?>"
                data-sexo="<?= htmlspecialchars($p['sexo'], ENT_QUOTES) ?>"
                data-telefono="<?= htmlspecialchars($p['telefono'], ENT_QUOTES) ?>"
                data-email="<?= htmlspecialchars($p['email'], ENT_QUOTES) ?>"
                data-direccion="<?= htmlspecialchars($p['direccion'], ENT_QUOTES) ?>"
                data-alergias="<?= htmlspecialchars($p['alergias'], ENT_QUOTES) ?>"
                data-enfermedades="<?= htmlspecialchars($p['enfermedades'], ENT_QUOTES) ?>"
                data-antecedentes="<?= htmlspecialchars($p['antecedentes'], ENT_QUOTES) ?>"
                data-metas_nutricionales="<?= htmlspecialchars($p['metas_nutricionales'], ENT_QUOTES) ?>"
              >
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= $p['edad'] ?></td>
                <td><?= htmlspecialchars($p['sexo']) ?></td>
                <td><?= htmlspecialchars($p['telefono']) ?></td>
                <td><?= htmlspecialchars($p['email']) ?></td>
                <td><?= htmlspecialchars($p['direccion']) ?></td>
                <td><?= $p['fecha_registro'] ?></td>
                <td><?= $p['fecha_actualizacion'] ?: '—' ?></td>
                <td>
                <a href="consultas.php?id_paciente=<?= $p['id'] ?>" class="btn btn-icon"  title="Ver historial de consultas"><i class="fas fa-notes-medical"></i></a>
                <a href="seguimiento.php?id_paciente=<?= $p['id'] ?>" class="btn btn-icon" title="Ver seguimiento nutricional"> <i class="fas fa-chart-line"></i> </a>  
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn btn-icon" onclick="return confirm('¿Eliminar este registro?');">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="10">No hay pacientes registrados.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Formulario único (Nuevo / Editar) -->
    <form id="form-paciente" class="form-paciente" style="display:none;" action="pacientes.php" method="POST">
      <input type="hidden" name="id" id="paciente-id" value="">
      <h2 id="form-title">Nuevo Paciente</h2>
      <!-- Campos del paciente -->
      <div class="form-group">
        <label for="paciente-nombre">Nombre</label>
        <input type="text" id="paciente-nombre" name="nombre" required>
      </div>
      <div class="form-group">
        <label for="paciente-edad">Edad</label>
        <input type="number" id="paciente-edad" name="edad" min="0">
      </div>
      <div class="form-group">
        <label for="paciente-sexo">Sexo</label>
        <select id="paciente-sexo" name="sexo" required>
          <option value="">Seleccione</option>
          <option value="M">Masculino</option>
          <option value="F">Femenino</option>
          <option value="Otro">Otro</option>
        </select>
      </div>
      <div class="form-group">
        <label for="paciente-telefono">Teléfono</label>
        <input type="tel" id="paciente-telefono" name="telefono">
      </div>
      <div class="form-group">
        <label for="paciente-email">Email</label>
        <input type="email" id="paciente-email" name="email">
      </div>
      <div class="form-group">
        <label for="paciente-direccion">Dirección</label>
        <input type="text" id="paciente-direccion" name="direccion">
      </div>
      <div class="form-group">
        <label for="paciente-alergias">Alergias</label>
        <textarea id="paciente-alergias" name="alergias"></textarea>
      </div>
      <div class="form-group">
        <label for="paciente-enfermedades">Enfermedades</label>
        <textarea id="paciente-enfermedades" name="enfermedades"></textarea>
      </div>
      <div class="form-group">      
      <label for="paciente-antecedentes">Antecedentes</label>
        <textarea id="paciente-antecedentes" name="antecedentes"></textarea>
      </div>
      <div class="form-group">
        <label for="paciente-metas">Metas Nutricionales</label>
        <textarea id="paciente-metas" name="metas_nutricionales"></textarea>
      </div>
      <div class="form-actions">
      <button type="submit" class="btn btn-primary" id="btn-submit">Guardar</button>
      <button type="reset" class="btn btn-secondary">Borrar</button>
      </div>
    </form>
  </main>

  <script src="script.js"></script>
</body>
</html>
<?php $conexion->close(); ?>
