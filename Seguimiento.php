<?php
// =========================================
// seguimiento.php – Seguimiento nutricional por paciente (CRUD completo)
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

// 3) Eliminar registro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delId = (int) $_POST['delete_id'];
    $stmt = $conexion->prepare("DELETE FROM seguimiento_nutricional WHERE id_seguimiento = ?");
    $stmt->bind_param("i", $delId);
    $stmt->execute();
    $stmt->close();
    header("Location: seguimiento.php?id_paciente={$id_p}");
    exit;
}

// 4) Insertar o actualizar registro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['peso'])) {
    $peso     = (float) $_POST['peso'];
    $altura   = (float) $_POST['altura'];
    $imc      = $altura > 0 ? round($peso / ($altura * $altura), 2) : 0;
    $calorias = (int) $_POST['calorias'];
    $metas    = $conexion->real_escape_string($_POST['metas']);

    if (isset($_POST['id_seguimiento']) && $_POST['id_seguimiento'] !== '') {
        // Actualizar
        $id_s = (int) $_POST['id_seguimiento'];
        $sqlUpd = "
            UPDATE seguimiento_nutricional SET
                peso                 = {$peso},
                altura               = {$altura},
                imc                  = {$imc},
                calorias_consumidas  = {$calorias},
                metas_alcanzadas     = '{$metas}'
            WHERE id_seguimiento = {$id_s}
        ";
        if (!$conexion->query($sqlUpd)) {
            die("Error al actualizar seguimiento: " . $conexion->error);
        }
    } else {
        // Insertar
        $sqlIns = "
            INSERT INTO seguimiento_nutricional
                (id_paciente, peso, altura, imc, calorias_consumidas, metas_alcanzadas)
            VALUES
                ({$id_p}, {$peso}, {$altura}, {$imc}, {$calorias}, '{$metas}')
        ";
        if (!$conexion->query($sqlIns)) {
            die("Error al guardar seguimiento: " . $conexion->error);
        }
    }
    header("Location: seguimiento.php?id_paciente={$id_p}");
    exit;
}

// 5) Leer paciente y registros
$pac = $conexion
    ->query("SELECT nombre FROM pacientes WHERE id_paciente = {$id_p}")
    ->fetch_assoc();

$res = $conexion->query("
    SELECT
        id_seguimiento,
        fecha_registro,
        peso,
        altura,
        imc,
        calorias_consumidas,
        metas_alcanzadas
    FROM seguimiento_nutricional
    WHERE id_paciente = {$id_p}
    ORDER BY fecha_registro DESC
");
if (!$res) {
    die("Error en consulta de seguimiento: " . $conexion->error);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Seguimiento Nutricional – <?= htmlspecialchars($pac['nombre']) ?></title>
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
      <div class="menu-item active"><a href="#"><i class="fas fa-chart-line"></i><span>Seguimiento</span></a></div>
      <div class="menu-item"><a href="consultas.php?id_paciente=<?= $id_p ?>"><i class="fas fa-notes-medical"></i><span>Consultas</span></a></div>
      <div class="menu-item"><a href="nutriologos.php"><i class="fas fa-user-md"></i><span>Nutriólogos</span></a></div>
    </nav>
  </aside>

  <!-- Contenido principal -->
  <main class="main-content">
    <h1 class="page-title">Seguimiento de <?= htmlspecialchars($pac['nombre']) ?></h1>

    <!-- Acción: nuevo registro -->
    <div class="action-buttons">
      <button class="btn btn-primary" id="btnNueva"><i class="fas fa-plus"></i> Nuevo Registro</button>
      <a class="btn btn-secondary" href="pacientes.php"><i class="fas fa-arrow-left"></i> Volver a Pacientes</a>
    </div>

    <!-- Tabla de registros -->
    <div class="recent-table">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th><th>Fecha</th><th>Peso (kg)</th><th>Altura (m)</th>
            <th>IMC</th><th>Calorías</th><th>Metas</th><th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($res->num_rows > 0): ?>
            <?php while ($fila = $res->fetch_assoc()): ?>
              <tr
                data-id="<?= $fila['id_seguimiento'] ?>"
                data-fecha="<?= $fila['fecha_registro'] ?>"
                data-peso="<?= $fila['peso'] ?>"
                data-altura="<?= $fila['altura'] ?>"
                data-imc="<?= $fila['imc'] ?>"
                data-calorias="<?= $fila['calorias_consumidas'] ?>"
                data-metas="<?= htmlspecialchars($fila['metas_alcanzadas'], ENT_QUOTES) ?>"
              >
                <td><?= $fila['id_seguimiento'] ?></td>
                <td><?= $fila['fecha_registro'] ?></td>
                <td><?= $fila['peso'] ?></td>
                <td><?= $fila['altura'] ?></td>
                <td><?= $fila['imc'] ?></td>
                <td><?= $fila['calorias_consumidas'] ?></td>
                <td><?= htmlspecialchars($fila['metas_alcanzadas']) ?></td>
                <td>
                  <button class="btn btn-icon edit-btn"><i class="fas fa-edit"></i></button>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="delete_id" value="<?= $fila['id_seguimiento'] ?>">
                    <button type="submit" class="btn btn-icon" onclick="return confirm('¿Eliminar registro?');">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="8">No hay registros.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Formulario para nuevo/editar registro -->
    <form id="form-seguimiento" class="form-paciente" style="display:none;"
          method="POST" action="seguimiento.php?id_paciente=<?= $id_p ?>">
      <input type="hidden" name="id_seguimiento" id="seg-id" value="">
      <h2 id="form-title">Nuevo Registro</h2>

      <div class="form-group">
        <label for="seg-peso">Peso (kg)</label>
        <input type="number" step="0.1" id="seg-peso" name="peso" required>
      </div>

        <div class="form-group">
            <label for="seg-altura">Altura (m)</label>
            <input type="number" step="0.01" id="seg-altura" name="altura" required>
            </div>

            <div class="form-group">
            <label for="seg-imc">IMC</label>
            <input type="text" id="seg-imc" name="imc" readonly placeholder="0.00">
            </div>

            <div class="form-group">
            <label for="seg-calorias">Calorías consumidas</label>
            <input type="number" id="seg-calorias" name="calorias" required>
        </div>


      <div class="form-group">
        <label for="seg-metas">Metas alcanzadas</label>
        <textarea id="seg-metas" name="metas"></textarea>
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
  const form     = document.getElementById('form-seguimiento');
  const title    = document.getElementById('form-title');
  const btnSave  = document.getElementById('btnGuardar');
  const fields   = {
    id       : document.getElementById('seg-id'),
    peso     : document.getElementById('seg-peso'),
    altura   : document.getElementById('seg-altura'),
    calorias : document.getElementById('seg-calorias'),
    metas    : document.getElementById('seg-metas')
  };
  const imcField = document.getElementById('seg-imc');

  // 1) cálculo
  function calcularIMC() {
    const peso   = parseFloat(fields.peso.value)   || 0;
    const altura = parseFloat(fields.altura.value) || 0;
    const imc    = altura > 0 ? (peso / (altura * altura)).toFixed(2) : '';
    imcField.value = imc;
  }

  // 2) listeners
  fields.peso.addEventListener('input', calcularIMC);
  fields.altura.addEventListener('input', calcularIMC);

  // 3) reset + edit deben recalcular IMC
  function resetForm() {
    form.reset();
    fields.id.value = '';
    imcField.value  = '';
    title.textContent = 'Nuevo Registro';
    btnSave.textContent = 'Guardar';
  }

  btnNueva.addEventListener('click', () => {
    resetForm();
    form.style.display = 'block';
  });

  document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      const tr = e.target.closest('tr');
      fields.id.value       = tr.dataset.id;
      fields.peso.value     = tr.dataset.peso;
      fields.altura.value   = tr.dataset.altura;
      fields.calorias.value = tr.dataset.calorias;
      fields.metas.value    = tr.dataset.metas;
      title.textContent     = 'Editar Registro';
      btnSave.textContent   = 'Actualizar';
      form.style.display    = 'block';
      calcularIMC();           // <— recalcula y muestra el IMC prellenado
    });
  });

  document.getElementById('btnCancelar').addEventListener('click', () => {
    form.style.display = 'none';
  });
});
</script>

</body>
</html>
