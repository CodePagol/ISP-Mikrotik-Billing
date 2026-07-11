<?php

namespace App\Livewire;

use App\Models\CommentList;
use Livewire\Component;

class CommentSubmit extends Component
{
    public $name;

    public $mobile;

    public $email;

    public $comment;

    public $allComments;

    public $num1;

    public $num2;

    public $captcha;

    public function mount()
    {
        $this->generateCaptcha();
    }

    public function generateCaptcha()
    {
        $this->num1 = rand(1, 9);
        $this->num2 = rand(1, 9);
        $this->captcha = '';
    }

    public function render()
    {
        return view('livewire.comment-submit')->layout('layouts.app');
    }

    public function submitComment()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required_if:email,null|string|max:20|min:5',
            'email' => 'required_if:mobile,null|email|max:255',
            'comment' => 'required|string',
            'captcha' => ['required', 'integer', function ($attribute, $value, $fail) {
                if (intval($value) !== ($this->num1 + $this->num2)) {
                    $fail(__('The captcha answer is incorrect.'));
                }
            }],
        ]);

        // Save the comment to the database
        CommentList::create([
            'name' => $this->name,
            'phone' => $this->mobile,
            'email' => $this->email,
            'comment' => $this->comment,
            'ip_address' => request()->ip(),
        ]);

        // Reset the form fields
        $this->reset();

        // Regenerate captcha
        $this->generateCaptcha();

        // Optionally, you can emit an event or show a success message
        session()->flash('message', 'Comment submitted successfully!');
    }

    // public function showComments()
    // {
    //     $this->allComments = CommentList::latest()->take(25)->get();
    // }
}
