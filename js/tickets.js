export let numerosSeleccionados = [];
window.numerosSeleccionados = numerosSeleccionados;
// Generación de tabla de números
export const setupTicketsTable = (totalNumeros, numerosVendidos, precioNumero) => {
    // const numerosSeleccionados = [];
    // Función para actualizar el carrito
    function actualizarCarrito() {
        const cartItems = document.querySelector('.cart-items');
        const noOfItems = document.querySelector('.noOfItems');
        const result = document.querySelector('.result');
        const totalElement = document.querySelector('.total'); // Seleccionar el elemento del total

        // Limpiar el carrito
        cartItems.innerHTML = '';
        result.innerHTML = '';

        // Agregar cada número seleccionado
        numerosSeleccionados.forEach(numero => {
            const item = document.createElement('div');
            item.className = 'cart-item';
        //     item.innerHTML = `
        //     <div class="numero-seleccionado">${numero}</div>
        //     <div class="remove-item" data-numero="${numero}">×</div>
        // `;
            cartItems.appendChild(item);

            // Mostrar también en la sección de resultados
            const numResult = document.createElement('span');
            numResult.className = 'numero-resultado';
            numResult.textContent = numero + ' ';
            result.appendChild(numResult);
        });

        // Actualizar contador
        noOfItems.textContent = `${numerosSeleccionados.length} `;//Numeros

        // Actualizar el total (aquí está el cambio principal)
        const total = parseFloat(numerosSeleccionados.length * precioNumero);
        if (!isNaN(total)) {
            // Usar toLocaleString para mejor compatibilidad
            totalElement.textContent = `$${total.toLocaleString('es-ES', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })}`;
        } else {
            alert(precioNumero);
            // Manejo de error por si acaso
            console.error('El precio por número no es válido:', precioNumero);
            totalElement.textContent = '$0.00';
        }
        
        
        // totalElement.textContent = `$${total.toFixed(2)}`; // Formatear a 2 decimales

        // Mostrar/ocultar carrito según necesidad
        const shopcart = document.getElementById('shopcart');
        if (numerosSeleccionados.length > 0) {
            shopcart.style.display = 'block';
        }
    }

    // Función para manejar la selección de números
    function seleccionarNumero(numero) {
        // Verificar si el número ya está seleccionado
        const index = numerosSeleccionados.indexOf(numero);

        if (index === -1) {
            // Agregar número
            numerosSeleccionados.push(numero);
        } else {
            // Quitar número
            numerosSeleccionados.splice(index, 1);
        }

        actualizarCarrito();
    }

    // Evento para eliminar números del carrito
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-item')) {
            const numero = e.target.getAttribute('data-numero');
            const index = numerosSeleccionados.indexOf(numero);

            if (index !== -1) {
                numerosSeleccionados.splice(index, 1);
                actualizarCarrito();

                // Restaurar el estilo del número en la tabla
                const numeroDiv = document.querySelector(`.carton-numero.disponible[data-numero="${numero}"]`);
                if (numeroDiv) {
                    numeroDiv.classList.remove('seleccionado');
                }
            }
        }
    });

    function generarTablaNumeros(start, end) {
        const tabla = document.querySelector('.numeros-tabla');
        tabla.innerHTML = '';
        let num = start;

        for (let i = 0; i < 10; i++) {
            const row = document.createElement('tr');
            for (let j = 0; j < 10; j++) {
                if (num <= end && num < totalNumeros) {
                    const numeroFormateado = String(num).padStart(3, '0');
                    const estaVendido = numerosVendidos.includes(numeroFormateado);

                    const cell = document.createElement('td');
                    const link = document.createElement('a');
                    link.href = estaVendido ? 'javascript:void(0)' : '#';
                    const div = document.createElement('div');
                    div.className = 'carton-numero ' + (estaVendido ? 'vendido' : 'disponible');
                    div.textContent = numeroFormateado;
                    div.setAttribute('data-numero', numeroFormateado);

                    if (estaVendido) {
                        div.title = 'Número ya vendido';
                    } else {
                        div.addEventListener('click', function () {
                            // Alternar selección del número
                            this.classList.toggle('seleccionado');
                            seleccionarNumero(numeroFormateado);
                        });
                    }
                    link.appendChild(div);
                    cell.appendChild(link);
                    row.appendChild(cell);
                    num++;
                }
            }
            tabla.appendChild(row);
        }
    }


    // Eventos para botones de rango
    document.querySelectorAll('.rango-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const start = parseInt(this.getAttribute('data-start'));
            const end = parseInt(this.getAttribute('data-end'));
            generarTablaNumeros(start, end);
        });
    });

    // Tabla inicial
    generarTablaNumeros(0, 99);
};

// Google Maps
let mapLoaded = false;

export const initMap = () => {
    const ubicacion = { lat: 7.8891, lng: -72.5078 };
    const map = new google.maps.Map(document.getElementById("mapa"), {
        zoom: 16,
        center: ubicacion,
    });
    new google.maps.Marker({
        position: ubicacion,
        map: map,
        title: "Los Audaces",
    });
};

export const loadGoogleMaps = () => {
    if (mapLoaded) return;
    mapLoaded = true;
    // El script de Google Maps ya se carga desde el HTML
};