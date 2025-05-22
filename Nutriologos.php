<?php
// =========================================
// nutriologos.php – Gestión completa de Nutriólogos
// =========================================

// 1) Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "test");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
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
// 2) Eliminar nutriólogo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delId = (int) $_POST['delete_id'];
    $stmt = $conexion->prepare("DELETE FROM nutriologos WHERE id_nutriologo = ?");
    $stmt->bind_param("i", $delId);
    $stmt->execute();
    $stmt->close();
    header("Location: nutriologos.php");
    exit;
}

// 3) Insertar o actualizar nutriólogo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'])) {
    $nombre    = $conexion->real_escape_string($_POST['nombre']);
    $especialidad = $conexion->real_escape_string($_POST['especialidad']);
    $cedula    = $conexion->real_escape_string($_POST['cedula_profesional']);
    $telefono  = $conexion->real_escape_string($_POST['telefono']);
    $email     = $conexion->real_escape_string($_POST['email']);

    if (isset($_POST['id_nutriologo']) && $_POST['id_nutriologo'] !== '') {
        // Actualizar
        $id_n = (int) $_POST['id_nutriologo'];
        $sql = "
            UPDATE nutriologos SET
                nombre              = '$nombre',
                especialidad        = '$especialidad',
                cedula_profesional  = '$cedula',
                telefono            = '$telefono',
                email               = '$email'
            WHERE id_nutriologo = $id_n
        ";
        if (! $conexion->query($sql)) {
            die("Error al actualizar nutriólogo: " . $conexion->error);
        }
    } else {
        // Insertar
        $sql = "
            INSERT INTO nutriologos
                (nombre, especialidad, cedula_profesional, telefono, email)
            VALUES
                ('$nombre', '$especialidad', '$cedula', '$telefono', '$email')
        ";
        if (! $conexion->query($sql)) {
            die("Error al guardar nutriólogo: " . $conexion->error);
        }
    }
    header("Location: nutriologos.php");
    exit;
}

// 4) Leer todos los nutriólogos
$sql = "
    SELECT
        id_nutriologo AS id,
        nombre,
        especialidad,
        cedula_profesional AS cedula,
        telefono,
        email,
        fecha_registro
    FROM nutriologos
    ORDER BY id_nutriologo DESC
";
$res = $conexion->query($sql);
if (! $res) {
    die("Error en consulta de nutriólogos: " . $conexion->error);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>NutriSalud - Nutriólogos</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
      <div class="menu-item"><a href="index.html"><i class="fas fa-home"></i><span>Inicio</span></a></div>
      <div class="menu-item"><a href="pacientes.php"><i class="fas fa-users"></i><span>Pacientes</span></a></div>
      <div class="menu-item"><a href="citas.php"><i class="fas fa-calendar-alt"></i><span>Citas</span></a></div>
      <div class="menu-item active"><a href="nutriologos.php"><i class="fas fa-user-md"></i><span>Nutriólogos</span></a></div>
      <div class="menu-item"><a href="configuracion.php"><i class="fas fa-cog"></i><span>Configuración</span></a></div>
    </nav>
  </aside>
  <!-- Contenido principal -->
  <main class="main-content">
    <h1 class="page-title">Gestión de Nutriólogos</h1>
    <div class="action-buttons">
      <button class="btn btn-primary" id="btnNuevo"><i class="fas fa-plus"></i> Nuevo Nutriólogo</button>
    </div>
    <div class="recent-table">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th><th>Nombre</th><th>Especialidad</th><th>Cédula</th>
            <th>Teléfono</th><th>Email</th><th>Registro</th><th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($res->num_rows > 0): ?>
            <?php while ($n = $res->fetch_assoc()): ?>
              <tr
                data-id="<?= $n['id'] ?>"
                data-nombre="<?= htmlspecialchars($n['nombre'], ENT_QUOTES) ?>"
                data-especialidad="<?= htmlspecialchars($n['especialidad'], ENT_QUOTES) ?>"
                data-cedula="<?= htmlspecialchars($n['cedula'], ENT_QUOTES) ?>"
                data-telefono="<?= htmlspecialchars($n['telefono'], ENT_QUOTES) ?>"
                data-email="<?= htmlspecialchars($n['email'], ENT_QUOTES) ?>"
              >
                <td><?= $n['id'] ?></td>
                <td><?= htmlspecialchars($n['nombre']) ?></td>
                <td><?= htmlspecialchars($n['especialidad']) ?></td>
                <td><?= htmlspecialchars($n['cedula']) ?></td>
                <td><?= htmlspecialchars($n['telefono']) ?></td>
                <td><?= htmlspecialchars($n['email']) ?></td>
                <td><?= $n['fecha_registro'] ?></td>
                <td>
                <a href="asignaciones.php?id_nutriologo=<?= $n['id'] ?>" class="btn btn-icon" title="Asignar pacientes"> <i class="fas fa-user-plus"></i> </a>
                  <button class="btn btn-icon edit-btn"><i class="fas fa-edit"></i></button>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="delete_id" value="<?= $n['id'] ?>">
                    <button type="submit" class="btn btn-icon" onclick="return confirm('¿Eliminar nutriólogo?');">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="8">No hay nutriólogos registrados.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <form id="form-nutriologo" class="form-paciente" style="display:none;" method="POST" action="nutriologos.php">
      <input type="hidden" name="id_nutriologo" id="nut-id" value="">
      <h2 id="form-title">Nuevo Nutriólogo</h2>
      <div class="form-group">
        <label for="nut-nombre">Nombre</label>
        <input type="text" id="nut-nombre" name="nombre" required>
      </div>
      <div class="form-group">
        <label for="nut-especialidad">Especialidad</label>
        <input type="text" id="nut-especialidad" name="especialidad">
      </div>
      <div class="form-group">
        <label for="nut-cedula">Cédula Profesional</label>
        <input type="text" id="nut-cedula" name="cedula_profesional">
      </div>
      <div class="form-group">
        <label for="nut-telefono">Teléfono</label>
        <input type="tel" id="nut-telefono" name="telefono">
      </div>
      <div class="form-group">
        <label for="nut-email">Email</label>
        <input type="email" id="nut-email" name="email">
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar</button>
        <button type="button" class="btn btn-secondary" id="btnCancelar">Cancelar</button>
      </div>
    </form>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const btnNuevo = document.getElementById('btnNuevo');
      const form     = document.getElementById('form-nutriologo');
      const title    = document.getElementById('form-title');
      const btnSave  = document.getElementById('btnGuardar');
      const flds     = {
        id           : document.getElementById('nut-id'),
        nombre       : document.getElementById('nut-nombre'),
        especialidad : document.getElementById('nut-especialidad'),
        cedula       : document.getElementById('nut-cedula'),
        telefono     : document.getElementById('nut-telefono'),
        email        : document.getElementById('nut-email')
      };
      function resetForm() {
        form.reset();
        flds.id.value = '';
        title.textContent = 'Nuevo Nutriólogo';
        btnSave.textContent = 'Guardar';
      }
      btnNuevo.addEventListener('click', () => { resetForm(); form.style.display = 'block'; });
      document.querySelectorAll('.edit-btn').forEach(btn => btn.addEventListener('click', e => {
        const tr = e.target.closest('tr');
        flds.id.value           = tr.dataset.id;
        flds.nombre.value       = tr.dataset.nombre;
        flds.especialidad.value = tr.dataset.especialidad;
        flds.cedula.value       = tr.dataset.cedula;
        flds.telefono.value     = tr.dataset.telefono;
        flds.email.value        = tr.dataset.email;
        title.textContent       = 'Editar Nutriólogo';
        btnSave.textContent     = 'Actualizar';
        form.style.display      = 'block';
      }));
      document.getElementById('btnCancelar').addEventListener('click', () => form.style.display = 'none');
    });
  </script>
</body>
</html>
