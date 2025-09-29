// Aplicación principal de Camagru
class CamagruApp {
    constructor() {
        // Intentar obtener token CSRF de varias fuentes
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                        document.querySelector('input[name="csrf_token"]')?.value ||
                        document.getElementById('csrf-token')?.value;
        
        console.log('CSRF Token obtenido:', this.csrfToken ? 'Sí' : 'No');
        this.init();
    }

    init() {
        this.initEventListeners();
        this.initTooltips();
        this.initGalleryFeatures();
        this.initEditorFeatures();
    }

    initEventListeners() {
        // Debug: Verificar IDs duplicados al cargar la página
        this.debugDuplicateIds();
        
        // Manejar likes
        document.addEventListener('click', (e) => {
            if (e.target.closest('.like-btn')) {
                this.handleLike(e.target.closest('.like-btn'));
            }
            
            if (e.target.closest('.comment-toggle-btn')) {
                this.toggleComments(e.target.closest('.comment-toggle-btn'));
            }
            
            if (e.target.closest('.delete-image-btn')) {
                this.handleDeleteImage(e.target.closest('.delete-image-btn'));
            }
            
            if (e.target.closest('.delete-comment-btn')) {
                this.handleDeleteComment(e.target.closest('.delete-comment-btn'));
            }
        });

        // Manejar envío de comentarios
        document.addEventListener('submit', (e) => {
            if (e.target.classList.contains('comment-form')) {
                e.preventDefault();
                this.handleCommentSubmit(e.target);
            }
        });

        // Auto-hide alerts
        this.autoHideAlerts();
    }

    initTooltips() {
        // Inicializar tooltips de Bootstrap
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    initGalleryFeatures() {
        // Cargar comentarios para imágenes visibles
        const imageCards = document.querySelectorAll('.image-card');
        imageCards.forEach(card => {
            const imageId = card.querySelector('.like-btn')?.dataset.imageId;
            if (imageId) {
                this.loadComments(imageId);
            }
        });
    }

    initEditorFeatures() {
        if (document.getElementById('webcam-canvas')) {
            this.initWebcam();
        }
    }

    // Funciones de likes
    async handleLike(button) {
        const imageId = button.dataset.imageId;
        const isLiked = button.classList.contains('btn-danger');
        
        console.log('Procesando like...', { imageId, isLiked, csrfToken: this.csrfToken });
        
        if (!this.csrfToken) {
            this.showAlert('error', 'Token de seguridad no disponible');
            return;
        }
        
        button.disabled = true;
        
        try {
            const response = await fetch(`/gallery/like/${imageId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `csrf_token=${encodeURIComponent(this.csrfToken)}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Actualizar UI
                const heartIcon = button.querySelector('i');
                const likesCount = button.querySelector('.likes-count');
                
                if (data.action === 'liked') {
                    button.classList.remove('btn-outline-danger');
                    button.classList.add('btn-danger');
                    heartIcon.classList.add('bi-heart-fill');
                    heartIcon.classList.remove('bi-heart');
                } else {
                    button.classList.remove('btn-danger');
                    button.classList.add('btn-outline-danger');
                    heartIcon.classList.remove('bi-heart-fill');
                    heartIcon.classList.add('bi-heart');
                }
                
                likesCount.textContent = data.likes_count;
                
                // Animación
                button.classList.add('animate__animated', 'animate__pulse');
                setTimeout(() => {
                    button.classList.remove('animate__animated', 'animate__pulse');
                }, 1000);
            } else {
                this.showAlert('error', data.message || 'Error al procesar like');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('error', 'Error de conexión');
        } finally {
            button.disabled = false;
        }
    }

    // Funciones de comentarios
    toggleComments(button) {
        const imageId = button.dataset.imageId;
        const commentsSection = document.getElementById(`comments-${imageId}`);
        
        if (!commentsSection) {
            console.error(`Comments section not found for image ${imageId}`);
            return;
        }
        
        // Verificar si esta sección ya está visible
        const isCurrentlyVisible = commentsSection.style.display === 'block';
        
        // Cerrar TODAS las secciones y desactivar TODOS los botones
        document.querySelectorAll('.comments-section').forEach(section => {
            section.style.display = 'none';
        });
        document.querySelectorAll('.comment-toggle-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Si no estaba visible, abrirla
        if (!isCurrentlyVisible) {
            commentsSection.style.display = 'block';
            button.classList.add('active');
            this.loadComments(imageId);
        }
    }

    async loadComments(imageId) {
        const commentsList = document.getElementById(`comments-list-${imageId}`);
        if (!commentsList) return;

        try {
            const response = await fetch(`/gallery/getComments/${imageId}`);
            const data = await response.json();
            
            if (data.success) {
                this.renderComments(commentsList, data.comments, imageId);
            }
        } catch (error) {
            console.error('Error loading comments:', error);
        }
    }

    renderComments(container, comments, imageId) {
        container.innerHTML = '';
        
        comments.forEach(comment => {
            const commentEl = document.createElement('div');
            commentEl.className = 'comment-item fade-in';
            commentEl.innerHTML = `
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="comment-author">${this.escapeHtml(comment.username)}</div>
                        <div class="comment-text">${this.escapeHtml(comment.comment)}</div>
                        <div class="comment-date">${this.formatDate(comment.created_at)}</div>
                    </div>
                    ${this.canDeleteComment(comment) ? `
                        <button class="comment-delete delete-comment-btn" 
                                data-comment-id="${comment.id}" 
                                data-image-id="${imageId}">
                            <i class="bi bi-trash"></i>
                        </button>
                    ` : ''}
                </div>
            `;
            container.appendChild(commentEl);
        });
    }

    canDeleteComment(comment) {
        const currentUserId = window.currentUserId; // Debe ser establecido en el layout
        return currentUserId && (currentUserId == comment.user_id || currentUserId == comment.image_owner);
    }

    async handleCommentSubmit(form) {
        const imageId = form.dataset.imageId;
        const commentInput = form.querySelector('.comment-input');
        const comment = commentInput.value.trim();
        
        if (!comment) return;
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalContent = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i>';
        
        try {
            const formData = new FormData();
            formData.append('comment', comment);
            formData.append('csrf_token', this.csrfToken);
            
            const response = await fetch(`/gallery/comment/${imageId}`, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                commentInput.value = '';
                
                // Actualizar contador de comentarios
                const commentBtn = form.closest('.card').querySelector('.comment-toggle-btn .comments-count');
                if (commentBtn) {
                    commentBtn.textContent = data.comments_count;
                }
                
                // Recargar comentarios
                this.loadComments(imageId);
                
                this.showAlert('success', 'Comentario agregado');
            } else {
                this.showAlert('error', data.message || 'Error al agregar comentario');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('error', 'Error de conexión');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalContent;
        }
    }

    async handleDeleteComment(button) {
        if (!confirm('¿Estás seguro de que quieres eliminar este comentario?')) return;
        
        const commentId = button.dataset.commentId;
        const imageId = button.dataset.imageId;
        
        try {
            const response = await fetch(`/gallery/deleteComment/${commentId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `csrf_token=${this.csrfToken}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Recargar comentarios
                this.loadComments(imageId);
                
                // Actualizar contador
                const commentBtn = document.querySelector(`[data-image-id="${imageId}"].comment-toggle-btn .comments-count`);
                if (commentBtn) {
                    commentBtn.textContent = data.comments_count;
                }
                
                this.showAlert('success', 'Comentario eliminado');
            } else {
                this.showAlert('error', data.message || 'Error al eliminar comentario');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('error', 'Error de conexión');
        }
    }

    // Funciones de imágenes
    async handleDeleteImage(button) {
        if (!confirm('¿Estás seguro de que quieres eliminar esta imagen? Esta acción no se puede deshacer.')) return;
        
        const imageId = button.dataset.imageId;
        
        try {
            const response = await fetch(`/editor/deleteImage/${imageId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `csrf_token=${this.csrfToken}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Eliminar elemento del DOM con reorganización mejorada
                const imageElement = button.closest('.user-image-col') || button.closest('.col-xl-3') || button.closest('.col-lg-6');
                if (imageElement) {
                    // Animación de salida
                    imageElement.style.transition = 'all 0.3s ease';
                    imageElement.style.opacity = '0';
                    imageElement.style.transform = 'scale(0.8)';
                    
                    setTimeout(() => {
                        imageElement.remove();
                        this.reorganizeImageGrid();
                    }, 300);
                }
                
                this.showAlert('success', 'Imagen eliminada exitosamente');
            } else {
                this.showAlert('error', data.message || 'Error al eliminar imagen');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('error', 'Error de conexión');
        }
    }

    // Funciones de webcam (para el editor)
    initWebcam() {
        const video = document.getElementById('webcam-video');
        const canvas = document.getElementById('webcam-canvas');
        const captureBtn = document.getElementById('capture-btn');
        const stickerOptions = document.querySelectorAll('.sticker-option');
        
        let selectedSticker = null;
        let stream = null;
        
        // Solicitar acceso a la webcam
        navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } })
            .then(mediaStream => {
                stream = mediaStream;
                video.srcObject = stream;
                video.play();
            })
            .catch(err => {
                console.error('Error accessing webcam:', err);
                this.showAlert('error', 'No se pudo acceder a la webcam. Puedes subir una imagen en su lugar.');
                document.getElementById('upload-section').style.display = 'block';
            });
        
        // Manejar selección de stickers
        stickerOptions.forEach(option => {
            option.addEventListener('click', () => {
                stickerOptions.forEach(opt => opt.classList.remove('selected'));
                option.classList.add('selected');
                selectedSticker = option.dataset.sticker;
                captureBtn.disabled = false;
            });
        });
        
        // Manejar captura
        captureBtn.addEventListener('click', () => {
            if (!selectedSticker) {
                this.showAlert('error', 'Selecciona un sticker primero');
                return;
            }
            
            this.capturePhoto(video, canvas, selectedSticker);
        });
        
        // Limpiar stream al salir
        window.addEventListener('beforeunload', () => {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        });
    }

    async capturePhoto(video, canvas, stickerName) {
        const ctx = canvas.getContext('2d');
        
        // Configurar canvas
        canvas.width = 640;
        canvas.height = 480;
        
        // Dibujar video frame
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Convertir a base64
        const imageData = canvas.toDataURL('image/png');
        
        // Enviar al servidor
        try {
            const response = await fetch('/editor/capture', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `image_data=${encodeURIComponent(imageData)}&sticker=${stickerName}&csrf_token=${this.csrfToken}&sticker_x=100&sticker_y=100`
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showAlert('success', 'Foto capturada y guardada exitosamente');
                // Actualizar galería de usuario
                this.refreshUserGallery();
            } else {
                this.showAlert('error', data.message || 'Error al capturar foto');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('error', 'Error de conexión');
        }
    }

    refreshUserGallery() {
        const galleryContainer = document.getElementById('user-gallery');
        if (galleryContainer) {
            location.reload(); // Simple refresh por ahora
        }
    }

    // Utilidades
    showAlert(type, message) {
        const alertContainer = document.getElementById('alert-container') || this.createAlertContainer();
        
        const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-info';
        const iconClass = type === 'success' ? 'bi-check-circle' : type === 'error' ? 'bi-exclamation-triangle' : 'bi-info-circle';
        
        const alert = document.createElement('div');
        alert.className = `alert ${alertClass} alert-dismissible fade show`;
        alert.innerHTML = `
            <i class="bi ${iconClass} me-2"></i>
            ${this.escapeHtml(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        alertContainer.appendChild(alert);
        
        // Auto-hide después de 5 segundos
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }

    createAlertContainer() {
        const container = document.createElement('div');
        container.id = 'alert-container';
        container.style.position = 'fixed';
        container.style.top = '80px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        container.style.maxWidth = '400px';
        document.body.appendChild(container);
        return container;
    }

    autoHideAlerts() {
        const alerts = document.querySelectorAll('.alert:not(.alert-dismissible)');
        alerts.forEach(alert => {
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 5000);
        });
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    debugDuplicateIds() {
        console.log('=== DEBUG: Verificando IDs de comentarios ===');
        
        // Verificar botones de comentarios
        const commentButtons = document.querySelectorAll('.comment-toggle-btn');
        console.log(`Found ${commentButtons.length} comment buttons:`);
        commentButtons.forEach(btn => {
            console.log(`Button for image: ${btn.dataset.imageId}`);
        });
        
        // Verificar secciones de comentarios
        const commentSections = document.querySelectorAll('.comments-section');
        console.log(`Found ${commentSections.length} comment sections:`);
        commentSections.forEach(section => {
            console.log(`Section ID: ${section.id}`);
        });
        
        // Verificar si hay IDs duplicados
        const ids = Array.from(commentSections).map(s => s.id);
        const uniqueIds = [...new Set(ids)];
        if (ids.length !== uniqueIds.length) {
            console.error('¡DUPLICATED IDs FOUND!', ids);
        } else {
            console.log('No duplicate IDs found ✓');
        }
        
        console.log('=== END DEBUG ===');
    }
    
    reorganizeImageGrid() {
        const userGrid = document.getElementById('user-images-grid');
        const mainGrid = document.getElementById('images-grid');
        
        if (userGrid) {
            // Para la página "Mis Imágenes"
            const columns = userGrid.querySelectorAll('.user-image-col');
            const totalImages = columns.length;
            
            // Actualizar contador si existe
            const counter = document.querySelector('h2 small');
            if (counter) {
                counter.textContent = `(${totalImages} fotos)`;
            }
            
            // Forzar recalculo del layout de Bootstrap
            userGrid.style.display = 'none';
            setTimeout(() => {
                userGrid.style.display = '';
            }, 10);
            
        } else if (mainGrid) {
            // Para la galería principal
            const columns = mainGrid.querySelectorAll('.col-lg-6');
            const totalImages = columns.length;
            
            // Actualizar contador si existe
            const counter = document.querySelector('.text-muted small');
            if (counter && counter.textContent.includes('imágenes')) {
                counter.textContent = `${totalImages} imágenes en esta página`;
            }
        }
    }
}

// Inicializar aplicación cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    // Establecer usuario actual para funciones de comentarios
    const userIdMeta = document.querySelector('meta[name="user-id"]');
    if (userIdMeta) {
        window.currentUserId = userIdMeta.content;
    }
    
    // Inicializar aplicación
    window.camagru = new CamagruApp();
});

// Funciones globales adicionales
window.confirmDelete = function(message) {
    return confirm(message || '¿Estás seguro de que quieres eliminar este elemento?');
};

// Añadir clase CSS para animación de spinning
const style = document.createElement('style');
style.textContent = `
    .spin {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);
