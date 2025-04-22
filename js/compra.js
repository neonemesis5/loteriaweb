export const setupCompra = () => {
    const registroForm = document.getElementById('registroClienteForm');
    if (!registroForm) {
        console.error('Formulario no encontrado');
        return;
    }

    registroForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        console.log('Evento submit capturado');

        // Validar campos
        const requiredFields = ['nombre', 'apellido', 'numIdentificacion', 'pais', 'email', 'telefono'];
        let isValid = true;

        requiredFields.forEach(field => {
            const input = document.getElementById(field);
            console.log(`Validando campo ${field}:`, input);

            if (!input || !input.value.trim()) {
                if (input) input.style.border = '1px solid red';
                isValid = false;
            } else if (input) {
                input.style.border = '';
            }
        });

        const isAdult = document.getElementById('is_adult')?.checked;
        if (!isAdult) {
            alert('Debes ser mayor de edad para participar');
            return;
        }

        if (!isValid) {
            alert('Por favor completa todos los campos requeridos');
            return;
        }

        const data = {
            action: 'registrarCompra',
            nombre: document.getElementById('nombre').value,
            apellido: document.getElementById('apellido').value,
            numIdentificacion: document.getElementById('numIdentificacion').value,
            pais: document.getElementById('pais').value,
            email: document.getElementById('email').value,
            telefono: document.getElementById('telefono').value,
            is_adult: document.getElementById('is_adult').checked ? '1' : '0',
            numeros: window.numerosSeleccionados || [],
            sorteo_id: document.querySelector('.btn-comprar')?.getAttribute('data-sorteo') || '',
            precio: window.appData?.precioNumero || 0
        };
        try {
            // Enviar datos al servidor
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const response = await fetch('index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken  // Correcto: dentro de headers
                },
                body: JSON.stringify(data)
            });
            const responseText = await response.text();
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (e) {
                console.error('Error parseando JSON:', e);
                throw new Error('Respuesta inesperada del servidor: ' + responseText.substring(0, 100));
            }
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Error en el servidor');
            }
            // Limpiar formulario y redirigir
            registroForm.reset();
            window.location.href = result.whatsapp_url;
        } catch (error) {
            console.error('Error:', error);
            alert(error.message || 'Ocurrió un error al procesar tu compra. Por favor intenta nuevamente.');
        }
    });
};