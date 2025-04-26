// politicas.js
export const showPoliticas = () => {
    // Cargar el JSON de políticas
    fetch('politicas.json')
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al cargar las políticas');
            }
            return response.json();
        })
        .then(data => {
            // Configurar eventos para cada enlace del footer
            setupPoliticasLinks(data);
        })
        .catch(error => {
            console.error('Error al cargar las políticas:', error);
            // Mostrar mensaje de error en el modal si falla la carga
            setupPoliticasLinks({
                error: "No se pudieron cargar las políticas. Por favor, intente más tarde."
            });
        });

    // Configurar el cierre del modal
    const closeButton = document.querySelector('.close-button');
    if (closeButton) {
        closeButton.addEventListener('click', () => {
            const modal = document.getElementById('modalPolitica');
            if (modal) modal.style.display = 'none';
        });
    }

    // Cerrar modal al hacer clic fuera del contenido
    window.addEventListener('click', (event) => {
        const modal = document.getElementById('modalPolitica');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
};

const setupPoliticasLinks = (politicasData) => {
    // Mapeo de IDs a las claves del JSON y funciones de procesamiento específicas
    const politicasMap = {
        'foo_tc': {
            title: 'Términos y Condiciones',
            content: () => formatTerminosCondiciones(politicasData.terminos_condiciones)
        },
        'foo_pr': {
            title: 'Política de Reembolso',
            content: () => formatPoliticaReembolso(politicasData.terminos_condiciones.politicas_generales.politica_reembolso)
        },
        'foo_pp': {
            title: 'Política de Privacidad',
            content: () => {
                const privacidad = politicasData.terminos_condiciones.politica_privacidad;
                const verificacion = privacidad.verificacion_identidad; // Cambio clave aquí
                return formatPoliticaPrivacidad(privacidad, verificacion);
            }
        },
        'foo_pe': {
            title: 'Política de Envío',
            content: () => `<p>Los premios se entregan personalmente en un lugar designado por Los Audaces, previa coordinación con el ganador.</p>`
        },
        'foo_pc': {
            title: 'Política de Cookies',
            content: () => formatPoliticaCookies(politicasData.politica_cookies)
        },
        'foo_fa': {
            title: 'Preguntas Frecuentes (FAQ)',
            content: () => formatFAQ(politicasData.faqs)
        }
    };

    // Configurar eventos para cada enlace
    Object.keys(politicasMap).forEach(id => {
        const link = document.getElementById(id);
        if (link) {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                showModalPolitica(
                    politicasMap[id].title,
                    politicasMap[id].content()
                );
            });
        }
    });
};

const showModalPolitica = (title, content) => {
    const modal = document.getElementById('modalPolitica');
    const modalTitle = document.getElementById('modalTitulo');
    const modalContent = document.getElementById('modalContenido');

    if (modal && modalTitle && modalContent) {
        modalTitle.textContent = title;
        modalContent.innerHTML = content;
        modal.style.display = 'block';
    }
};

// Funciones específicas para formatear cada tipo de política
const formatTerminosCondiciones = (terminos) => {
    let html = `<h3>${terminos.titulo}</h3>`;
    
    // Políticas generales
    html += `<h4>Políticas Generales</h4>`;
    
    // Política de compra
    html += `<h5>Política de Compra</h5>`;
    html += `<p><strong>Edad mínima:</strong> ${terminos.politicas_generales.politica_compra.edad_minima.general}`;
    if (terminos.politicas_generales.politica_compra.edad_minima.excepciones) {
        html += `<br><strong>Excepciones:</strong> Honduras (${terminos.politicas_generales.politica_compra.edad_minima.excepciones.Honduras})</p>`;
    }
    
    html += `<h6>Selección de números:</h6><ul>`;
    terminos.politicas_generales.politica_compra.seleccion_numeros.forEach(item => {
        html += `<li>${item}</li>`;
    });
    html += `</ul>`;
    
    html += `<p><strong>Conflictos de números:</strong> ${terminos.politicas_generales.politica_compra.conflictos_numeros}</p>`;
    html += `<p><strong>Entrega de tickets:</strong> ${terminos.politicas_generales.politica_compra.entrega_tickets}</p>`;
    
    // Política de premios
    html += `<h5>Política de Premios</h5>`;
    html += `<p><strong>Entrega:</strong> ${terminos.politicas_generales.politica_premios.entrega}</p>`;
    html += `<p><strong>Plazo de reclamo:</strong> ${terminos.politicas_generales.politica_premios.plazo_reclamo}</p>`;
    html += `<p><strong>Transferibilidad:</strong> ${terminos.politicas_generales.politica_premios.transferibilidad}</p>`;
    
    // Política de privacidad
    html += `<h5>Política de Privacidad</h5>`;
    html += `<p><strong>Protección de datos:</strong> ${terminos.politica_privacidad.proteccion_datos}</p>`;
    html += `<p><strong>Uso de imagen:</strong> ${terminos.politica_privacidad.uso_imagen}</p>`;
    
    return html;
};

const formatPoliticaReembolso = (politica) => {
    let html = `<h3>Política de Reembolso</h3>`;
    
    html += `<p><strong>Cancelación de sorteo:</strong> ${politica.cancelacion_sorteo}</p>`;
    html += `<p><strong>Pagos no confirmados:</strong> ${politica.pagos_no_confirmados}</p>`;
    html += `<p><strong>Plazo de reembolso:</strong> ${politica.plazo_reembolso}</p>`;
    
    return html;
};

const formatPoliticaPrivacidad = (politica, verificacion = null) => {
    let html = `<h3>Política de Privacidad</h3>`;
    
    // Contenido básico
    html += `<section class="politica-section">
        <h4>1. Protección de Datos</h4>
        <p>${politica.proteccion_datos}</p>
    </section>`;
    
    html += `<section class="politica-section">
        <h4>2. Uso de Imagen</h4>
        <p>${politica.uso_imagen}</p>
    </section>`;
    
    // Verificación de identidad (si se proporciona)
    if (verificacion) {
        html += `<section class="politica-section">
            <h4>3. Verificación de Identidad</h4>`;
        
        if (verificacion.documentacion_requerida?.ganador) {
            html += `<h5>Documentación requerida para ganadores:</h5><ul>`;
            verificacion.documentacion_requerida.ganador.forEach(item => {
                html += `<li>${item}</li>`;
            });
            html += `</ul>`;
        }
        
        if (verificacion.documentacion_requerida?.terceros) {
            html += `<h5>Para terceros:</h5><ul>`;
            verificacion.documentacion_requerida.terceros.forEach(item => {
                html += `<li>${item}</li>`;
            });
            html += `</ul>`;
        }
        
        if (verificacion.validacion_gobierno) {
            html += `<h5>Validación con Autoridades:</h5>
            <p>${verificacion.validacion_gobierno}</p>`;
        }
        
        html += `</section>`;
    }
    
    return html;
};
const formatPoliticaCookies = (politica) => {
    let html = `<h3>Política de Cookies</h3>`;
    
    html += `<p>${politica.definicion}</p>`;
    
    html += `<h4>Tipos de cookies que utilizamos:</h4><ul>`;
    politica.tipos_cookies.forEach(tipo => {
        html += `<li><strong>${tipo.nombre}:</strong> ${tipo.descripcion}</li>`;
    });
    html += `</ul>`;
    
    html += `<h4>Gestión de cookies</h4>`;
    html += `<p>${politica.desactivacion.instrucciones}</p>`;
    html += `<p><strong>Advertencia:</strong> ${politica.desactivacion.advertencia}</p>`;
    
    html += `<h4>Enlaces útiles</h4><ul>`;
    politica.enlaces_utiles.forEach(enlace => {
        html += `<li><a href="${enlace.url}" target="_blank">${enlace.texto}</a></li>`;
    });
    html += `</ul>`;
    
    html += `<p><strong>Última actualización:</strong> ${politica.actualizaciones.fecha_actualizacion}</p>`;
    
    return html;
};

const formatFAQ = (faqs) => {
    let html = `<h3>Preguntas Frecuentes</h3>`;
    
    // Premios
    html += `<h4>Premios</h4>`;
    faqs.premios.forEach(item => {
        html += `<p><strong>${item.pregunta}</strong><br>${item.respuesta}</p>`;
    });
    
    // Pagos
    html += `<h4>Pagos</h4>`;
    faqs.pagos.forEach(item => {
        html += `<p><strong>${item.pregunta}</strong><br>${item.respuesta}</p>`;
    });
    
    // Verificación de resultados
    html += `<h4>Verificación de resultados</h4>`;
    faqs.verificacion_resultados.forEach(item => {
        html += `<p><strong>${item.pregunta}</strong><br>${item.respuesta}</p>`;
    });
    
    // Soporte
    html += `<h4>Soporte</h4>`;
    faqs.soporte.forEach(item => {
        html += `<p><strong>${item.pregunta}</strong><br>${item.respuesta}</p>`;
    });
    
    return html;
};