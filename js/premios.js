// Galería de premios
export const initPremiosGallery = (premiosData) => {
    const thumbItems = document.querySelectorAll('.thumb-item');
    const detalleImagen = document.getElementById('detalle-imagen');
    const detalleInfo = document.getElementById('detalle-info');

    if (thumbItems.length > 0 && premiosData.length > 0) {
        thumbItems[0].classList.add('active');

        thumbItems.forEach((item, index) => {
            item.addEventListener('click', () => {
                thumbItems.forEach(thumb => thumb.classList.remove('active'));
                item.classList.add('active');

                const premio = premiosData[index];
                if (premio) {
                    detalleImagen.src = 'resources/premios/' + premio.foto;
                    detalleImagen.alt = premio.name;

                    detalleInfo.innerHTML = `
                        <h2>${premio.name}</h2>
                        <p>${premio.descripcion || ''}</p>
                        <div class="premio-caracteristicas">
                            ${premio.valor ? `<p><strong>Valor:</strong> $${parseFloat(premio.valor).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>` : ''}
                            <p><strong>Posición:</strong> ${premio.posicion}</p>
                        </div>
                    `;
                }
            });
        });
    }
};

// Slider de premios
export const setupPremiosSlider = (premiosImages) => {
    const slider = document.querySelector('.slider');
    const dotsContainer = document.querySelector('.slider-dots');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');

    let currentSlide = 0;
    let slideInterval;

    function renderSlider() {
        if (!slider || !dotsContainer) return;
        slider.innerHTML = '';
        dotsContainer.innerHTML = '';

        premiosImages.forEach((src, i) => {
            const slide = document.createElement('div');
            slide.className = 'slide';
            slide.style.minWidth = '100%';

            const img = document.createElement('img');
            img.src = src;
            img.alt = `Premio ${i + 1}`;
            slide.appendChild(img);
            slider.appendChild(slide);

            const dot = document.createElement('div');
            dot.className = 'dot' + (i === 0 ? ' active' : '');
            dot.addEventListener('click', () => goToSlide(i));
            dotsContainer.appendChild(dot);
        });

        startAutoSlide();
    }

    function goToSlide(i) {
        currentSlide = i;
        slider.style.transform = `translateX(-${i * 100}%)`;
        document.querySelectorAll('.dot').forEach((d, index) => d.classList.toggle('active', index === i));
    }

    function nextSlide() {
        goToSlide((currentSlide + 1) % premiosImages.length);
    }

    function prevSlide() {
        goToSlide((currentSlide - 1 + premiosImages.length) % premiosImages.length);
    }

    function startAutoSlide() {
        clearInterval(slideInterval);
        slideInterval = setInterval(nextSlide, 5000);
    }

    nextBtn?.addEventListener('click', () => {
        nextSlide();
        startAutoSlide();
    });
    prevBtn?.addEventListener('click', () => {
        prevSlide();
        startAutoSlide();
    });

    slider?.addEventListener('mouseenter', () => clearInterval(slideInterval));
    slider?.addEventListener('mouseleave', startAutoSlide);

    renderSlider();
};