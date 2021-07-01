<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Mail;

class ContactMessagesController extends Controller
{
    public function store()
    {
        $contactMessage = ContactMessage::create($this->validateRequest());

        Mail::to(env('CONTACT_EMAIL'))->send(new ContactMail($contactMessage));

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
