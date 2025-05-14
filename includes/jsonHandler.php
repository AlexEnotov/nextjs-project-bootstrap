<?php
// Prevent direct access to this file
if (!defined('INCLUDED')) {
    die('Direct access not permitted');
}

class JsonHandler {
    /**
     * Read data from a JSON file
     * @param string $filePath Path to the JSON file
     * @return array|null Returns decoded JSON data or null on error
     */
    public static function readData($filePath) {
        try {
            if (!file_exists($filePath)) {
                throw new Exception("File not found: $filePath");
            }

            $content = file_get_contents($filePath);
            if ($content === false) {
                throw new Exception("Unable to read file: $filePath");
            }

            $data = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("JSON decode error: " . json_last_error_msg());
            }

            return $data;
        } catch (Exception $e) {
            error_log("Error reading JSON file: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Write data to a JSON file with file locking
     * @param string $filePath Path to the JSON file
     * @param array $data Data to write
     * @return bool Returns true on success, false on failure
     */
    public static function writeData($filePath, $data) {
        try {
            $dir = dirname($filePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $fp = fopen($filePath, 'w');
            if (!$fp) {
                throw new Exception("Unable to open file for writing: $filePath");
            }

            // Acquire exclusive lock
            if (!flock($fp, LOCK_EX)) {
                fclose($fp);
                throw new Exception("Unable to lock file: $filePath");
            }

            // Convert data to JSON with pretty print
            $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("JSON encode error: " . json_last_error_msg());
            }

            // Write data
            if (fwrite($fp, $jsonData) === false) {
                throw new Exception("Unable to write to file: $filePath");
            }

            // Release lock and close file
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);

            return true;
        } catch (Exception $e) {
            error_log("Error writing JSON file: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update specific item in a JSON array
     * @param string $filePath Path to the JSON file
     * @param int $id ID of the item to update
     * @param array $newData New data for the item
     * @return bool Returns true on success, false on failure
     */
    public static function updateItem($filePath, $id, $newData) {
        try {
            $data = self::readData($filePath);
            if (!$data) {
                throw new Exception("Unable to read data for update");
            }

            $updated = false;
            foreach ($data as &$item) {
                if (isset($item['id']) && $item['id'] === $id) {
                    $item = array_merge($item, $newData);
                    $updated = true;
                    break;
                }
            }

            if (!$updated) {
                throw new Exception("Item with ID $id not found");
            }

            return self::writeData($filePath, $data);
        } catch (Exception $e) {
            error_log("Error updating item: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete item from JSON array
     * @param string $filePath Path to the JSON file
     * @param int $id ID of the item to delete
     * @return bool Returns true on success, false on failure
     */
    public static function deleteItem($filePath, $id) {
        try {
            $data = self::readData($filePath);
            if (!$data) {
                throw new Exception("Unable to read data for deletion");
            }

            $data = array_filter($data, function($item) use ($id) {
                return !isset($item['id']) || $item['id'] !== $id;
            });

            return self::writeData($filePath, array_values($data));
        } catch (Exception $e) {
            error_log("Error deleting item: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get next available ID for a JSON array
     * @param string $filePath Path to the JSON file
     * @return int Next available ID
     */
    public static function getNextId($filePath) {
        $data = self::readData($filePath);
        if (!$data) {
            return 1;
        }

        $maxId = 0;
        foreach ($data as $item) {
            if (isset($item['id']) && $item['id'] > $maxId) {
                $maxId = $item['id'];
            }
        }

        return $maxId + 1;
    }
}
