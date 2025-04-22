// Función para mostrar/ocultar secciones
export const showSection = (id) => {
    // Lista de todas las secciones principales
    const allSections = ['home-section', 'rifas-section', 'contacto-section', 'showpremios', 'carton-num', 'shopcart','registroCliente'];
    
    allSections.forEach(sec => {
        const el = document.getElementById(sec);
        if (el) {
            if (sec === id) {
                // Mostrar la sección solicitada
                el.style.display = (sec === 'carton-num' || sec === 'contacto-section') ? 'flex' : 'block';
                
                // Caso especial: si es carton-num, también mostrar shopcart
                if (sec === 'carton-num') {
                    const shopcartEl = document.getElementById('shopcart');
                    if (shopcartEl) shopcartEl.style.display = 'block';
                }
            } else {
                // Ocultar otras secciones (excepto shopcart cuando es carton-num)
                if (!(id === 'carton-num' && sec === 'shopcart')) {
                    el.style.display = 'none';
                }
            }
        }
    });
    
    window.scrollTo(0, 0);
};

// Configuración de eventos de navegación (modificar los listeners relevantes)
export const setupNavigation = () => {
    document.querySelector('#inicio')?.addEventListener('click', e => {
        e.preventDefault();
        showSection('home-section');
    });
    document.querySelector('#rifas')?.addEventListener('click', e => {
        e.preventDefault();
        showSection('rifas-section');
    });
    document.querySelector('#contact')?.addEventListener('click', e => {
        e.preventDefault();
        showSection('contacto-section');
        loadGoogleMaps();
    });
    // Modificar estos listeners para mostrar ambas secciones
    document.querySelector('#comprar_numprin')?.addEventListener('click', e => {
        e.preventDefault();
        showSection('carton-num');
    });
    document.querySelector('#comprar_num')?.addEventListener('click', e => {
        e.preventDefault();
        showSection('carton-num');
    });
    
    document.querySelector('#regcompra')?.addEventListener('click', e => {
        e.preventDefault();
        showSection('registroCliente');
    });

    document.querySelector('#premios')?.addEventListener('click', e => {
        e.preventDefault();
        showSection('showpremios');
        initPremiosGallery();
    });
    
    // Opcional: Añadir botón para alternar solo el carrito
    document.querySelector('.cart-toggle-btn')?.addEventListener('click', e => {
        e.preventDefault();
        const shopcart = document.getElementById('shopcart');
        if (shopcart) {
            shopcart.style.display = shopcart.style.display === 'none' ? 'block' : 'none';
        }
    });
};