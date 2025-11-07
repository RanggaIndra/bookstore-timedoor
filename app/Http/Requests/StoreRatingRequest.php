<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRatingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array {
        return [
            'author_id' => ['required','integer','exists:authors,id'],
            'book_id'   => ['required','integer',
                Rule::exists('books','id')->where(fn($q)=>$q->where('author_id',$this->input('author_id')))
            ],
            'score'     => ['required','integer','between:1,10'],
        ];
    }

    public function fingerprint(): string {
        $ip = $this->ip() ?? '0.0.0.0';
        $ua = substr((string)$this->header('User-Agent'),0,160);
        return sha1($ip.'|'.$ua);
    }

    public function messages(): array {
        return ['book_id.exists' => 'Kombinasi buku & penulis tidak valid.'];
    }
}
