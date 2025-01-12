<?php
/**
 * Core PrestaShop module - Cornelius
 * @author    tivuno.com <hi@tivuno.com>
 * @copyright 2018 - 2025 © tivuno.com
 * @license   https://tivuno.com/blog/nea-tis-epicheirisis/apli-adeia
 */
// PrestaShop validator - Start
if (!defined('_PS_VERSION_')) {
    exit;
}
// PrestaShop validator - Finish
class TvcorePhoto
{
    public static function getPhoto(string $link)
    {
        return $link;
    }

    /**
     * It creates an image for the specified product, given a link
     * In case it’s the first image, we can set it as cover via the 3rd parameter
     * @param int $id_product
     * @param string $image_source
     * @param bool $is_cover
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function setProductPhoto(int $id_product, string $image_source, bool $is_cover = false)
    {
        if (self::doesExist($image_source)) {
            $valid_filename = self::getRemoteImage($image_source);
            $image = new Image();
            $image->id_product = $id_product;
            $image->position = Image::getHighestPosition($id_product) + 1;
            if ($is_cover === true) {
                $image->cover = 1;
                self::deleteCover($id_product);
            }
            $image->add();
            if (self::createImageFile($valid_filename, $image->getPathForCreation(), $image->id, $id_product, 1)) {
                return $image->id;
            }
        }

        return false;
    }

    public static function doesExist($link): bool
    {
        $file_headers = @get_headers($link);
        if ($file_headers and $file_headers[0] == 'HTTP/1.1 200 OK') {
            return true;
        }

        return false;
    }

    public static function getRemoteImage(string $image_link)
    {
        $file_name = self::getFilenameFromLink($image_link);
        if (!$file_name) {
            return false;
        }
        $fp = fopen(_PS_TMP_IMG_DIR_ . $file_name, 'w');
        $ch = curl_init();
        $ports = [];
        if (preg_match('/:(\d+)/', $image_link, $ports)) {
            $image_link = preg_replace('/:\d+/', '', $image_link);
            curl_setopt($ch, CURLOPT_PORT, (int) $ports[1]);
        }
        curl_setopt($ch, CURLOPT_URL, $image_link);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        if (ini_get('open_basedir') == '') {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        }
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        if (self::isDownloadable($file_name, $http_code)) {
            return _PS_TMP_IMG_DIR_ . $file_name;
        } else {
            unlink(_PS_TMP_IMG_DIR_ . $file_name);
        }

        return false;
    }

    private static function getFilenameFromLink($image_link)
    {
        if (str_contains($image_link, '?')) {
            $file_name = md5($image_link) . '.jpg';
        } else {
            $url_parts = explode('/', $image_link);
            $file_name = str_replace('%20', '_', end($url_parts));
        }

        return $file_name;
    }

    public static function isDownloadable($file_name, $http_code)
    {
        if ($http_code == 404) {
            return false;
        }
        $file_info = '';
        if (filesize(_PS_TMP_IMG_DIR_ . $file_name) > 0) {
            $file_info = getimagesize(_PS_TMP_IMG_DIR_ . $file_name);
        }
        if (empty($file_info)) {
            return false;
        }
        if (isset($file_info['mime']) && strpos($file_info['mime'], 'image/') !== 0) {
            return false;
        }

        return true;
    }

    public static function deleteCover($id_product)
    {
        Db::getInstance()->update(
            'image',
            ['cover' => null],
            '`id_product`=' . (int) $id_product
        );
    }

    public static function createImageFile($file, $path, $id_image, $id_product, $thumbnail = true)
    {
        [$tmp_width, $tmp_height, $type] = getimagesize($file);

        $MAX_IMAGE_WIDTH = 3000;
        $MAX_IMAGE_HEIGHT = 3000;
        if ($tmp_width > $MAX_IMAGE_WIDTH) {
            $tmp_width = $MAX_IMAGE_WIDTH;
            // $this->logger->logInfo('Image: ' . $file . ", has been resized because it's too wide.");
        }
        if ($tmp_height > $MAX_IMAGE_HEIGHT) {
            $tmp_height = $MAX_IMAGE_HEIGHT;
            // $this->logger->logInfo('Image: ' . $file . ", has been resized because it's too tall.");
        }

        $image_created = ImageManager::resize($file, $path . '.jpg', $tmp_width, $tmp_height);
        $images_types = ImageType::getImagesTypes('products');
        $watermark_types = explode(',', Configuration::get('WATERMARK_TYPES'));

        if ($thumbnail) {
            foreach ($images_types as $image_type) {
                ImageManager::resize(
                    $file,
                    $path . '-' . Tools::stripslashes($image_type['name']) . '.jpg',
                    $image_type['width'],
                    $image_type['height']
                );

                if (in_array($image_type['id_image_type'], $watermark_types)) {
                    Hook::exec('actionWatermark', ['id_image' => $id_image, 'id_product' => $id_product]);
                }
            }
        }

        return $image_created;
    }
}
