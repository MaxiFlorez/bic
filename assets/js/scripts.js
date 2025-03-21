 // assets/js/scripts.js

// Función para confirmar eliminación


function confirmarEliminacion(event) {
    if (!confirm('¿Estás seguro de eliminar este registro?')) {
        event.preventDefault(); // Evita la acción predeterminada si el usuario cancela
    }
}

// Función para alternar modo edición
function toggleEdit() {
    const body = document.body;
    const isEditing = body.classList.toggle('editing'); // Alternar clase 'editing'
    const btnEditar = document.getElementById('btn-editar');
    
    // Cambiar el texto del botón
    btnEditar.textContent = isEditing ? 'Cancelar Edición' : 'Editar';
    
    // Restablecer el formulario si se cancela la edición
    if (!isEditing) {
        document.getElementById('form-edicion').reset();
        const preview = document.getElementById('preview-foto');
        preview.src = preview.dataset.originalSrc; // Restaurar imagen original
    }
}

// Función para previsualizar imágenes
function previewImage(input) {
    const preview = document.getElementById('preview-foto');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result; // Actualizar la vista previa
        };
        reader.readAsDataURL(input.files[0]); // Leer el archivo como URL
    }
}

// Asignar eventos cuando el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    // Confirmar eliminación
    const enlacesEliminar = document.querySelectorAll('a[data-eliminar]');
    enlacesEliminar.forEach(enlace => {
        enlace.addEventListener('click', confirmarEliminacion);
    });

    // Previsualizar imágenes
    const inputFoto = document.querySelector('input[name="foto"]');
    if (inputFoto) {
        inputFoto.addEventListener('change', function() {
            previewImage(this);
        });
    }

    // Alternar modo edición
    const btnEditar = document.getElementById('btn-editar');
    if (btnEditar) {
        btnEditar.addEventListener('click', toggleEdit);
    }

    // Validación del formulario antes de enviar
    const formEdicion = document.getElementById('form-edicion');
    if (formEdicion) {
        formEdicion.addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid'); // Marcar campo como inválido
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault(); // Evitar envío si hay errores
                alert('Complete todos los campos obligatorios.');
            }
        });
    }
}); 
