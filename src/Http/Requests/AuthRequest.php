<?php


namespace ppeCore\dvtinh\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuthRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $req = $this->all();
//        \Illuminate\Support\Facades\Log::channel('single')->info('$req', [$req]);
//        \Illuminate\Support\Facades\Log::channel('single')->info('$this->method()', [$this->method()]);

        $rules = [
            'email' => 'string|email|required|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ];
        switch ($this->method()) {
            case 'POST':

                break;
        }
        return $rules;
    }


    protected function prepareForValidation()
    {
//        $userInfo = $this->input('userInfo');
//        $this->merge([
//            'id'      => $this->route('galleryId'),
//            'shop_id' => @$userInfo['shop']['id'],
//        ]);
//
//        if ($this->method() == 'POST' && $this->is('*/create-draft')) {
//            $this->merge([
//                'status' => 'draft'
//            ]);
//        }
    }


}