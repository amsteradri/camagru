// Editor de fotos con preview unificado
class CamagruEditor {
    constructor() {
        // Evitar múltiples inicializaciones
        if (window.camagruEditorInitialized) {
            console.log('CamagruEditor ya inicializado');
            return window.camagruEditor;
        }
        
        console.log('Construyendo nueva instancia de CamagruEditor');
        window.camagruEditorInitialized = true;
        
        // Elementos del DOM
        this.video = document.getElementById('webcam-video');
        this.canvas = document.getElementById('webcam-canvas');
        this.interactiveCanvas = document.getElementById('interactive-canvas');
        this.captureBtn = document.getElementById('capture-btn');
        this.processBtn = document.getElementById('process-btn');
        this.clearBtn = document.getElementById('clear-stickers');
        this.instructions = document.getElementById('instructions');
        this.imageUpload = document.getElementById('image-upload');
        
        // Estado de la aplicación
        this.selectedSticker = null;
        this.positionedStickers = [];
        this.stream = null;
        this.csrfToken = document.getElementById('csrf-token')?.value;
        this.uploadedImage = null;
        this.currentMode = 'webcam'; // 'webcam' o 'uploaded'
        
        // Flags de protección
        this.isCapturing = false;
        this.isProcessing = false;
        this.isDeleting = false;
        
        // Protección contra doble click
        this.lastActionTime = 0;
        this.actionDebounceMs = 2000;
        
        // Handlers reutilizables
        this.boundCaptureHandler = null;
        this.boundProcessHandler = null;
        this.boundClearHandler = null;
        this.boundUploadHandler = null;
        
        this.init();
    }

    init() {
        this.initWebcam();
        this.initStickers();
        this.initStickerSizeSelector();
        this.initEventListeners();
        this.initInteractiveCanvas();
    }

    async initWebcam() {
        if (!this.video) {
            console.error('Elemento de video no encontrado');
            return;
        }
        
        console.log('Iniciando webcam en editor.js...');
        
        try {
            this.stream = await navigator.mediaDevices.getUserMedia({ 
                video: { width: 640, height: 480 } 
            });
            
            this.video.srcObject = this.stream;
            
            // Manejar promesa de play()
            try {
                await this.video.play();
                console.log('Video reproduciendo correctamente');
            } catch (playError) {
                console.error('Error al reproducir video:', playError);
            }
            
            this.video.addEventListener('loadedmetadata', () => {
                this.adjustCanvasSize();
                console.log('Webcam inicializada y metadatos cargados');
            });
            
        } catch (error) {
            console.error('Error accessing webcam:', error);
            this.showWebcamError();
        }
    }

    adjustCanvasSize() {
        if (this.video && this.canvas && this.interactiveCanvas) {
            const rect = this.video.getBoundingClientRect();
            this.canvas.width = 640;
            this.canvas.height = 480;
            this.interactiveCanvas.width = 640;
            this.interactiveCanvas.height = 480;
        }
    }

    adjustCanvasSizeToImage(img) {
        // Esperar a que la imagen se cargue completamente
        img.onload = () => {
            const rect = img.getBoundingClientRect();
            if (this.interactiveCanvas) {
                this.interactiveCanvas.width = rect.width;
                this.interactiveCanvas.height = rect.height;
                this.interactiveCanvas.style.width = rect.width + 'px';
                this.interactiveCanvas.style.height = rect.height + 'px';
                console.log('Canvas ajustado a imagen:', rect.width, 'x', rect.height);
            }
        };
        
        // Si la imagen ya está cargada
        if (img.complete) {
            img.onload();
        }
    }

    showWebcamError() {
        const container = this.video?.parentElement;
        if (container) {
            container.innerHTML = `
                <div class="text-center p-4 bg-light rounded">
                    <i class="bi bi-camera-video-off display-1 text-muted"></i>
                    <h5 class="mt-3">Webcam no disponible</h5>
                    <p class="text-muted">Puedes subir una imagen desde tu dispositivo</p>
                </div>
            `;
        }
    }

    initStickers() {
        const stickerOptions = document.querySelectorAll('.sticker-option');
        stickerOptions.forEach(option => {
            option.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Remover selección previa
                stickerOptions.forEach(opt => opt.classList.remove('selected'));
                
                // Seleccionar nuevo sticker
                option.classList.add('selected');
                const size = this.getStickerSize();
                this.selectedSticker = {
                    filename: option.dataset.sticker,
                    width: size,
                    height: size
                };
                
                this.animateSelection(option);
                this.showInstructions();
                this.enableActions();
            });
        });
    }

    animateSelection(option) {
        option.style.transform = 'scale(0.95)';
        setTimeout(() => {
            option.style.transform = 'scale(1)';
        }, 150);
    }

    initStickerSizeSelector() {
        const sizeSelector = document.getElementById('sticker-size');
        if (sizeSelector) {
            sizeSelector.addEventListener('change', () => {
                // Si hay un sticker seleccionado, actualizar su tamaño
                if (this.selectedSticker) {
                    const size = this.getStickerSize();
                    this.selectedSticker.width = size;
                    this.selectedSticker.height = size;
                    console.log('Tamaño de sticker actualizado a:', size);
                }
            });
        }
    }

    getStickerSize() {
        const sizeSelector = document.getElementById('sticker-size');
        return sizeSelector ? parseInt(sizeSelector.value) : 120;
    }

    showInstructions() {
        if (this.instructions) {
            this.instructions.style.display = 'block';
        }
    }

    enableActions() {
        if (this.currentMode === 'webcam' && this.captureBtn) {
            this.captureBtn.disabled = false;
        } else if (this.currentMode === 'uploaded' && this.processBtn) {
            this.processBtn.disabled = false;
        }
    }

    initInteractiveCanvas() {
        if (!this.interactiveCanvas) return;
        
        this.interactiveCanvas.addEventListener('click', (e) => {
            if (this.selectedSticker) {
                this.addStickerAtPosition(e);
            }
        });
    }

    addStickerAtPosition(e) {
        if (!this.selectedSticker) return;
        
        const rect = this.interactiveCanvas.getBoundingClientRect();
        const x = e.clientX - rect.left - (this.selectedSticker.width / 2);
        const y = e.clientY - rect.top - (this.selectedSticker.height / 2);
        
        // Obtener dimensiones reales del canvas/imagen
        const canvasWidth = this.interactiveCanvas.width || 640;
        const canvasHeight = this.interactiveCanvas.height || 480;
        
        const sticker = {
            ...this.selectedSticker,
            x: Math.max(0, Math.min(x, canvasWidth - this.selectedSticker.width)),
            y: Math.max(0, Math.min(y, canvasHeight - this.selectedSticker.height)),
            id: Date.now()
        };
        
        this.positionedStickers.push(sticker);
        this.createStickerElement(sticker);
        this.showClearButton();
        
        console.log('Sticker añadido:', sticker, 'Canvas size:', canvasWidth, 'x', canvasHeight);
    }

    createStickerElement(sticker) {
        const element = document.createElement('div');
        element.className = 'positioned-sticker';
        element.style.left = sticker.x + 'px';
        element.style.top = sticker.y + 'px';
        element.style.width = sticker.width + 'px';
        element.style.height = sticker.height + 'px';
        element.style.backgroundImage = `url(/stickers/${sticker.filename})`;
        element.style.backgroundSize = 'contain';
        element.style.backgroundRepeat = 'no-repeat';
        element.style.backgroundPosition = 'center';
        element.style.position = 'absolute';
        element.style.zIndex = '4';
        element.dataset.stickerId = sticker.id;
        
        this.addDragEvents(element, sticker);
        
        // Agregar al contenedor correcto (no al canvas, sino al contenedor padre)
        const container = this.interactiveCanvas.parentElement;
        if (container) {
            container.appendChild(element);
        }
        
        console.log('Elemento sticker creado:', sticker.filename, 'en posición', sticker.x, sticker.y);
    }

    addDragEvents(element, sticker) {
        let isDragging = false;
        let dragOffset = { x: 0, y: 0 };
        
        element.addEventListener('mousedown', (e) => {
            isDragging = true;
            element.classList.add('dragging');
            
            const rect = element.getBoundingClientRect();
            dragOffset.x = e.clientX - rect.left;
            dragOffset.y = e.clientY - rect.top;
            
            e.preventDefault();
        });
        
        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            
            const containerRect = this.interactiveCanvas.getBoundingClientRect();
            const newX = e.clientX - containerRect.left - dragOffset.x;
            const newY = e.clientY - containerRect.top - dragOffset.y;
            
            // Usar dimensiones reales del canvas
            const canvasWidth = this.interactiveCanvas.width || 640;
            const canvasHeight = this.interactiveCanvas.height || 480;
            
            const clampedX = Math.max(0, Math.min(newX, canvasWidth - sticker.width));
            const clampedY = Math.max(0, Math.min(newY, canvasHeight - sticker.height));
            
            element.style.left = clampedX + 'px';
            element.style.top = clampedY + 'px';
            
            sticker.x = clampedX;
            sticker.y = clampedY;
        });
        
        document.addEventListener('mouseup', () => {
            if (isDragging) {
                isDragging = false;
                element.classList.remove('dragging');
            }
        });
    }

    showClearButton() {
        if (this.clearBtn) {
            this.clearBtn.style.display = 'inline-block';
        }
    }

    clearAllStickers() {
        // Remover elementos visuales
        document.querySelectorAll('.positioned-sticker').forEach(el => el.remove());
        
        // Limpiar array
        this.positionedStickers = [];
        
        // Ocultar botón clear
        if (this.clearBtn) {
            this.clearBtn.style.display = 'none';
        }
        
        console.log('Todos los stickers eliminados');
    }

    initEventListeners() {
        // Botón de captura - limpieza agresiva de listeners
        if (this.captureBtn) {
            console.log('Configurando event listeners del botón de captura');
            
            // Remover todos los listeners existentes clonando el botón
            const newCaptureBtn = this.captureBtn.cloneNode(true);
            this.captureBtn.parentNode.replaceChild(newCaptureBtn, this.captureBtn);
            this.captureBtn = newCaptureBtn;
            
            // Configurar el botón
            this.captureBtn.type = 'button';
            
            // Agregar nuevo listener
            this.boundCaptureHandler = (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('Botón de captura clickeado desde editor.js');
                if (!e.target.disabled) {
                    this.capturePhoto();
                }
            };
            this.captureBtn.addEventListener('click', this.boundCaptureHandler);
            
            // Marcar que este botón está controlado por el editor avanzado
            this.captureBtn.dataset.controlledByAdvancedEditor = 'true';
        }

        // Botón de procesar imagen subida
        if (this.processBtn) {
            this.processBtn.type = 'button';
            this.processBtn.removeEventListener('click', this.boundProcessHandler);
            this.boundProcessHandler = (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (!e.target.disabled) {
                    this.processUploadedImage();
                }
            };
            this.processBtn.addEventListener('click', this.boundProcessHandler);
        }

        // Botón de limpiar
        if (this.clearBtn) {
            this.clearBtn.removeEventListener('click', this.boundClearHandler);
            this.boundClearHandler = () => this.clearAllStickers();
            this.clearBtn.addEventListener('click', this.boundClearHandler);
        }

        // Input de archivo
        if (this.imageUpload) {
            this.imageUpload.addEventListener('change', (e) => {
                this.handleFileSelect(e);
            });
        }

        // Eventos de eliminación de imágenes
        if (!document.camagruDeleteHandlerAdded) {
            document.addEventListener('click', (e) => {
                if (e.target.closest('.delete-image-btn')) {
                    if (window.camagruEditor) {
                        window.camagruEditor.handleDeleteImage(e.target.closest('.delete-image-btn'));
                    }
                }
            });
            document.camagruDeleteHandlerAdded = true;
        }

        // Redimensionar canvas
        window.addEventListener('resize', () => {
            this.adjustCanvasSize();
        });
    }

    async handleFileSelect(e) {
        console.log('handleFileSelect iniciado');
        const file = e.target.files[0];
        if (!file) {
            console.log('No se seleccionó archivo');
            return;
        }

        console.log('Archivo seleccionado:', file.name, 'Tamaño:', file.size, 'Tipo:', file.type);

        // Validar archivo
        if (!this.validateFile(file)) {
            console.log('Validación de archivo fallida');
            return;
        }

        try {
            console.log('Iniciando lectura de archivo...');
            // Leer archivo como data URL
            const reader = new FileReader();
            reader.onload = (event) => {
                console.log('Archivo leído exitosamente, tamaño de data:', event.target.result.length);
                this.displayUploadedImage(event.target.result, file);
            };
            reader.onerror = (error) => {
                console.error('Error leyendo archivo:', error);
            };
            reader.readAsDataURL(file);
        } catch (error) {
            console.error('Error loading file:', error);
            this.showAlert('error', 'Error al cargar el archivo');
        }
    }

    validateFile(file) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (!allowedTypes.includes(file.type)) {
            this.showAlert('error', 'Tipo de archivo no permitido. Use JPG, PNG o GIF.');
            this.imageUpload.value = '';
            return false;
        }

        if (file.size > maxSize) {
            this.showAlert('error', 'El archivo es muy grande. Máximo 5MB.');
            this.imageUpload.value = '';
            return false;
        }

        return true;
    }

    displayUploadedImage(dataUrl, file) {
        console.log('Iniciando displayUploadedImage...', file.name);
        
        // Cambiar a modo uploaded
        this.currentMode = 'uploaded';
        this.uploadedImage = { dataUrl, file };

        // Ocultar video
        if (this.video) {
            this.video.style.display = 'none';
            console.log('Video oculto');
        }

        // Buscar el contenedor correcto
        const container = document.querySelector('.webcam-container');
        if (!container) {
            console.error('No se encontró .webcam-container');
            return;
        }
        console.log('Contenedor encontrado:', container);

        // Remover preview anterior si existe
        const existingPreview = container.querySelector('.uploaded-preview');
        if (existingPreview) {
            existingPreview.remove();
            console.log('Preview anterior removido');
        }

        // Crear nuevo preview - simplificado
        const previewImg = document.createElement('img');
        previewImg.className = 'uploaded-preview';
        previewImg.src = dataUrl;
        previewImg.style.cssText = `
            width: 100%;
            max-width: 640px;
            height: auto;
            border-radius: 10px;
            display: block;
        `;

        // Reemplazar el contenido del video directamente
        container.insertBefore(previewImg, container.firstChild);
        console.log('Preview imagen creada y añadida');

        // Asegurar que el canvas interactivo esté visible y funcionando
        if (this.interactiveCanvas) {
            this.interactiveCanvas.style.display = 'block';
            this.interactiveCanvas.style.zIndex = '10';
            console.log('Canvas interactivo configurado');
        }

        // Cambiar botones
        if (this.captureBtn) {
            this.captureBtn.style.display = 'none';
        }
        if (this.processBtn) {
            this.processBtn.style.display = 'inline-block';
            this.processBtn.disabled = !this.selectedSticker;
        }

        console.log('Imagen cargada en preview exitosamente:', file.name);
        this.showAlert('success', 'Imagen cargada. Ahora selecciona un sticker y haz clic para añadirlo.');
    }

    async capturePhoto() {
        if (this.isCapturing) return;
        
        const currentTime = Date.now();
        if (currentTime - this.lastActionTime < this.actionDebounceMs) return;
        
        this.isCapturing = true;
        this.lastActionTime = currentTime;
        
        try {
            await this.handleCapture();
        } finally {
            this.isCapturing = false;
        }
    }

    async handleCapture() {
        if (!this.video || this.video.videoWidth === 0 || !this.stream) {
            this.showAlert('error', 'Webcam no disponible');
            return;
        }

        try {
            // Configurar canvas para captura
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

    async processUploadedImage() {
        if (this.isProcessing || !this.uploadedImage) return;
        
        const currentTime = Date.now();
        if (currentTime - this.lastActionTime < this.actionDebounceMs) return;
        
        this.isProcessing = true;
        this.lastActionTime = currentTime;
        
        try {
            this.setLoadingState(this.processBtn, true);

            const formData = new FormData();
            formData.append('image', this.uploadedImage.file);
            
            // Enviar datos de stickers
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

            const result = await response.json();

            if (result.success) {
                this.showAlert('success', 'Imagen procesada y guardada exitosamente');
                this.addImageToGallery(result);
                this.resetUploadState();
            } else {
                this.showAlert('error', result.message || 'Error al procesar imagen');
            }

        } catch (error) {
            console.error('Error processing image:', error);
            this.showAlert('error', 'Error de conexión');
        } finally {
            this.setLoadingState(this.processBtn, false);
            this.isProcessing = false;
        }
    }

    resetUploadState() {
        console.log('Reiniciando estado de upload...');
        
        // Limpiar archivo
        this.imageUpload.value = '';
        this.uploadedImage = null;
        this.currentMode = 'webcam';

        // Restaurar video
        if (this.video) {
            this.video.style.display = 'block';
        }

        // Remover preview
        const preview = document.querySelector('.uploaded-preview');
        if (preview) {
            preview.remove();
            console.log('Preview removido');
        }

        // Restaurar botones
        if (this.captureBtn) {
            this.captureBtn.style.display = 'inline-block';
            this.captureBtn.disabled = !this.selectedSticker;
        }
        if (this.processBtn) {
            this.processBtn.style.display = 'none';
            this.processBtn.disabled = true;
        }

        console.log('Estado de upload reiniciado correctamente');
    }

    async sendCaptureToServer(imageData) {
        console.log('=== CAPTURE DEBUG START ===');
        console.log('Enviando imagen al servidor, stickers:', this.positionedStickers.length);
        
        const formData = new FormData();
        formData.append('image_data', imageData);
        
        // Enviar datos de stickers
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

        const result = await response.json();
        console.log('Server response:', result);
        console.log('=== CAPTURE DEBUG END ===');

        if (result.success) {
            this.showAlert('success', 'Foto capturada y guardada exitosamente');
            this.addImageToGallery(result);
        } else {
            this.showAlert('error', result.message || 'Error al capturar foto');
        }
    }

    addImageToGallery(imageData) {
        const gallery = document.getElementById('user-gallery');
        if (!gallery) return;

        // Eliminar mensaje vacío si existe
        const emptyMessage = gallery.querySelector('.text-center.text-muted');
        if (emptyMessage) {
            emptyMessage.remove();
        }

        // Crear HTML para nueva imagen
        const imageHtml = `
            <div class="user-image-card" data-image-id="${imageData.image_id}">
                <img src="${imageData.image_url}" alt="Mi imagen" loading="lazy">
                <div class="user-image-overlay">
                    <button class="btn btn-danger btn-sm delete-image-btn" 
                            data-image-id="${imageData.image_id}"
                            title="Eliminar imagen">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;

        gallery.insertAdjacentHTML('afterbegin', imageHtml);
        this.updateGalleryCount();

        console.log('✅ Nueva imagen agregada a la galería:', imageData);
    }

    updateGalleryCount() {
        const gallery = document.getElementById('user-gallery');
        if (!gallery) return;
        
        const images = gallery.querySelectorAll('.user-image-card');
        // Buscar el contador específicamente dentro de la tarjeta de la galería
        const cardBody = gallery.closest('.card-body');
        if (cardBody) {
            const countElement = cardBody.querySelector('.card-title small');
            if (countElement) {
                countElement.textContent = `(${images.length})`;
                console.log('Contador de galería actualizado:', images.length);
            }
        }
    }

    async handleDeleteImage(button) {
        if (this.isDeleting) return;
        
        const imageId = button.dataset.imageId;
        if (!imageId || !confirm('¿Estás seguro de que quieres eliminar esta imagen?')) {
            return;
        }
        
        this.isDeleting = true;
        const originalText = button.innerHTML;
        
        try {
            button.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i>';
            button.disabled = true;
            
            // Usar URLSearchParams para enviar como form-data que PHP pueda leer en $_POST
            const params = new URLSearchParams();
            params.append('csrf_token', this.csrfToken);

            const response = await fetch(`/editor/deleteImage/${imageId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Remover elemento del DOM
                const imageCard = button.closest('.user-image-card');
                if (imageCard) {
                    imageCard.remove();
                    this.updateGalleryCount();
                }
                this.showAlert('success', 'Imagen eliminada exitosamente');
            } else {
                this.showAlert('error', result.message || 'Error al eliminar imagen');
                button.innerHTML = originalText;
                button.disabled = false;
            }
            
        } catch (error) {
            console.error('Error deleting image:', error);
            this.showAlert('error', 'Error de conexión');
            button.innerHTML = originalText;
            button.disabled = false;
        } finally {
            this.isDeleting = false;
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
        if (window.camagru && window.camagru.showAlert) {
            window.camagru.showAlert(type, message);
        } else {
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