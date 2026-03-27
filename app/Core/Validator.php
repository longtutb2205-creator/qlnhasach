<?php

namespace App\Core;

/**
 * Validator — Kiểm tra dữ liệu đầu vào
 *
 * Cách dùng:
 *   $v = new Validator($_POST);
 *   $v->required('ten')->minLength('ten', 2)->email('email');
 *   if ($v->fails()) { $errors = $v->errors(); }
 */
class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    // ─── Rules ────────────────────────────────────────────────

    /**
     * Bắt buộc nhập
     */
    public function required(string $field, string $label = ''): static
    {
        $label = $label ?: $field;
        $value = trim($this->data[$field] ?? '');
        if ($value === '') {
            $this->errors[$field] = "{$label} không được để trống.";
        }
        return $this;
    }

    /**
     * Độ dài tối thiểu
     */
    public function minLength(string $field, int $min, string $label = ''): static
    {
        $label = $label ?: $field;
        $value = $this->data[$field] ?? '';
        if (isset($this->errors[$field])) return $this;
        if (mb_strlen($value) < $min) {
            $this->errors[$field] = "{$label} phải có ít nhất {$min} ký tự.";
        }
        return $this;
    }

    /**
     * Độ dài tối đa
     */
    public function maxLength(string $field, int $max, string $label = ''): static
    {
        $label = $label ?: $field;
        $value = $this->data[$field] ?? '';
        if (isset($this->errors[$field])) return $this;
        if (mb_strlen($value) > $max) {
            $this->errors[$field] = "{$label} không được vượt quá {$max} ký tự.";
        }
        return $this;
    }

    /**
     * Định dạng email
     */
    public function email(string $field, string $label = ''): static
    {
        $label = $label ?: $field;
        $value = $this->data[$field] ?? '';
        if (isset($this->errors[$field])) return $this;
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "{$label} không đúng định dạng email.";
        }
        return $this;
    }

    /**
     * Chỉ chứa số
     */
    public function numeric(string $field, string $label = ''): static
    {
        $label = $label ?: $field;
        $value = $this->data[$field] ?? '';
        if (isset($this->errors[$field])) return $this;
        if ($value !== '' && !is_numeric($value)) {
            $this->errors[$field] = "{$label} phải là số.";
        }
        return $this;
    }

    /**
     * Giá trị tối thiểu (số)
     */
    public function min(string $field, float $min, string $label = ''): static
    {
        $label = $label ?: $field;
        $value = $this->data[$field] ?? '';
        if (isset($this->errors[$field])) return $this;
        if ($value !== '' && (float) $value < $min) {
            $this->errors[$field] = "{$label} phải lớn hơn hoặc bằng {$min}.";
        }
        return $this;
    }

    /**
     * Số điện thoại Việt Nam (10 số, bắt đầu 0)
     */
    public function phone(string $field, string $label = ''): static
    {
        $label = $label ?: $field;
        $value = $this->data[$field] ?? '';
        if (isset($this->errors[$field])) return $this;
        if ($value !== '' && !preg_match('/^0[0-9]{9}$/', $value)) {
            $this->errors[$field] = "{$label} không đúng định dạng số điện thoại.";
        }
        return $this;
    }

    /**
     * Giá trị phải nằm trong danh sách cho phép
     */
    public function in(string $field, array $allowed, string $label = ''): static
    {
        $label = $label ?: $field;
        $value = $this->data[$field] ?? '';
        if (isset($this->errors[$field])) return $this;
        if ($value !== '' && !in_array($value, $allowed, true)) {
            $this->errors[$field] = "{$label} có giá trị không hợp lệ.";
        }
        return $this;
    }

    /**
     * Xác nhận trùng khớp (vd: password_confirmation)
     */
    public function confirmed(string $field, string $confirmField = '', string $label = ''): static
    {
        $label       = $label ?: $field;
        $confirmField = $confirmField ?: $field . '_confirmation';
        if (isset($this->errors[$field])) return $this;
        if (($this->data[$field] ?? '') !== ($this->data[$confirmField] ?? '')) {
            $this->errors[$field] = "{$label} xác nhận không khớp.";
        }
        return $this;
    }

    // ─── Kết quả ──────────────────────────────────────────────

    /**
     * Có lỗi không?
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Lấy tất cả lỗi
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Lấy lỗi đầu tiên của một field
     */
    public function error(string $field): string
    {
        return $this->errors[$field] ?? '';
    }

    /**
     * Lấy dữ liệu đã được trim
     */
    public function validated(): array
    {
        return array_map(fn($v) => is_string($v) ? trim($v) : $v, $this->data);
    }
}