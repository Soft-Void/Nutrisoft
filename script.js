// script.js

document.addEventListener('DOMContentLoaded', () => {
  const btnNuevo = document.getElementById('miBotón');
  const formPaciente = document.getElementById('form-paciente');
  const formTitle = document.getElementById('form-title');
  const btnSubmit = document.getElementById('btn-submit');

  const inputs = {
    id:       document.getElementById('paciente-id'),
    nombre:   document.getElementById('paciente-nombre'),
    edad:     document.getElementById('paciente-edad'),
    sexo:     document.getElementById('paciente-sexo'),
    telefono: document.getElementById('paciente-telefono'),
    email:    document.getElementById('paciente-email'),
    direccion:document.getElementById('paciente-direccion'),
    alergias: document.getElementById('paciente-alergias'),
    enfermedades: document.getElementById('paciente-enfermedades'),
    antecedentes: document.getElementById('paciente-antecedentes'),
    metas:    document.getElementById('paciente-metas'),
  };

  // Función para limpiar formulario
  function resetForm() {
    formPaciente.reset();
    inputs.id.value = '';
    formTitle.textContent = 'Nuevo Paciente';
    btnSubmit.textContent = 'Guardar';
  }

  // Mostrar formulario en blanco
  btnNuevo.addEventListener('click', () => {
    resetForm();
    formPaciente.style.display = 'block';
  });



  // Cerrar formulario al hacer reset
  formPaciente.addEventListener('reset', () => {
    formPaciente.style.display = 'none';
  });
});
