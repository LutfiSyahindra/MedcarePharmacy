<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApotekProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && (
            $user->hasAnyRole(['Admin', 'admin', 'Apoteker', 'apoteker'])
            || $user->can('MEDCARE.SETTINGS.PROFILE_APOTEK')
        );
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:150'],
            'slogan' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email:rfc', 'max:150'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:1000'],
            'village' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'pharmacist_name' => ['nullable', 'string', 'max:150'],
            'pharmacist_license_number' => ['nullable', 'string', 'max:100'],
            'pharmacy_license_number' => ['nullable', 'string', 'max:100'],
            'license_expired_at' => ['nullable', 'date'],
            'tax_id' => ['nullable', 'string', 'max:40'],
            'operational_hours' => ['required', 'array'],
            'operational_hours.*.enabled' => ['nullable', 'boolean'],
            'operational_hours.*.open' => ['nullable', 'date_format:H:i'],
            'operational_hours.*.close' => ['nullable', 'date_format:H:i'],
            'receipt_footer' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'branch_id.required' => 'Cabang apotek wajib dipilih.',
            'name.required' => 'Nama apotek wajib diisi.',
            'logo.image' => 'Logo harus berupa file gambar.',
            'logo.mimes' => 'Logo harus berformat JPG, PNG, atau WEBP.',
            'logo.max' => 'Ukuran logo maksimal 2 MB.',
            'address.required' => 'Alamat lengkap apotek wajib diisi.',
            'latitude.required' => 'Silakan pilih lokasi apotek pada peta.',
            'longitude.required' => 'Silakan pilih lokasi apotek pada peta.',
        ];
    }
}
