<?php
namespace Src\Helpers;

class ValidationHelper
{
    /**
     * Validate required fields (GET or POST)
     * Ensures every required field exists and is not empty.
     */
    public static function validate(array $required, array $arrived): bool
    {
        foreach ($required as $field) {

            // field missing
            if (!array_key_exists($field, $arrived)) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => "Missing required field: $field"
                ]);
                return false;
            }

            // field exists but empty value
            if ($arrived[$field] === '' || $arrived[$field] === null) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => "Empty value for field: $field"
                ]);
                return false;
            }
        }

        return true; // everything valid
    }
}
