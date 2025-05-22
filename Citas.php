<?php
// =========================================
// citas.php – Gestión completa de Citas (CRUD)
// =========================================

// 1) Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "test");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// 2) Eliminar cita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delId = (int) $_POST['delete_id'];
    $stmt = $conexion->prepare("DELETE FROM citas WHERE id_cita = ?");
    $stmt->bind_param("i", $delId);
    $stmt->execute();
    $stmt->close();
    header("Location: citas.php");
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
// 3) Insertar o actualizar cita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fecha'])) {
    // datos comunes
    $id_paciente   = (int) $_POST['id_paciente'];
    $id_nutriologo = (int) $_POST['id_nutriologo'];
    $fecha         = $conexion->real_escape_string($_POST['fecha']);
    $obs           = $conexion->real_escape_string($_POST['observaciones']);

    if (!empty($_POST['id_cita'])) {
        // actualizar
        $id_c = (int) $_POST['id_cita'];
        $sql = "
            UPDATE citas SET
                id_paciente   = {$id_paciente},
                id_nutriologo = {$id_nutriologo},
                fecha         = '{$fecha}',
                observaciones = '{$obs}'
            WHERE id_cita = {$id_c}
        ";
        if (! $conexion->query($sql)) {
            die("Error al actualizar cita: " . $conexion->error);
        }
    } else {
        // insertar
        $sql = "
            INSERT INTO citas
                (id_paciente, id_nutriologo, fecha, observaciones)
            VALUES
                ({$id_paciente}, {$id_nutriologo}, '{$fecha}', '{$obs}')
        ";
        if (! $conexion->query($sql)) {
            die("Error al guardar cita: " . $conexion->error);
        }
    }
    header("Location: citas.php");
    exit;
}

// 4) Obtener datos para listas y tabla
$pacientes   = $conexion->query("SELECT id_paciente, nombre FROM pacientes ORDER BY nombre");
$nutriologos = $conexion->query("SELECT id_nutriologo, nombre FROM nutriologos ORDER BY nombre");
$citasRes    = $conexion->query("
    SELECT
      c.id_cita,
      p.nombre   AS paciente,
      n.nombre   AS nutriologo,
      c.fecha,
      c.observaciones
    FROM citas c
    JOIN pacientes p    ON c.id_paciente   = p.id_paciente
    JOIN nutriologos n  ON c.id_nutriologo = n.id_nutriologo
    ORDER BY c.fecha DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>NutriSalud - Citas</title>
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
      <span ><?= htmlspecialchars($nombreNut) ?></span>
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
      <div class="menu-item active"><a href="citas.php"><i class="fas fa-calendar-alt"></i><span>Citas</span></a></div>
      <div class="menu-item"><a href="nutriologos.php"><i class="fas fa-user-md"></i><span>Nutriólogos</span></a></div>
      <div class="menu-item"><a href="configuracion.php"><i class="fas fa-cog"></i><span>Configuración</span></a></div>
    </nav>
  </aside>

  <main class="main-content">
    <h1 class="page-title">Gestión de Citas</h1>

    <!-- Nuevo / Editar Cita -->
    <div class="action-buttons">
      <button class="btn btn-primary" id="btnNuevo"><i class="fas fa-plus"></i> Nueva Cita</button>
    </div>

    <!-- Tabla de Citas -->
    <div class="recent-table">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th><th>Paciente</th><th>Nutriólogo</th><th>Fecha y hora</th><th>Observaciones</th><th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($citasRes->num_rows > 0): ?>
            <?php while ($c = $citasRes->fetch_assoc()): ?>
              <tr
                data-id="<?= $c['id_cita'] ?>"
                data-paciente="<?= htmlspecialchars($c['paciente'], ENT_QUOTES) ?>"
                data-nutriologo="<?= htmlspecialchars($c['nutriologo'], ENT_QUOTES) ?>"
                data-fecha="<?= htmlspecialchars($c['fecha'], ENT_QUOTES) ?>"
                data-obs="<?= htmlspecialchars($c['observaciones'], ENT_QUOTES) ?>"
              >
                <td><?= $c['id_cita'] ?></td>
                <td><?= htmlspecialchars($c['paciente']) ?></td>
                <td><?= htmlspecialchars($c['nutriologo']) ?></td>
                <td><?= $c['fecha'] ?></td>
                <td><?= nl2br(htmlspecialchars($c['observaciones'])) ?></td>
                <td>
                  <button class="btn btn-icon edit-btn" title="Editar cita">
                    <i class="fas fa-edit"></i>
                  </button>
                  
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="delete_id" value="<?= $c['id_cita'] ?>">
                    <button type="submit" class="btn btn-icon" onclick="return confirm('¿Cancelar esta cita?');" title="Cancelar cita">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
              
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="6">No hay citas programadas.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Formulario emergente -->
    <form id="form-cita" class="form-paciente" style="display:none;" method="POST" action="citas.php">
      <input type="hidden" name="id_cita" id="cita-id" value="">
      <h2 id="form-title">Nueva Cita</h2>

      <div class="form-group">
        <label for="cita-paciente">Paciente</label>
        <select id="cita-paciente" name="id_paciente" required>
          <option value="">Selecciona paciente</option>
          <?php while ($p = $pacientes->fetch_assoc()): ?>
            <option value="<?= $p['id_paciente'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="cita-nutriologo">Nutriólogo</label>
        <select id="cita-nutriologo" name="id_nutriologo" required>
          <option value="">Selecciona nutriólogo</option>
          <?php while ($n = $nutriologos->fetch_assoc()): ?>
            <option value="<?= $n['id_nutriologo'] ?>"><?= htmlspecialchars($n['nombre']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="cita-fecha">Fecha y hora</label>
        <input type="datetime-local" id="cita-fecha" name="fecha" required>
      </div>

      <div class="form-group">
        <label for="cita-obs">Observaciones</label>
        <textarea id="cita-obs" name="observaciones"></textarea>
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
      const form     = document.getElementById('form-cita');
      const title    = document.getElementById('form-title');
      const btnSave  = document.getElementById('btnGuardar');
      const fields   = {
        id         : document.getElementById('cita-id'),
        paciente   : document.getElementById('cita-paciente'),
        nutriologo : document.getElementById('cita-nutriologo'),
        fecha      : document.getElementById('cita-fecha'),
        obs        : document.getElementById('cita-obs'),
      };

      function resetForm() {
        form.reset();
        fields.id.value = '';
        title.textContent = 'Nueva Cita';
        btnSave.textContent = 'Guardar';
      }

      btnNuevo.addEventListener('click', () => {
        resetForm();
        form.style.display = 'block';
      });

      document.querySelectorAll('.edit-btn').forEach(btn => {
  btn.addEventListener('click', e => {
    const tr = e.target.closest('tr');
    fields.id.value         = tr.dataset.id;
    fields.paciente.value   = tr.dataset.idPaciente;
    fields.nutriologo.value = tr.dataset.idNutriologo;
    // recorta segundos y mete la T
    fields.fecha.value      = tr.dataset.fecha.substring(0,16).replace(' ', 'T');
    fields.obs.value        = tr.dataset.obs;
    title.textContent       = 'Editar Cita';
    btnSave.textContent     = 'Actualizar';
    form.style.display      = 'block';
  });
});


      document.getElementById('btnCancelar').addEventListener('click', () => {
        form.style.display = 'none';
      });
    });
  </script>
</body>
</html>
