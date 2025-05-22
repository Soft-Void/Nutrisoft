<?php
// =========================================
// consultas.php – Historial de consultas (CRUD completo)
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
// 2) Parámetro paciente
if (!isset($_GET['id_paciente'])) {
    die("Falta el parámetro id_paciente");
}
$id_p = (int) $_GET['id_paciente'];

// 3) Eliminar consulta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_consulta_id'])) {
    $delId = (int) $_POST['delete_consulta_id'];
    $stmt = $conexion->prepare("DELETE FROM consultas WHERE id_consulta = ?");
    $stmt->bind_param("i", $delId);
    $stmt->execute();
    $stmt->close();
    header("Location: consultas.php?id_paciente=$id_p");
    exit;
}

// 4) Insertar o actualizar consulta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fecha'])) {
    // Datos comunes
    $fecha      = $conexion->real_escape_string($_POST['fecha']);
    $nutriologo = $conexion->real_escape_string($_POST['nutriologo']);
    $notas      = $conexion->real_escape_string($_POST['notas']);

    if (isset($_POST['id_consulta']) && $_POST['id_consulta'] !== '') {
        // Actualizar
        $id_c = (int) $_POST['id_consulta'];
        $sqlUpd = "
            UPDATE consultas SET
                fecha      = '$fecha',
                nutriologo = '$nutriologo',
                notas      = '$notas'
            WHERE id_consulta = $id_c
        ";
        if (! $conexion->query($sqlUpd)) {
            die("Error al actualizar consulta: " . $conexion->error);
        }
    } else {
        // Insertar
        $sqlIns = "
            INSERT INTO consultas (id_paciente, fecha, nutriologo, notas)
            VALUES ($id_p, '$fecha', '$nutriologo', '$notas')
        ";
        if (! $conexion->query($sqlIns)) {
            die("Error al guardar consulta: " . $conexion->error);
        }
    }
    header("Location: consultas.php?id_paciente=$id_p");
    exit;
}

// 5) Leer paciente y consultas
$pac = $conexion
    ->query("SELECT nombre FROM pacientes WHERE id_paciente=$id_p")
    ->fetch_assoc();

$resC = $conexion->query("
    SELECT id_consulta, fecha, nutriologo, notas
    FROM consultas
    WHERE id_paciente = $id_p
    ORDER BY fecha DESC
");
if (! $resC) {
    die("Error en la consulta de historial: " . $conexion->error);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Historial de Consultas – <?= htmlspecialchars($pac['nombre']) ?></title>
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
      <div class="menu-item"><a href="index.html"><i class="fas fa-home"></i><span>Inicio</span></a></div>
      <div class="menu-item"><a href="pacientes.php"><i class="fas fa-users"></i><span>Pacientes</span></a></div>
      <div class="menu-item active"><a href="#"><i class="fas fa-notes-medical"></i><span>Consultas</span></a></div>
      <div class="menu-item"><a href="nutriologos.php"><i class="fas fa-user-md"></i><span>Nutriólogos</span></a></div>
      <div class="menu-item"><a href="configuracion.php"><i class="fas fa-cog"></i><span>Configuración</span></a></div>
    </nav>
  </aside>

  <main class="main-content">
    <h1 class="page-title">Consultas de “<?= htmlspecialchars($pac['nombre']) ?>”</h1>

    <div class="action-buttons">
      <button class="btn btn-primary" id="btnNueva"><i class="fas fa-plus"></i> Nueva Consulta</button>
      <a class="btn btn-secondary" href="pacientes.php"><i class="fas fa-arrow-left"></i> Volver a Pacientes</a>
    </div>

    <div class="recent-table">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th><th>Fecha</th><th>Nutriólogo</th><th>Notas</th><th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($resC->num_rows > 0): ?>
            <?php while ($c = $resC->fetch_assoc()): ?>
              <tr
                data-id="<?= $c['id_consulta'] ?>"
                data-fecha="<?= htmlspecialchars($c['fecha'], ENT_QUOTES) ?>"
                data-nutriologo="<?= htmlspecialchars($c['nutriologo'], ENT_QUOTES) ?>"
                data-notas="<?= htmlspecialchars($c['notas'], ENT_QUOTES) ?>"
              >
                <td><?= $c['id_consulta'] ?></td>
                <td><?= $c['fecha'] ?></td>
                <td><?= htmlspecialchars($c['nutriologo']) ?></td>
                <td><?= nl2br(htmlspecialchars($c['notas'])) ?></td>
                <td>
                  <button class="btn btn-icon edit-btn"><i class="fas fa-edit"></i></button>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="delete_consulta_id" value="<?= $c['id_consulta'] ?>">
                    <button type="submit" class="btn btn-icon"
                            onclick="return confirm('¿Eliminar esta consulta?');">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="5">No hay consultas registradas.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <form id="form-consulta" class="form-paciente" style="display:none;"
          method="POST" action="consultas.php?id_paciente=<?= $id_p ?>">
      <input type="hidden" name="id_consulta" id="consulta-id" value="">
      <h2 id="form-title">Nueva Consulta</h2>
      <div class="form-group">
        <label for="consulta-fecha">Fecha y hora</label>
        <input type="datetime-local" id="consulta-fecha" name="fecha" required>
      </div>
      <div class="form-group">
        <label for="consulta-nutriologo">Nutriólogo</label>
        <input type="text" id="consulta-nutriologo" name="nutriologo" required>
      </div>
      <div class="form-group">
        <label for="consulta-notas">Notas</label>
        <textarea id="consulta-notas" name="notas"></textarea>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar</button>
        <button type="button" class="btn btn-secondary" id="btnCancelar">Cancelar</button>
      </div>
    </form>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const btnNueva = document.getElementById('btnNueva');
      const form    = document.getElementById('form-consulta');
      const title   = document.getElementById('form-title');
      const btnSave = document.getElementById('btnGuardar');
      const fields  = {
        id:         document.getElementById('consulta-id'),
        fecha:      document.getElementById('consulta-fecha'),
        nutriologo: document.getElementById('consulta-nutriologo'),
        notas:      document.getElementById('consulta-notas'),
      };

      function resetForm() {
        form.reset();
        fields.id.value = '';
        title.textContent = 'Nueva Consulta';
        btnSave.textContent = 'Guardar';
      }

      btnNueva.addEventListener('click', () => {
        resetForm();
        form.style.display = 'block';
      });

      document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', e => {
          const tr = e.target.closest('tr');
          fields.id.value         = tr.dataset.id;
          fields.fecha.value      = tr.dataset.fecha;
          fields.nutriologo.value = tr.dataset.nutriologo;
          fields.notas.value      = tr.dataset.notas;
          title.textContent       = 'Editar Consulta';
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
