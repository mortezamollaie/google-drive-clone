<?php

namespace App\Http\Requests;

use App\Models\File;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreFileRequest extends ParentIdBaseRequest
{
    protected function prepareForValidation()
    {
        $paths = array_filter($this->relative_paths ?? [], fn($f) => $f != null);

        $this->merge([
            'file_paths' => $paths,
            'folder_name' => $this->detectFolderName($paths)
        ]);
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'files.*' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    if (!$this->folder_name) {
                        /** @var $value \Illuminate\Http\UploadedFile */
                        $file = File::query()->where('name', $value->getClientOriginalName())
                            ->where('created_by', Auth::id())
                            ->where('parent_id', $this->parent_id)
                            ->whereNull('deleted_at')
                            ->exists();

                        if ($file) {
                            $fail('File "' . $value->getClientOriginalName() . '" already exists.');
                        }
                    }
                }
            ],
            'folder_name' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        /** @var $value \Illuminate\Http\UploadedFile */
                        $file = File::query()->where('name', $value)
                            ->where('created_by', Auth::id())
                            ->where('parent_id', $this->parent_id)
                            ->whereNull('deleted_at')
                            ->exists();

                        if ($file) {
                            $fail('Folder "' . $value . '" already exists.');
                        }
                    }
                }
            ]
        ]);
    }

    public function detectFolderName($paths)
    {
        if (!$paths) {
            return null;
        }

        $parts = explode("/", $paths[0]);

        return $parts[0];
    }
}
