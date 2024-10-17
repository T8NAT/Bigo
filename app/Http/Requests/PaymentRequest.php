<?php

namespace App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class PaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' =>'required|numeric',
            'currency'=>'required|in:SAR,EGP,AED,QAR,OMR,BHD,KWD,USD,GBP,EUR',
            'cardNumber'=>'required',
            'cardholderName'=>'required',
            'cvv' => 'required|digits_between:3,4',
            'expiryDate' => 'required',
        ];
    }

    protected function failedValidation(Validator|\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = $validator->errors()->getMessages();
        if($this->is("api/*")) {

            throw new ValidationException($validator,

                response()->json(['success'=>false,'status'=>422,'error'=>$this->formatErrors($errors)],422),
            );
        }
        parent::failedValidation($validator);

    }

    function formatErrors(array $errors): array
    {
        $formattedErrors = [];
        foreach ($errors as $field => $messages) {
            $formattedErrors[] = [
                'field' => $field,
                'messages' => $messages,
            ];
        }
        return $formattedErrors;
    }
}
