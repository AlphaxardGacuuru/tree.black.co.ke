<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFamilyRelationshipRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'family_tree_id' => ['required', 'uuid', 'exists:family_trees,id'],
            'related_user_id' => ['required', 'uuid', 'exists:users,id', Rule::notIn([$this->user()->id])],
            'relationship_type' => ['required', 'string', 'max:50'],
        ];
    }
}
