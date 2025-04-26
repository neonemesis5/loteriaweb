import { showSection, setupNavigation } from './navigation.js';
import { setupMobileMenu } from './mobileMenu.js';
import { initPremiosGallery, setupPremiosSlider } from './premios.js';
import { setupTicketsTable, loadGoogleMaps, initMap } from './tickets.js';
import { setupAuth } from './auth.js';
import { setupCompra } from './compra.js';
import { showPoliticas } from './politicas.js';
document.addEventListener('DOMContentLoaded', () => {
    // Verificar que los datos están disponibles
    if (!window.appData) {
        console.error('Datos de la aplicación no encontrados');
        return;
    }

    // Configurar navegación
    setupNavigation();
    
    // Configurar menú móvil
    setupMobileMenu();
    
    // Configurar slider de premios
	setupPremiosSlider(window.appData.premiosImages);

    // Configurar tabla de tickets
    setupTicketsTable(
        window.appData.totalNumeros || 0,
        window.appData.numerosVendidos || [],
        window.appData.precioNumero || 0
    );
    
    // Configurar autenticación
    setupAuth();

    //llamada a metodo de compra
    setupCompra();

	showPoliticas();
    // Hacer disponible initMap globalmente para Google Maps
    window.initMap = initMap;
    
    // Si hay premios, inicializar la galería
    if (window.appData.premiosData && window.appData.premiosData.length > 0) {
        document.querySelector('#premios')?.addEventListener('click', () => {
            initPremiosGallery(window.appData.premiosData);
        });
    }
});
