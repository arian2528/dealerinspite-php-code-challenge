<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;

class ContactMessagesController extends Controller
{
    public function store()
    {
        ContactMessage::create($this->validateRequest());

        return back()->with('msg-sent', 'Your ,message has been sent successfully');
    }

    protected function validateRequest()
    {
        return \request()->validate([
             'name'      => 'required|max:100',
             'phone'     => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:100',
             'email'     => 'required|email|unique:contact_messages|max:100',
             'msg'       => 'required'
         ]);
    }
}
