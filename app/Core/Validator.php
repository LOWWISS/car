<?php
/**
 * Validator: server-side input validation.
 *
 * SECURITY (Input Validation & Sanitization): every form is validated server-side
 * (type, length, format). Client-side JS validation is for UX only and is never
 * trusted. Returns an array of error messages keyed by field.
 */
final class Validator
{
    private array $data;
    private array $rules;
    public array $errors = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    public function validate(): bool
    {
        foreach ($this->rules as $field => $ruleSet) {
            $value = $this->data[$field] ?? null;
            $rules = explode('|', $ruleSet);
            foreach ($rules as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }
                $this->apply($field, $value, $rule, $params);
            }
        }
        return empty($this->errors);
    }

    private function apply(string $field, mixed $value, string $rule, array $params): void
    {
        if (isset($this->errors[$field])) return;

        switch ($rule) {
            case 'required':
                if ($value === null || $value === '' ) {
                    $this->errors[$field] = ucfirst($field) . ' is required.';
                }
                break;
            case 'string':
                if ($value !== null && !is_string($value)) {
                    $this->errors[$field] = ucfirst($field) . ' must be text.';
                }
                break;
            case 'integer':
                if ($value !== null && $value !== '' && !ctype_digit((string)$value)) {
                    $this->errors[$field] = ucfirst($field) . ' must be a whole number.';
                }
                break;
            case 'numeric':
                if ($value !== null && $value !== '' && !is_numeric($value)) {
                    $this->errors[$field] = ucfirst($field) . ' must be a number.';
                }
                break;
            case 'email':
                if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field] = 'Invalid email address.';
                }
                break;
            case 'min':
                $len = is_array($value) ? count($value) : strlen((string)$value);
                if ($len < (int)$params[0]) {
                    $this->errors[$field] = ucfirst($field) . " must be at least {$params[0]} characters.";
                }
                break;
            case 'max':
                $len = is_array($value) ? count($value) : strlen((string)$value);
                if ($len > (int)$params[0]) {
                    $this->errors[$field] = ucfirst($field) . " must be at most {$params[0]} characters.";
                }
                break;
            case 'in':
                if ($value !== null && $value !== '' && !in_array($value, $params, true)) {
                    $this->errors[$field] = ucfirst($field) . ' has an invalid value.';
                }
                break;
            case 'date':
                if ($value !== null && $value !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}(:\d{2})?)?$/', (string)$value)) {
                    $this->errors[$field] = ucfirst($field) . ' must be a valid date.';
                }
                break;
            case 'datetime':
                if ($value !== null && $value !== '' && strtotime((string)$value) === false) {
                    $this->errors[$field] = ucfirst($field) . ' must be a valid date/time.';
                }
                break;
        }
    }

    /** Whitelist validation for dynamic column names (e.g. sort fields) —
     *  prevents SQL injection via column/table identifiers. */
    public static function whitelist(string $value, array $allowed): ?string
    {
        return in_array($value, $allowed, true) ? $value : null;
    }
}
