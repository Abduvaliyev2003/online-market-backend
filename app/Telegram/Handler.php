<?php

namespace App\Telegram;

use App\Models\User;
use DefStudio\Telegraph\Enums\ChatActions;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Keyboard\ReplyButton;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;
use Illuminate\Support\Stringable;


class Handler  extends WebhookHandler
{   
    public function start():void
    {
        $user = User::with('telegramAccounts')
                    ->whereHas('telegramAccounts', function($query)
                    {
                        $query->where('chat_id', $this->chatid());
                    })->first();
        // $this->chat->message(json_encode($user));   
        if($user == null){
            $this->register();
        }       
       

    }
    public function register(): void
    {
        $this->chat
            ->html('Please send your phone number for registration: +998 ** *** ** **')
            ->replyKeyboard(
                ReplyKeyboard::make()
                    ->buttons([ReplyButton::make('Send Phone Number')->requestContact(true)])
                    ->resize(true)
                    ->inputPlaceholder("Phone number ...")
            )
            ->send();
    }

  
    public function handleContact($contact) {

       
        $phoneNumber = $contact->getPhoneNumber();
        
        $this->chat->message($phoneNumber)->send();
      
    }
      
    protected function handleChatMessage(Stringable $text): void
    {
        $phone_number_validation_regex = "/^998([378]{2}|(9[013-57-9]))\d{7}$/";
     
        $telegram = $text->getPhoneNumber();
        $this->chat->message($telegram)->send();
        if ($text->value() === 'Register') {
            // User clicked "Register" button
            $this->register();
        } elseif (preg_match($phone_number_validation_regex, str_replace('+', '', preg_replace('/\s+/', '', $text)))) {
            // Valid phone number received
            // $this->handleRegistration($text->value());
        }
    }

    

    

    protected function handleUnknownCommand(Stringable $text):void
    {
        if($text->value() == '/start'){
            $this->reply('command exited');
        }
    }
}


