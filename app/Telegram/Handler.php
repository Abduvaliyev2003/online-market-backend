<?php

namespace App\Telegram;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\TelegramAccounts;
use App\Models\User;
use DefStudio\Telegraph\Enums\ChatActions;
use Illuminate\Support\Str;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\ReplyButton;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Stringable;
use Nette\Utils\Random;
use DefStudio\Telegraph\Keyboard\Keyboard;

class Handler  extends WebhookHandler
{  
     
    
    public function start():void
    {
        $this->typing();
        // $this->chat->message('Natog`ri formatda yozdingiz')->send();
        $user = $this->user($this->chat->chat_id);
     
        if($user == null){
            $this->register();
        } else 
        {
            $this->menu();
        }    
    }

    protected function handleUnknownCommand(Stringable $text):void
    {
        if($text->value() == '/start'){
            $this->reply('command exited');
        }
    }

    public function register(): void
    {
        $this->typing();
        $this->chat
            ->html('Please send your phone number for registration: +998 ** *** ** **')
            ->replyKeyboard(
                ReplyKeyboard::make()
                    ->buttons([ReplyButton::make('Send Phone Number')->requestContact(true)])
                    ->resize(true)
                    ->inputPlaceholder("Phone number ...")
            )
            ->send();
        // $this->setpage('register');
    }
    
      
    protected function handleChatMessage(Stringable $text): void
    {
        $this->typing();
        $contact = $this->message?->contact()?->phoneNumber() ?? null;
       
        if($text->value() === '⬅️ Главное меню') {
            $this->menu();
        } else {
            switch($this->getPage())
            {   
                case 'register':
                    if($contact  !== null || $contact  !== []){
                        $this->user_store($contact,  $this->chat->chat_id);
                        $this->menu();
                    }
                    elseif($text->value() !== '')
                    {
                        if ($this->regexPhoneNumber($text->value())) {
                            $this->user_store($text,  $this->chat->chat_id);
                            $this->menu();
                        } else {
                            $this->chat->message('Natog`ri formatda yozdingiz')->send();
                        }
                    }
                    break;
                case 'comment': 
                    if($text->value() !== '')
                    {   
                        $this->chat->message("😊 Спасибо за комментарий")->send();
                        $this->menu();
                    }
                    break;
                case 'setting':
                    if ($contact !== null && $contact !== []) {
                        $this->updatePhone($contact);
                    } else {
                        if ($this->regexPhoneNumber($text->value())) {
                            $this->updatePhone($text->value());
                        } else {
                            $this->chat->message('Natog`ri formatda yozdingiz')->send();
                        }
                    }
                    
                    break;
                default:
                    $this->menu();
            }
        }
    
    }
    

    public function menu($bol = false, $edit = false):void
    {
     
        $inlineKeyboard = Keyboard::make()
        ->row([
            Button::make('🛒 Начать заказ')->action('order'),
        ])
        ->row([
            Button::make('И о нас')->action('about'),
            Button::make('🛍 Мои заказы')->action('my_order'),
        ])
        ->row([
            Button::make('✍️ Оставить отзыв')->action('comment'),
            Button::make('⚙️ Настройки')->action('setting'),
        ]);
       
        $replyKeyboard = ReplyKeyboard::make()
        ->row([
            ReplyButton::make('⬅️ Главное меню'),
        ])->resize(true);
        if($bol == false){
            $messagid = Telegraph::message(' Оформим ваш заказ вместе? 🤗')
                ->replyKeyboard($replyKeyboard)
                ->send();
        }
        if($edit == true){
            $this->chat->edit($this->messageId)->message('Для заказа нажмите 🛒 Начать заказ')
            ->keyboard($inlineKeyboard)
            ->send();
        } else {
            Telegraph::message('Для заказа нажмите 🛒 Начать заказ')
            ->keyboard($inlineKeyboard)
            ->send();
        }
        
    }

    protected function user_store($phone, $chatid , $page = 'menu')
    {
        $user = User::where('phone', $phone)->first();
        
        if ($user == null) {
            $randomPassword = Str::random(8);

            $newUser = User::updateOrCreate(
                    ['phone' => $phone],
                    [
                     'password' => Hash::make($randomPassword),
                    ]
                );

            $role = Role::find(2);
            $newUser->roles()->sync([$role->id]);

            $newUser->telegramAccounts()->updateOrCreate(
                ['chat_id' => $chatid],
                [
                 'telegram_page' => $page,
                ]
            );
        } else {
            $user->telegramAccounts()->create([
                'chat_id' => $chatid,
                'telegram_page' => 'menu',
            ]);
        }
     
        return true;
    }

    public function order()
    {
        $categories =  Category::get();
       
        $keybord = [];
        foreach($categories as $category){
          $keybord[] =   Button::make($category->name)->action('category')->param('category_id', $category->id);
        }
        $keybord[] =  Button::make('⬅️ Главное меню')->action('menus');
        $this->chat->edit($this->messageId)->message('Выберите категорию.')
        ->keyboard(Keyboard::make()->buttons($keybord)->chunk(2))->send(); 
    } 

    public function category($cate = null): void 
    {
        $category_id = $this->data->get('category_id');
        $products = Product::where('category_id', $cate ?? $category_id)
                   ->select('id', 'title')
                   ->get();
        if($products !== []){
            foreach ($products as $product) {
                $keybord[] =   Button::make($product->title)->action('products')->param('product_id', $product->id);
            }
            $keybord[] =  Button::make('⬅️ Назад')->action('product_back');
            if($cate == null){
                $this->chat->edit($this->messageId)->message('Выберит продукт')
                    ->keyboard(Keyboard::make()->buttons($keybord)->chunk(2))->send(); 
            } else {
                Telegraph::deleteMessage($this->messageId)->send();
                $this->chat->message('Выберит продукт')
                    ->keyboard(Keyboard::make()->buttons($keybord)->chunk(2))->send(); 
            }
            
        } else {
            $this->chat->message('Пустой')->send();
        }
    }

    public function product_back()
    {
        $this->order();
    }

    public function menus():void
    {
        $this->menu(true , true);
    }
    
    public function setting($boll = false)
    {
       $this->typing();
       $this->setpage('setting');
        // Telegraph::deleteMessage($this->messageId)->send();
        $inlineKeyboard = Keyboard::make()
            ->row([
                Button::make('Телефон')->action('phone'),
            ])
            ->row([
                Button::make('⬅️ Главное меню')->action('menus')
            ]);
        $user = $this->user( $this->chat->chat_id);
       
        $phone = $user->phone;
        if($boll == false){
          $this->chat->edit($this->messageId)->html(
            "<b> Телефон:</b> $user->phone \n\n Выберите одно из следующих "
          )
          ->keyboard($inlineKeyboard)
          ->send();
        } else {
           $this->chat->html( "<b> Телефон:</b> $user->phone \n\n Выберите одно из следующих ")
           ->keyboard($inlineKeyboard)
           ->send();
        }
        
    }
    
    public function comment()
    {
        Telegraph::deleteMessage($this->messageId)->send();
        $this->setpage('comment');
        $this->chat->message("написать комментарий")->send();
    }
     
    public function products()
    {
        $product_id = $this->data->get('product_id');
        $product = Product::find($product_id);
        
        Telegraph::deleteMessage($this->messageId)->send();
        $inlineKeyboard = Keyboard::make()
        ->row([
            Button::make('➖')->action('minus'),
            Button::make('1')->action(''),
            Button::make('➕')->action('plus'),
        ])
        ->row([
            Button::make('🗑 Дабавыт карзино')->action('add_karzina')
        ])
        ->row([
            Button::make('⬅️ Назад')->action('back')->param('category_id',  $product->category_id)
        ]);
    
        $this->chat->edit($this->messageId)->html($product->title)
           ->photo('https://media.istockphoto.com/id/886884542/photo/pile-of-metal-rods.jpg?s=612x612&w=0&k=20&c=V5vZ--olClbcdR9QyYWzzqR3-uZbLWmKjaf9ZVwT4k0=')
           ->keyboard($inlineKeyboard)
           ->send();

    }
    

    public function back()
    {
        $category_id = $this->data->get('category_id');
       
        $this->category($category_id);
    }
    
    public function phone()
    {
        Telegraph::deleteMessage($this->messageId)->send();
        $this->chat
        ->html('Смена номера: +998 ** *** ** ** ')
        ->replyKeyboard(
            ReplyKeyboard::make()
                ->buttons([ReplyButton::make('Отправить номер телефона')->requestContact(true)])
                ->resize(true)
                ->inputPlaceholder("Phone number ...")
        )
        ->send();
    }
    private function user()
    {
        $user = User::with('telegramAccounts')
        ->whereHas('telegramAccounts', function($query)
        {
            $query->where('chat_id', intval($this->chat->chat_id));
        })->first();
      
        return $user ?? null;
    }
    


    private function setpage(string $page):void
    {
       $account = TelegramAccounts::where('chat_id', $this->chat->chat_id)->update([
            'telegram_page' => $page
       ]);
    }

    private function regexPhoneNumber($number)
    {
        $phone_number_validation_regex = "/^998([378]{2}|(9[013-57-9]))\d{7}$/";
        $boolen =  preg_match($phone_number_validation_regex, str_replace('+', '', preg_replace('/\s+/', '', $number)));
        return $boolen;
    }
    
    private function getPage()
    {
        $account = TelegramAccounts::where('chat_id', $this->chat->chat_id)->first()['telegram_page'] ?? 'register';

        return $account ?? 'register';
    }

    public function updatePhone($phone):void
    {   
        $user = $this->user();
     
        User::where('id', $user->id)->update([
            'phone' => $phone ?? '+998998784803'
        ]);
        $replyKeyboard = ReplyKeyboard::make()
        ->row([
            ReplyButton::make('⬅️ Главное меню'),
        ])->resize(true);
        $messagid = Telegraph::message('Номер изменился')
                ->replyKeyboard($replyKeyboard)
                ->send();
        $this->setting(true);
    }

    private function typing()
    {
        $this->chat->action(ChatActions::TYPING)->send();
    }

   
}


