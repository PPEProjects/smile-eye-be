<?php


namespace ppeCore\dvtinh\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use ppeCore\dvtinh\Models\User;

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
        $rules = [];
        if ($this->is('*/register')) {
            $req = $this->all();
            $rules = [
                'email'    => [
                    'email',
                    'required',
                    function ($attribute, $value, $fail) use ($req) {
                        if (User::where('email', '=', $value)->exists()) {
                            return $fail(__('ppe.email_already_exists'));
                        }
                    },
                ],
                'password' => 'required|min:8|confirmed',
                'name' => 'required',
            ];
        }
        if ($this->is('*/login')) {
            $rules = [
                'email' => 'required',
                'password' => 'required',
            ];
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