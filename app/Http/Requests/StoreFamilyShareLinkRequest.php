<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFamilyShareLinkRequest extends FormRequest
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
			'relationship_type' => [
				'required',
				'string',
				Rule::in(['father', 'mother', 'parent', 'child', 'sibling', 'aunt', 'uncle', 'cousin']),
			],
		];
	}
}
