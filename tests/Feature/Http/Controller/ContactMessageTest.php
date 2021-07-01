<?php

namespace Tests\Feature\Http\Controller;

use App\Mail\ContactMail;
use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_the_home_index_page_is_rendered_correctly()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_saving_contact_message_into_db()
    {
        $this->withoutExceptionHandling();

        $this->post('/contactMessages', $this->getFakeParams());

        $this->assertCount(1, ContactMessage::all());
    }

    public function test_send_email_using_contact_mail_has_proper_contact_email()
    {
        $contactMessage = ContactMessage::create($this->getFakeParams());

        Mail::fake();

        Mail::to(env('CONTACT_EMAIL'))->send(new ContactMail($contactMessage));

        Mail::assertSent(ContactMail::class, function ($mail) {
           return $mail->hasTo(env('CONTACT_EMAIL'));
        });
    }

    public function test_mailable_content_contains_subject_and_data()
    {
        $this->withoutExceptionHandling();

        $params = $this->getFakeParams();

        $contactMessage = ContactMessage::create($params);

        $mailable = new ContactMail($contactMessage);

        $mailable->assertSeeInHtml($contactMessage->name);
        $mailable->assertSeeInHtml($contactMessage->phone);
        $mailable->assertSeeInHtml($contactMessage->email);
        $mailable->assertSeeInHtml($contactMessage->msg);
    }

    public function test_phone_is_not_required_in_form()
    {
        $params = $this->getFakeParams();
        $params['phone'] = '';

        $this->post('/contactMessages', $params);

        $this->assertCount(1, ContactMessage::all());
    }

    public function test_email_is_required_in_form()
    {
        $params = $this->getFakeParams();
        $params['email'] = '';

        $response = $this->post('/contactMessages', $params);

        $response->assertSessionHasErrors('email');
    }

    public function test_message_is_required_in_form()
    {
        $params = $this->getFakeParams();
        $params['msg'] = '';

        $response = $this->post('/contactMessages', $params);

        $response->assertSessionHasErrors('msg');
    }

    public function test_name_is_required_in_form()
    {
        $params = $this->getFakeParams();
        $params['name'] = '';

        $response = $this->post('/contactMessages', $params);

        $response->assertSessionHasErrors('name');
    }

    public function test_form_display_success_message_after_submit()
    {
        $params = $this->getFakeParams();

        $response = $this->post('/contactMessages', $params);

        $response->assertSessionHas('msg-sent');
    }

    protected function getFakeParams(): array
    {
        $faker = \Faker\Factory::create();

        return [
            'name'  => $faker->name(),
            'email' => 'emailtest@gmail.com',
            'phone' => '7867654334',
            'msg'   => $faker->paragraph(),
        ];
    }
}
