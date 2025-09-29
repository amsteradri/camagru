// Editor de fotos con posicionamiento manual de stickers
class CamagruEditor {
    constructor() {
        // Evitar múltiples inicializaciones
        if (window.camagruEditorInitialized) {
            console.log('CamagruEditor ya inicializado');
            return window.camagruEditor;
        }
        
        console.log('Construyendo nueva instancia de CamagruEditor');
        window.camagruEditorInitialized = true;
        this.video = document.getElementById('webcam-video');
        this.canvas = document.getElementById('webcam-canvas');
        this.interactiveCanvas = document.getElementById('interactive-canvas');
        this.previewOverlay = document.getElementById('preview-overlay');
        this.captureBtn = document.getElementById('capture-btn');
        this.uploadSection = document.getElementById('upload-section');
        this.uploadForm = document.getElementById('upload-form');
        this.imageUpload = document.getElementById('image-upload');
        this.uploadBtn = document.getElementById('upload-btn');
        this.toggleUploadBtn = document.getElementById('toggle-upload');
        this.clearBtn = document.getElementById('clear-stickers');
        this.instructions = document.getElementById('instructions');
        
        this.selectedSticker = null;
        this.stickerImage = null;
        this.positionedStickers = [];
        this.stream = null;
        this.csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
        this.isDragging = false;
        this.dragSticker = null;
        this.dragOffset = { x: 0, y: 0 };
        this.isCapturing = false;
        this.isDeleting = false;
        this.isUploading = false;
        
        this.init();
    }

    init() {
        this.initWebcam();
        this.initStickers();
        this.initUpload();
        this.initEventListeners();
        this.initInteractiveCanvas();
    }

    async initWebcam() {
        try {
            this.stream = await navigator.mediaDevices.getUserMedia({ 
                video: { 
                    width: { ideal: 640 }, 
                    height: { ideal: 480 } 
                } 
            });
            
            this.video.srcObject = this.stream;
            this.video.play();
            
            // Ajustar canvas cuando el video carga
            this.video.addEventListener('loadedmetadata', () => {
                this.adjustCanvasSize();
                this.enableCapture(); // Habilitar botón de captura
            });
            
            const errorMsg = document.getElementById('webcam-error');
            if (errorMsg) {
                errorMsg.style.display = 'none';
            }
            
        } catch (error) {
            console.error('Error accessing webcam:', error);
            this.showWebcamError();
        }
    }

    adjustCanvasSize() {
        const rect = this.video.getBoundingClientRect();
        this.interactiveCanvas.style.width = rect.width + 'px';
        this.interactiveCanvas.style.height = rect.height + 'px';
        
        // Mantener resolución real para captura
        this.interactiveCanvas.width = 640;
        this.interactiveCanvas.height = 480;
    }

    showWebcamError() {
        // Crear mensaje de error si no existe
        let errorMsg = document.getElementById('webcam-error');
        if (!errorMsg) {
            errorMsg = document.createElement('div');
            errorMsg.id = 'webcam-error';
            errorMsg.className = 'alert alert-warning';
            errorMsg.innerHTML = `
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Webcam no disponible</strong><br>
                No se pudo acceder a la cámara. Puedes subir una imagen en su lugar.
            `;
            this.video.parentNode.insertBefore(errorMsg, this.video);
        }
        
        // Ocultar video y mostrar sección de subida
        this.video.style.display = 'none';
        this.showUploadSection();
    }

    initStickers() {
        const stickerOptions = document.querySelectorAll('.sticker-option');
        
        stickerOptions.forEach(option => {
            option.addEventListener('click', () => {
                // Quitar selección anterior
                stickerOptions.forEach(opt => opt.classList.remove('selected'));
                
                // Marcar como seleccionado
                option.classList.add('selected');
                this.selectedSticker = option.dataset.sticker;
                
                // Cargar imagen del sticker
                this.stickerImage = new Image();
                this.stickerImage.onload = () => {
                    this.showInstructions();
                    this.enableCapture();
                };
                this.stickerImage.src = option.src;
                
                // Feedback visual
                this.animateSelection(option);
            });
        });
    }

    animateSelection(option) {
        option.style.transform = 'scale(1.1)';
        setTimeout(() => {
            option.style.transform = '';
        }, 200);
    }

    showInstructions() {
        if (this.instructions) {
            this.instructions.style.display = 'block';
            this.instructions.scrollIntoView({ behavior: 'smooth' });
        }
    }

    enableCapture() {
        // Siempre habilitar captura si hay cámara
        if (this.captureBtn && this.video && this.video.videoWidth > 0) {
            this.captureBtn.disabled = false;
        }
        if (this.uploadBtn && this.imageUpload?.files.length > 0) {
            this.uploadBtn.disabled = false;
        }
    }

    initInteractiveCanvas() {
        if (!this.interactiveCanvas) return;

        // Evento para colocar stickers
        this.interactiveCanvas.addEventListener('click', (e) => {
            if (this.selectedSticker && this.stickerImage && !this.isDragging) {
                this.addStickerAtPosition(e);
            }
        });

        // Mostrar canvas cuando hay sticker seleccionado
        this.interactiveCanvas.classList.add('active');
    }

    addStickerAtPosition(e) {
        const rect = this.interactiveCanvas.getBoundingClientRect();
        const containerRect = this.video.getBoundingClientRect();
        
        // Calcular posición relativa al contenedor de video
        const x = e.clientX - containerRect.left;
        const y = e.clientY - containerRect.top;

        // Crear elemento DOM del sticker
        const stickerElement = document.createElement('img');
        stickerElement.src = this.stickerImage.src;
        stickerElement.className = 'positioned-sticker';
        stickerElement.style.left = Math.max(0, x - 25) + 'px';
        stickerElement.style.top = Math.max(0, y - 25) + 'px';
        stickerElement.style.width = '50px';
        stickerElement.style.height = '50px';
        stickerElement.style.position = 'absolute';
        stickerElement.draggable = false;

        // Crear objeto sticker para datos
        const sticker = {
            id: Date.now() + Math.random(),
            filename: this.selectedSticker,
            element: stickerElement,
            x: (x / containerRect.width) * 640, // Coordenadas para el servidor
            y: (y / containerRect.height) * 480,
            width: 50,
            height: 50
        };

        // Agregar eventos de arrastre al elemento
        this.addDragEvents(stickerElement, sticker);

        // Agregar al DOM y al array
        this.previewOverlay.appendChild(stickerElement);
        this.positionedStickers.push(sticker);
        this.showClearButton();
    }

    addDragEvents(element, sticker) {
        let isDragging = false;
        let dragOffset = { x: 0, y: 0 };

        const startDrag = (e) => {
            e.preventDefault();
            isDragging = true;
            element.classList.add('dragging');
            
            const clientX = e.clientX || (e.touches && e.touches[0].clientX);
            const clientY = e.clientY || (e.touches && e.touches[0].clientY);
            const rect = element.getBoundingClientRect();
            
            dragOffset.x = clientX - rect.left;
            dragOffset.y = clientY - rect.top;
            
            document.body.style.userSelect = 'none';
        };

        const drag = (e) => {
            if (!isDragging) return;
            e.preventDefault();
            
            const clientX = e.clientX || (e.touches && e.touches[0].clientX);
            const clientY = e.clientY || (e.touches && e.touches[0].clientY);
            const containerRect = this.video.getBoundingClientRect();
            
            let newX = clientX - containerRect.left - dragOffset.x;
            let newY = clientY - containerRect.top - dragOffset.y;
            
            // Limitar bounds
            newX = Math.max(0, Math.min(newX, containerRect.width - 50));
            newY = Math.max(0, Math.min(newY, containerRect.height - 50));
            
            element.style.left = newX + 'px';
            element.style.top = newY + 'px';
            
            // Actualizar coordenadas para el servidor
            sticker.x = (newX / containerRect.width) * 640;
            sticker.y = (newY / containerRect.height) * 480;
        };

        const endDrag = () => {
            if (isDragging) {
                isDragging = false;
                element.classList.remove('dragging');
                document.body.style.userSelect = '';
            }
        };

        // Eventos mouse
        element.addEventListener('mousedown', startDrag);
        document.addEventListener('mousemove', drag);
        document.addEventListener('mouseup', endDrag);

        // Eventos touch
        element.addEventListener('touchstart', startDrag);
        document.addEventListener('touchmove', drag);
        document.addEventListener('touchend', endDrag);
    }

    // Métodos antiguos eliminados - ahora usamos DOM elements para drag & drop

    showClearButton() {
        if (this.clearBtn && this.positionedStickers.length > 0) {
            this.clearBtn.style.display = 'inline-block';
        }
    }

    clearAllStickers() {
        // Limpiar el array de stickers
        this.positionedStickers = [];
        
        // Limpiar elementos DOM del overlay
        if (this.previewOverlay) {
            const stickers = this.previewOverlay.querySelectorAll('.positioned-sticker');
            stickers.forEach(sticker => sticker.remove());
        }
        
        // Ocultar botón de limpiar
        if (this.clearBtn) {
            this.clearBtn.style.display = 'none';
        }
        
        // Deshabilitar botón de captura
        if (this.captureBtn) {
            this.captureBtn.disabled = true;
        }
        
        // Mostrar instrucciones
        if (this.instructions) {
            this.instructions.style.display = 'block';
        }
    }

    initUpload() {
        if (this.toggleUploadBtn) {
            this.toggleUploadBtn.addEventListener('click', () => {
                this.showUploadSection();
            });
        }

        if (this.imageUpload) {
            this.imageUpload.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    this.validateUploadFile(file);
                }
            });
        }

        if (this.uploadForm) {
            this.uploadForm.addEventListener('submit', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                // Prevenir múltiples submissions
                if (this.isUploading) {
                    console.log('Upload ya en progreso, ignorando');
                    return;
                }
                
                this.handleUpload();
            });
        }
    }

    showUploadSection() {
        if (this.uploadSection) {
            this.uploadSection.style.display = 'block';
            this.uploadSection.scrollIntoView({ behavior: 'smooth' });
        }
    }

    validateUploadFile(file) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (!allowedTypes.includes(file.type)) {
            this.showAlert('error', 'Tipo de archivo no permitido. Use JPG, PNG o GIF.');
            this.imageUpload.value = '';
            this.uploadBtn.disabled = true;
            return false;
        }

        if (file.size > maxSize) {
            this.showAlert('error', 'El archivo es muy grande. Máximo 5MB.');
            this.imageUpload.value = '';
            this.uploadBtn.disabled = true;
            return false;
        }

        // Habilitar botón si también hay sticker seleccionado
        if (this.selectedSticker && this.uploadBtn) {
            this.uploadBtn.disabled = false;
        }

        return true;
    }

    initEventListeners() {
        // Botón de captura - remover listener existente antes de agregar
        if (this.captureBtn) {
            // Prevenir envío de formulario si el botón está en un form
            this.captureBtn.type = 'button';
            
            this.captureBtn.removeEventListener('click', this.boundCaptureHandler);
            this.boundCaptureHandler = (e) => {
                console.log('🔄 CLICK CAPTURA RECIBIDO:', {
                    timestamp: new Date().toISOString(),
                    eventType: e.type,
                    buttonText: e.target.textContent.trim(),
                    isCapturing: this.isCapturing
                });
                
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                // Verificar que el botón no esté deshabilitado
                if (e.target.disabled) {
                    console.log('🚫 BOTÓN DESHABILITADO, ignorando click');
                    return;
                }
                
                this.capturePhoto();
            };
            this.captureBtn.addEventListener('click', this.boundCaptureHandler, { once: false, passive: false });
        }

        // Botón de limpiar stickers
        if (this.clearBtn) {
            this.clearBtn.removeEventListener('click', this.boundClearHandler);
            this.boundClearHandler = () => this.clearAllStickers();
            this.clearBtn.addEventListener('click', this.boundClearHandler);
        }

        // Limpiar stream al salir
        window.addEventListener('beforeunload', () => {
            this.cleanup();
        });

        // Eventos de eliminación de imágenes - usar delegación una sola vez
        if (!document.camagruDeleteHandlerAdded) {
            document.addEventListener('click', (e) => {
                if (e.target.closest('.delete-image-btn')) {
                    // Buscar la instancia del editor
                    if (window.camagruEditor) {
                        window.camagruEditor.handleDeleteImage(e.target.closest('.delete-image-btn'));
                    }
                }
            });
            document.camagruDeleteHandlerAdded = true;
        }

        // Redimensionar canvas cuando cambie la ventana
        window.addEventListener('resize', () => {
            this.adjustCanvasSize();
        });
    }

    async capturePhoto() {
        const currentTime = Date.now();
        
        // Prevenir múltiples capturas simultáneas
        if (this.isCapturing) {
            console.log('🚫 CAPTURA BLOQUEADA: Captura ya en progreso');
            return;
        }
        
        // Protección contra doble-click (debounce)
        if (currentTime - this.lastCaptureTime < this.captureDebounceMs) {
            console.log('🚫 CAPTURA BLOQUEADA: Debounce activo', {
                tiempoTranscurrido: currentTime - this.lastCaptureTime,
                minimoRequerido: this.captureDebounceMs
            });
            return;
        }
        
        console.log('✅ CAPTURA INICIADA:', {
            timestamp: new Date().toISOString(),
            stickers: this.positionedStickers.length,
            isCapturing: this.isCapturing,
            lastCaptureTime: this.lastCaptureTime
        });
        
        this.isCapturing = true;
        this.lastCaptureTime = currentTime;
        
        try {
            // Permitir captura con o sin stickers
            await this.handleCapture();
        } finally {
            this.isCapturing = false;
            console.log('✅ CAPTURA FINALIZADA:', new Date().toISOString());
        }
    }



    async handleCapture() {
        if (!this.video || this.video.videoWidth === 0) {
            this.showAlert('error', 'Por favor inicia la cámara primero');
            return;
        }

        if (!this.stream) {
            this.showAlert('error', 'Webcam no disponible');
            return;
        }

        try {
            // Configurar canvas principal para captura
            this.canvas.width = 640;
            this.canvas.height = 480;
            const ctx = this.canvas.getContext('2d');

            // Dibujar frame del video
            ctx.drawImage(this.video, 0, 0, this.canvas.width, this.canvas.height);

            // Convertir a base64
            const imageData = this.canvas.toDataURL('image/png');

            // Mostrar loading
            this.setLoadingState(this.captureBtn, true);

            // Enviar al servidor
            await this.sendCaptureToServer(imageData);

        } catch (error) {
            console.error('Error capturing photo:', error);
            this.showAlert('error', 'Error al capturar la foto');
        } finally {
            this.setLoadingState(this.captureBtn, false);
        }
    }

    async sendCaptureToServer(imageData) {
        console.log('=== CAPTURE DEBUG START ===');
        console.log('Enviando imagen al servidor, stickers:', this.positionedStickers.length);
        console.log('Stickers data:', this.positionedStickers);
        console.log('isCapturing flag:', this.isCapturing);
        
        const formData = new FormData();
        formData.append('image_data', imageData);
        
        // Enviar datos de todos los stickers posicionados
        const stickersData = this.positionedStickers.map(sticker => ({
            filename: sticker.filename,
            x: Math.round(sticker.x),
            y: Math.round(sticker.y),
            width: sticker.width,
            height: sticker.height
        }));
        
        formData.append('stickers_data', JSON.stringify(stickersData));
        formData.append('csrf_token', this.csrfToken);

        const response = await fetch('/editor/capture', {
            method: 'POST',
            body: formData
        });

        console.log('Response status:', response.status);
        const result = await response.json();
        console.log('Server response:', result);
        console.log('=== CAPTURE DEBUG END ===');

        if (result.success) {
            this.showAlert('success', 'Foto capturada y guardada exitosamente');
            this.addImageToGallery(result); // Agregar imagen a la galería dinámicamente
            // No limpiar automáticamente - dejar que el usuario decida
        } else {
            this.showAlert('error', result.message || 'Error al capturar foto');
        }
    }

    async handleUpload() {
        console.log('=== UPLOAD DEBUG START ===');
        console.log('Upload iniciado, stickers:', this.positionedStickers.length);
        
        // Prevenir múltiples uploads simultáneos
        if (this.isUploading) {
            console.log('Upload ya en progreso, ignorando');
            return;
        }
        
        this.isUploading = true;
        
        // Permitir subida con o sin stickers
        const file = this.imageUpload?.files[0];
        if (!file) {
            this.showAlert('error', 'Selecciona una imagen para subir');
            this.isUploading = false;
            return;
        }

        try {
            this.setLoadingState(this.uploadBtn, true);

            const formData = new FormData();
            formData.append('image', file);
            
            // Enviar datos de todos los stickers posicionados
            const stickersData = this.positionedStickers.map(sticker => ({
                filename: sticker.filename,
                x: Math.round(sticker.x),
                y: Math.round(sticker.y),
                width: sticker.width,
                height: sticker.height
            }));
            
            formData.append('stickers_data', JSON.stringify(stickersData));
            formData.append('csrf_token', this.csrfToken);

            const response = await fetch('/editor/upload', {
                method: 'POST',
                body: formData
            });

            console.log('Upload response status:', response.status);
            const result = await response.json();
            console.log('Upload server response:', result);
            console.log('=== UPLOAD DEBUG END ===');

            if (result.success) {
                this.showAlert('success', 'Imagen procesada y guardada exitosamente');
                this.addImageToGallery(result); // Agregar imagen a la galería dinámicamente
                this.uploadForm.reset();
                this.uploadBtn.disabled = true;
                // No limpiar automáticamente - dejar que el usuario decida
            } else {
                this.showAlert('error', result.message || 'Error al procesar imagen');
            }

        } catch (error) {
            console.error('Error uploading image:', error);
            this.showAlert('error', 'Error de conexión');
        } finally {
            this.setLoadingState(this.uploadBtn, false);
            this.isUploading = false;
            console.log('Upload finalizado');
        }
    }

    async handleDeleteImage(button) {
        // Prevenir múltiples eliminaciones simultáneas
        if (this.isDeleting) {
            return;
        }
        
        if (!confirm('¿Estás seguro de que quieres eliminar esta imagen? Esta acción no se puede deshacer.')) {
            return;
        }
        
        this.isDeleting = true;

        const imageId = button.dataset.imageId;

        try {
            const response = await fetch(`/editor/deleteImage/${imageId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `csrf_token=${this.csrfToken}`
            });

            const result = await response.json();

            if (result.success) {
                // Eliminar elemento del DOM con animación
                const imageElement = button.closest('.user-image-card');
                if (imageElement) {
                    imageElement.style.opacity = '0';
                    imageElement.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        imageElement.remove();
                        this.updateGalleryCount();
                    }, 300);
                }
                
                this.showAlert('success', 'Imagen eliminada exitosamente');
            } else {
                this.showAlert('error', result.message || 'Error al eliminar imagen');
            }

        } catch (error) {
            console.error('Error deleting image:', error);
            this.showAlert('error', 'Error de conexión');
        } finally {
            this.isDeleting = false;
        }
    }

    refreshUserGallery() {
        // Actualizar contador sin recargar la página
        this.updateGalleryCount();
        console.log('Galería actualizada sin recarga');
    }

    addImageToGallery(imageData) {
        const gallery = document.getElementById('user-gallery');
        if (!gallery) {
            console.warn('Gallery element not found');
            return;
        }

        // Eliminar mensaje "no tienes fotos" si existe
        const emptyMessage = gallery.querySelector('.text-center.text-muted');
        if (emptyMessage) {
            emptyMessage.remove();
        }

        // Crear HTML para la nueva imagen con el formato correcto
        const imageHtml = `
            <div class="user-image-card" data-image-id="${imageData.image_id}">
                <img src="${imageData.image_url}" 
                     alt="Mi imagen"
                     loading="lazy">
                <div class="user-image-overlay">
                    <button class="btn btn-danger btn-sm delete-image-btn" 
                            data-image-id="${imageData.image_id}"
                            title="Eliminar imagen">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;

        // Agregar al principio de la galería (primera posición en el grid)
        gallery.insertAdjacentHTML('afterbegin', imageHtml);

        // Actualizar contador
        this.updateGalleryCount();

        console.log('✅ Nueva imagen agregada a la galería:', imageData);
    }

    updateGalleryCount() {
        const gallery = document.getElementById('user-gallery');
        if (!gallery) return;
        
        const images = gallery.querySelectorAll('.user-image-card');
        const countElement = document.querySelector('.card-title small');
        
        if (countElement) {
            countElement.textContent = `(${images.length})`;
        }

        // Mostrar mensaje si no hay imágenes
        if (images.length === 0) {
            gallery.innerHTML = `
                <div class="text-center text-muted py-3">
                    <i class="bi bi-camera2 display-6"></i>
                    <p class="mt-2">Aún no tienes fotos.<br>¡Captura tu primera imagen!</p>
                </div>
            `;
        }
    }

    setLoadingState(button, isLoading) {
        if (!button) return;

        if (isLoading) {
            button.disabled = true;
            button.dataset.originalText = button.innerHTML;
            button.innerHTML = '<i class="bi bi-arrow-clockwise spin me-2"></i>Procesando...';
        } else {
            button.disabled = false;
            button.innerHTML = button.dataset.originalText || button.innerHTML;
        }
    }

    showAlert(type, message) {
        // Usar la función global del app principal
        if (window.camagru && window.camagru.showAlert) {
            window.camagru.showAlert(type, message);
        } else {
            // Fallback simple
            alert(message);
        }
    }

    cleanup() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
        }
    }
}

// Inicializar editor cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('webcam-video') && !window.camagruEditor) {
        console.log('Inicializando CamagruEditor');
        window.camagruEditor = new CamagruEditor();
    } else if (window.camagruEditor) {
        console.log('CamagruEditor ya inicializado, ignorando');
    }
});