<?php

class Image extends Model {
    protected $table = 'images';

    public function getUserImages($userId, $limit = null, $offset = 0) {
        $sql = "SELECT i.*, u.username 
                FROM images i 
                JOIN users u ON i.user_id = u.id 
                WHERE i.user_id = ? 
                ORDER BY i.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        return $this->db->fetchAll($sql, [$userId]);
    }

    public function getAllImages($limit = null, $offset = 0) {
        if ($limit === null) {
            $limit = Config::getImagesPerPage();
        }
        $sql = "SELECT i.*, u.username,
                       (SELECT COUNT(*) FROM likes l WHERE l.image_id = i.id) as likes_count,
                       (SELECT COUNT(*) FROM comments c WHERE c.image_id = i.id) as comments_count
                FROM images i 
                JOIN users u ON i.user_id = u.id 
                ORDER BY i.created_at DESC 
                LIMIT {$limit} OFFSET {$offset}";
        
        return $this->db->fetchAll($sql);
    }

    public function getImageWithDetails($imageId) {
        $sql = "SELECT i.*, u.username,
                       (SELECT COUNT(*) FROM likes l WHERE l.image_id = i.id) as likes_count,
                       (SELECT COUNT(*) FROM comments c WHERE c.image_id = i.id) as comments_count
                FROM images i 
                JOIN users u ON i.user_id = u.id 
                WHERE i.id = ?";
        
        return $this->db->fetch($sql, [$imageId]);
    }

    public function uploadImage($userId, $imageData, $originalFilename = null) {
        error_log("UPLOAD DEBUG: uploadImage called for user $userId, data size: " . strlen($imageData));
        
        // Generar nombre único para el archivo
        $filename = uniqid() . '_' . time() . '.png';
        $filepath = Config::getUploadPath() . $filename;
        
        error_log("UPLOAD DEBUG: Generated filename: $filename");

        // Asegurar que el directorio existe
        if (!file_exists(Config::getUploadPath())) {
            mkdir(Config::getUploadPath(), 0777, true);
        }

        // Guardar imagen en el servidor
        if (file_put_contents($filepath, $imageData)) {
            // Guardar en la base de datos
            $data = [
                'user_id' => $userId,
                'filename' => $filename,
                'original_filename' => $originalFilename
            ];

            $imageId = $this->create($data);
            if ($imageId) {
                return ['success' => true, 'image_id' => $imageId, 'filename' => $filename];
            } else {
                // Si falla la BD, eliminar archivo
                unlink($filepath);
            }
        }

        return ['success' => false, 'message' => 'Error al subir la imagen'];
    }

    public function deleteImage($imageId, $userId) {
        error_log("DELETE DEBUG: deleteImage called for imageId $imageId, userId $userId");
        
        // Verificar que la imagen pertenece al usuario
        $image = $this->find($imageId);
        if (!$image) {
            error_log("DELETE DEBUG: Image not found");
            return ['success' => false, 'message' => 'Imagen no encontrada'];
        }
        
        if ($image['user_id'] != $userId) {
            error_log("DELETE DEBUG: User does not own this image. Owner: {$image['user_id']}");
            return ['success' => false, 'message' => 'No tienes permisos para eliminar esta imagen'];
        }

        // Eliminar archivo físico
        $filepath = Config::getUploadPath() . $image['filename'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        // Eliminar de la base de datos (esto también eliminará likes y comentarios por CASCADE)
        if ($this->delete($imageId)) {
            error_log("DELETE DEBUG: Image deleted successfully from database");
            return ['success' => true, 'message' => 'Imagen eliminada exitosamente'];
        }

        error_log("DELETE DEBUG: Failed to delete from database");
        return ['success' => false, 'message' => 'Error al eliminar la imagen'];
    }

    public function processImageWithMultipleStickers($imageData, $stickers) {
        try {
            // Crear imagen desde los datos
            $image = imagecreatefromstring($imageData);
            if (!$image) {
                throw new Exception("No se pudo crear la imagen");
            }

            // Redimensionar imagen base a las dimensiones configuradas
            $resizedImage = imagecreatetruecolor(Config::getImageWidth(), Config::getImageHeight());
            imagecopyresampled(
                $resizedImage, $image, 
                0, 0, 0, 0,
                Config::getImageWidth(), Config::getImageHeight(),
                imagesx($image), imagesy($image)
            );

            // Aplicar cada sticker
            foreach ($stickers as $stickerData) {
                $stickerPath = Config::getStickersPath() . $stickerData['filename'];
                $sticker = $this->loadSticker($stickerPath);
                
                if ($sticker) {
                    // Redimensionar sticker si es necesario
                    $stickerWidth = $stickerData['width'];
                    $stickerHeight = $stickerData['height'];
                    
                    if ($stickerWidth != imagesx($sticker) || $stickerHeight != imagesy($sticker)) {
                        $resizedSticker = imagecreatetruecolor($stickerWidth, $stickerHeight);
                        
                        // Preservar transparencia
                        imagealphablending($resizedSticker, false);
                        imagesavealpha($resizedSticker, true);
                        
                        imagecopyresampled(
                            $resizedSticker, $sticker,
                            0, 0, 0, 0,
                            $stickerWidth, $stickerHeight,
                            imagesx($sticker), imagesy($sticker)
                        );
                        
                        imagedestroy($sticker);
                        $sticker = $resizedSticker;
                    }
                    
                    // Aplicar sticker en la posición especificada
                    imagecopy(
                        $resizedImage, $sticker,
                        $stickerData['x'], $stickerData['y'],
                        0, 0,
                        imagesx($sticker), imagesy($sticker)
                    );
                    
                    imagedestroy($sticker);
                } else {
                    error_log("Could not load sticker: " . $stickerPath);
                }
            }

            // Convertir a PNG y obtener los datos
            ob_start();
            imagepng($resizedImage);
            $finalImageData = ob_get_contents();
            ob_end_clean();

            // Limpiar memoria
            imagedestroy($image);
            imagedestroy($resizedImage);

            return $finalImageData;
        } catch (Exception $e) {
            error_log("Error processing image with multiple stickers: " . $e->getMessage());
            return false;
        }
    }

    public function processImageWithSticker($imageData, $stickerPath, $stickerX = 0, $stickerY = 0) {
        try {
            // Crear imagen desde los datos base64
            $image = imagecreatefromstring($imageData);
            if (!$image) {
                throw new Exception("No se pudo crear la imagen");
            }

            // Redimensionar imagen base a las dimensiones configuradas
            $resizedImage = imagecreatetruecolor(Config::getImageWidth(), Config::getImageHeight());
            imagecopyresampled(
                $resizedImage, $image, 
                0, 0, 0, 0,
                Config::getImageWidth(), Config::getImageHeight(),
                imagesx($image), imagesy($image)
            );

            // Cargar sticker
            $sticker = $this->loadSticker($stickerPath);
            if (!$sticker) {
                throw new Exception("No se pudo cargar el sticker");
            }

            // Aplicar sticker a la imagen
            imagecopy(
                $resizedImage, $sticker,
                $stickerX, $stickerY,
                0, 0,
                imagesx($sticker), imagesy($sticker)
            );

            // Convertir a PNG y obtener los datos
            ob_start();
            imagepng($resizedImage);
            $finalImageData = ob_get_contents();
            ob_end_clean();

            // Limpiar memoria
            imagedestroy($image);
            imagedestroy($resizedImage);
            imagedestroy($sticker);

            return $finalImageData;
        } catch (Exception $e) {
            error_log("Error processing image: " . $e->getMessage());
            return false;
        }
    }

    private function loadSticker($stickerPath) {
        if (!file_exists($stickerPath)) {
            return false;
        }

        $imageInfo = getimagesize($stickerPath);
        if (!$imageInfo) {
            return false;
        }

        switch ($imageInfo['mime']) {
            case 'image/png':
                return imagecreatefrompng($stickerPath);
            case 'image/jpeg':
                return imagecreatefromjpeg($stickerPath);
            case 'image/gif':
                return imagecreatefromgif($stickerPath);
            default:
                return false;
        }
    }

    public function getTotalCount() {
        return $this->count();
    }

    public function getUserImagesCount($userId) {
        return $this->count("user_id = {$userId}");
    }
}
