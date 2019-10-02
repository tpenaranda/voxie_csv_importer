<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'data' => 'array|present',
            'data.*.email' => 'email|max:191|nullable',
            'data.*.fb_messenger_id' => 'string|max:191|nullable',
            'data.*.first_name' => 'string|max:191|nullable',
            'data.*.last_name' => 'string|max:191|nullable',
            'data.*.phone' => 'string|max:191|required',
            'data.*.sticky_phone_number_id' => 'numeric|nullable',
            'data.*.team_id' => 'numeric|required',
            'data.*.time_zone' => 'string|max:191|nullable',
            'data.*.twitter_id' => 'string|max:191|nullable',
            'data.*.unsubscribed_status' => 'string|max:191|required',
        ]);

        return collect($request->json('data'))->map(function ($data) {
            $contact = Contact::create($data);

            $customAttributes = array_except($data, $contact->getFillable());

            $contact->customAttributes()->createMany(collect($customAttributes)->transform(function ($value, $key) {
                return compact('key', 'value');
            }));

            return $contact->load('customAttributes');
        });
    }
}
