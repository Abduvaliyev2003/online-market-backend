<?php

namespace App\Telegram;

use App\Models\Category;
use App\Models\CommentModel;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentT;
use App\Models\Product;
use App\Models\Role;
use App\Models\TelegramAccounts;
use App\Models\User;
use App\Models\UserAddress;
use Carbon\Carbon;
use DefStudio\Telegraph\Enums\ChatActions;
use Illuminate\Support\Str;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\ReplyButton;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Stringable;
use DefStudio\Telegraph\Keyboard\Keyboard;

class Handler  extends WebhookHandler
{  
     
    public function start():void
    {
        $this->typing();
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
        $latitude = $this->message?->location()?->latitude() ?? "";
        $longitude = $this->message?->location()?->longitude() ?? "";
        if($text->value() === '⬅️ Главное меню') {
            Telegraph::deleteMessage($this->messageId - 1)->send();
            $this->menu();
        } else {
            switch($this->getPage())
            {   
                case 'register':
                    if( $contact  !== null || $contact  !== [])
                    {  
                        $this->user_store($contact,  $this->chat->chat_id);
                        $this->menu();
                    }
                    elseif($text->value() !== null){
                        $reg = $this->regexPhoneNumber($text);
                        if ($reg) {
                            $phone = $text->value();
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
                        $this->commentStore($text);
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
                case 'location': 
                    if($longitude !== "")
                    {
                        $address = $this->storeLocation($longitude, $latitude);
                        $this->chat->message($address['title'])->send();
                        $this->order($address);
                    } 
                    break;
                case 'next':
                    $e = PaymentT::where('title', $text)->first();
                    if("⬅️ Назад" == $text) {
                        Telegraph::deleteMessage($this->messageId - 2 )->send();
                        $this->karzina();
                    } elseif($e !== null)
                    {
                        $this->finish($e);
                    }
                    elseif($text == '🛒 Начать заказ')
                    {
                        $this->new_location();
                    }
                    break;
                default:
                    $this->menu();
            }
        }
    
    }
    
    public function menu($bol = false, $edit = false):void
    {
        $this->setpage('menu');
        $inlineKeyboard = Keyboard::make()
        ->row([
            Button::make('🛒 Начать заказ')->action('new_location'),
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

    public function order($address = null)
    {
        $categories =  Category::get();
        $user = $this->user();
  
        $keybord = [];
        foreach($categories as $category){
          $keybord[] =   Button::make($category->name)->action('category')->param('category_id', $category->id);
        }
        $keybord[] =  Button::make('⬅️ Главное меню')->action('menus');
        $keybord[] = Button::make('🗑  Карзино')->action('karzina_cate');
        if($address == null){
            $this->chat->edit($this->messageId)->message('Выберите категорию.')
            ->keyboard(Keyboard::make()->buttons($keybord)->chunk(2))->send(); 
        } else {
            $replyKeyboard = ReplyKeyboard::make()
            ->row([
                ReplyButton::make('⬅️ Главное меню'),
            ])->resize(true);
            $messagid = Telegraph::message('Оформим ваш заказ вместе? 🤗')
                        ->replyKeyboard($replyKeyboard)
                        ->send();
            $order = Order::create([
                'user_id' => $user->id,
                'telegram_id' =>  $this->chat->chat_id,
                'address_id' => $address['id'],
                'status' => 'start',
                'total_sum' => 0
            ]);
            $this->updateUser([
               'order_id' => $order->id
            ]);
            $this->chat->message('Выберите категорию.')
            ->keyboard(Keyboard::make()->buttons($keybord)->chunk(2))->send(); 
        }
        
    } 

    public function  new_location()
    {    
        $this->setpage('location');
        Telegraph::deleteMessage($this->messageId)->send();
        $this->chat
        ->html('Введите свой адрес')
        ->replyKeyboard(
            ReplyKeyboard::make()
                ->buttons(
                    [
                        ReplyButton::make('Выберите свое местоположение')->requestLocation(true),
                    ], [
                        ReplyButton::make('⬅️ Главное меню')
                    ])
                ->resize(true)
        )
        ->send();
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
            $keybord[] =  Button::make('🗑  Карзино')->action('karzina_cate');
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

    public function  commentStore($comment):void
    {
        $user = $this->user();
        $user->commentModels()->create([
           'comment' => $comment
        ]);
    }

    public function my_order()
    {
        $user = $this->user();
        Telegraph::deleteMessage($this->messageId)->send();
        $orders = Order::with(['orderItems'])
                ->where('user_id' , $user->id)
                ->where('status', 'end')
                ->get();
       
        if ($orders->isEmpty() ) {
            $this->chat->message("Мои заказы:")->send(); 
        } else {
            foreach ($orders as  $key => $value) {
                $orderItems = OrderItem::with('products')->where('order_id', $value['id'])->get();
                $text = "Мои заказы: " .  Carbon::parse($value['created_at'])->format('d/m/Y');
                $text .= "\n \n🗺 " . UserAddress::where('id', $value['address_id'])->first()['title'];
                foreach ( $orderItems as $val) {
                    $text .= "\n✔️ " . $val['products']['title'] . " " . $val['count'];
                }
                $text .= "\n\nТип оплаты: " . PaymentT::where('id', $value['payment_id'])->first()['title'];
                $text .= "\nТовары: " . number_format($value['total_sum']) .  " сум";
                $inlineKeyboard = Keyboard::make()->row([
                    Button::make('❌ Удалить')->action('userOrderDelete')->param('order', $value->id),
                ]);
                $this->chat->html($text)->keyboard($inlineKeyboard)->send();
            }        
        }         
        

        
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
        $this->setpage('product');
        $product = Product::find($product_id);
        $order = $this->getOrder();
        $orderItem  =  OrderItem::where('order_id',$order->id)->where('product_id', $product->id)->first();
        $item = null;
        if($orderItem == null)
        {
        $item  =  $product->orderItems()->create([
                'order_id' => $order->id,
                'count' => 1,
                'total_sum' =>  $product->price
            ]);
        } else {
           $count = $orderItem->count + 1;
           $price = $product->price * $count;
            $orderItem->update([
                'count' => $count,
                'total_sum' =>  $price
            ]);
        }
        
        Telegraph::deleteMessage($this->messageId)->send();
        $inlineKeyboard = Keyboard::make()
        ->row([
            Button::make('➖')->action('minus')->param('order_item-id', $item->id ?? $orderItem->id),
            Button::make('1')->action(''),
            Button::make('➕')->action('plus')->param('order_item-id', $item->id ?? $orderItem->id),
        ])
        ->row([
            Button::make('🗑 Дабавыт карзино')->action('add_karzina')->param('order_item-id', $item->id ?? $orderItem->id)
        ])
        ->row([
            Button::make('⬅️ Назад')->action('back')->param('category_id',  $product->category_id),
            Button::make('🗑  Карзино')->action('karzina')->param('orderItem_id',  $product->id)
        ]);
    
        $this->chat->edit($this->messageId)->html("Наименование товара: ". $product->title ."\nЦена:  ". $product->price . " сум" ."\nОписание: " . $product->desc)
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
     
    private function storeLocation($long, $lat)
    { 
       
        $api_key = '49167188-2e49-4d62-a9f2-a27752083ce6';
        $url = "https://geocode-maps.yandex.ru/1.x/?apikey={$api_key}&format=json&geocode={$long},{$lat}";
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        $user = $this->user();
        $address = '';
        if (!empty($data['response']['GeoObjectCollection']['featureMember'])) {
            $address = $data['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['text'];
        }
        $userAddress = UserAddress::where('user_id', $user->id)
                       ->where('long', $long)
                       ->where('lat', $lat)
                       ->first();
       
        if($userAddress !== null)   
        {
            return $userAddress;
        } else {
          
            $addresses = UserAddress::create([
                'long' => $long,
                'lat' => $lat,
                'user_id' => $user->id,
                'title' => $address
            ]);
            return $addresses;
        }
       
    }
    
    public function minus()
    {
        $order_item_id = $this->data->get('order_item-id');
        $orderItem = OrderItem::find($order_item_id);
        $product = Product::find($orderItem->product_id);
        $counter =  $orderItem->count == 1 ? $orderItem->count : $orderItem->count - 1 ;
       
        $this->updateCounter($orderItem, $counter, $product);
        $orderItem->update([
            'count' => $counter,
            'total_sum' => $product->price * $counter
        ]);
    }

    public function plus()
    {
        $order_item_id = $this->data->get('order_item-id');
        $orderItem = OrderItem::find($order_item_id);
        $product = Product::find($orderItem->product_id);
        $counter =   $orderItem->count + 1 ;
         $inlineKeyboard = Keyboard::make()
        ->row([
            Button::make('➖')->action('minus')->param('order_item-id', $orderItem->id),
            Button::make('1')->action(''),
            Button::make('➕')->action('plus')->param('order_item-id', $orderItem->id),
        ])
        ->row([
            Button::make('🗑 Дабавыт карзино')->action('add_karzina')->param('order_item-id', $orderItem->id)
        ])
        ->row([
            Button::make('⬅️ Назад')->action('back')->param('category_id',  $product->category_id),
            Button::make('🗑  Карзино')->action('karzina')->param('orderItem_id',  $product->id)
        ]);
        $this->updateCounter($orderItem, $counter, $product);
        $orderItem->update([
            'count' => $counter,
            'total_sum' => $product->price * $counter
        ]);
    }

    public function karzina($edit = false, $filter = null)
    {
        $user = $this->user();
        $order = Order::with('orderItems', 'orderItems.products')
                ->whereHas('orderItems', function ($query) {
                    $query->where('status', 'karzina');
                });
        if($filter !== null)  {
            $order = $order->where('id', $user->order_id)
                    ->where('user_id' , $user->id)->first();
        } else {
            $order =  $order->where('id', $user->order_id)->where('user_id' , $user->id)->latest()->first();
        }
        if($order !== null && $order !== [])
        {
            $inlineKey = [];
            $text = "Корзина: ";
          
            foreach ($order['orderItems'] as $orderItem) {
                // $this->chat->message(json_encode($orderItem))->send();
                $text .= "\n" . $orderItem['count'] . " " .  $orderItem['products']['title'] . " "  . $orderItem['total_sum'] . " sum";
                $inlineKey = array_merge($inlineKey, $this->lineKeyb($orderItem));
            }
            $inlineKey[] = 

            $inlineKeyboard = Keyboard::make()->buttons($inlineKey)->chunk(3)->row([
                Button::make('⬅️ Назад')->action('order'),
                Button::make('❌ Удалить все')->action('delete')->param('order', $order->id),
                Button::make("➡️ Продолжать")->action('next')->param('next', $order->id)
            ]);
               
            $text .= "\n Итого:" . $order->total_sum . " sum";
            if($edit){
                $this->chat->edit($this->messageId)->html($text)->keyboard($inlineKeyboard)->send();
              
            } else  
            {
                Telegraph::deleteMessage($this->messageId)->send();
                $replyKeyboard = ReplyKeyboard::make()
                ->row([
                    ReplyButton::make('⬅️ Главное меню'),
                ])->resize(true);
                $this->chat->html('Оформим ваш заказ вместе?')->replyKeyboard($replyKeyboard)->send();
                $this->chat->html($text)->keyboard($inlineKeyboard)->send();
             
            }   
        } else 
        {    
            Telegraph::deleteMessage($this->messageId)->send();
            $inlineKeyboard = Keyboard::make()->row([
                Button::make('⬅️ Назад')->action('order'),
            ]);
            $this->chat->message('Карзина пуста')->keyboard($inlineKeyboard)->send();
        }
    }
    
    public function karzina_cate()
    {
        $this->karzina(true);
    }

    public function add_karzina()
    {
        $order_item_id = $this->data->get('order_item-id');
        $orderItem = OrderItem::find($order_item_id);
        $product = Product::find($orderItem->product_id) ?? null;
        $order = Order::find($orderItem->order_id);
        
        $this->category($product->category_id);
        $price = $orderItem->total_sum + $order->total_sum;
        $order->update([
           'total_sum' => $price
        ]);
        $orderItem->update([
           'status' => 'karzina'
        ]);
        Telegraph::deleteMessage($this->messageId)->send();
    }
    
      public function next()
    {
        $user = $this->user();
      
        $order = Order::where('id',$user->order_id)->with('userAdresses')->first();
        $orderItem = OrderItem::where('order_id', $order->id)->with('products')->get();
        $text = '🛍 Ваш  продукт сегодня' ;
        $text .= "\n🗺 ". $order?->user_adresses?->title . PHP_EOL;
        foreach($orderItem as $value){
            $text .= "\n✔️ " . $value['products']['title'] . " " . $value['count'];
        }
        $inlineKeyboard = Keyboard::make()->row([
            Button::make('⬅️ Назад')->action('karzina'),
           
            Button::make("✅ Подтверждение")->action('yes')->param('next', $order->id)
        ]);
        $text .= "\n💵 Общая сумма: ". number_format($order->total_sum) . ' сум' .PHP_EOL;
        $this->chat->edit($this->messageId)
            ->html($text)
            ->keyboard($inlineKeyboard)->send();
    }

    public function yes()
    {   
        Telegraph::deleteMessage($this->messageId)->send();
        $this->setpage('next');
        $text = "
        Пожалуйста, выберите тип оплаты
        ";
        $payment = PaymentT::get();
        $key = [];
        foreach($payment as $value){
           $key[] =  ReplyButton::make($value['title']);
        }

        $key[] = ReplyButton::make('⬅️ Назад');
           
        $key[] =      ReplyButton::make('⬅️ Главное меню');
        $replyKeyboard = ReplyKeyboard::make()
        ->buttons(
            $key
        )->resize(true);

        $this->chat->message($text)->replyKeyboard($replyKeyboard)->send();
    }

    public function finish($payment)
    {
        $user = $this->user();
        $orderI = Order::where('id' , $user->order_id)->update([
           'status' => 'end',
           'payment_id' => $payment['id']
        ]);
        $replyKeyboard = ReplyKeyboard::make()
        ->row([
            ReplyButton::make('⬅️ Главное меню'),
            ReplyButton::make('🛒 Начать заказ'),
        ])->resize(true);
        $this->chat->html('Оформим ваш заказ вместе?')->replyKeyboard($replyKeyboard)->send();
        $order = Order::where('id',$user->order_id)->with('userAdresses')->first();
        $address = UserAddress::find($order['address_id']);
        $orderItem = OrderItem::where('order_id', $order->id)->with('products')->get();
        $text = 'Номер заказа:' . rand(123, 1232) ;
        $text .= "\nСтатус: Подтвержден";
        $text .= "\nАдрес: " . $address['title'];
        $text .= "\n🗺 ". $order?->user_adresses?->title . PHP_EOL;
        foreach($orderItem as $value){
            $text .= "\n✔️ " . $value['products']['title'] . " " . $value['count'];
        }
        $text .= "\n\nТип оплаты: " . $payment['title'];
        $text .= "\nТовары: " . number_format($order['total_sum']) .  " сум";
        $text .= "\nИтого:  " .  number_format($order['total_sum']) .  " сум";

        $this->chat->html($text)->replyKeyboard($replyKeyboard)->send();
        OrderItem::where('order_id' , $order->id)->update([
            'status' => 'end'
        ]);
    }

    private function lineKeyb($orderItem)
    {
        $line = [
                $orderItem->count == 1 
                     ? Button::make('❌')->action('delete_once')->param('order_item-id', $orderItem->id)->width(0.5)
                     : Button::make('➖')->action('edit_minus')->param('order_item-id', $orderItem->id)->width(0.5),
                Button::make($orderItem->count)->action('')->width(0.5),
                Button::make('➕')->action('edit_plus')->param('order_item-id', $orderItem->id)->width(0.5), 
        ];

        return  $line;
    }
    
    public function edit_minus()
    {
        $order_item_id = $this->data->get('order_item-id');
        $orderItem = OrderItem::find($order_item_id);
        $product = Product::find($orderItem->product_id);
        $order = Order::find($orderItem->order_id);
        $counter =  $orderItem->count == 1 ? $orderItem->count : $orderItem->count - 1 ;
        
        if($orderItem->count !== 1){
            $price = $product?->price * $counter;
            $order->update([
               'total_sum' => $order->total_sum - $product?->price
            ]);
            $orderItem->update([
                'count' => $counter,
                'total_sum' =>$price
            ]);
        }
        $this->karzina(true, $order->id);
    }
    
    public function edit_plus()
    {
        $order_item_id = $this->data->get('order_item-id');
        $orderItem = OrderItem::find($order_item_id);
        $product = Product::find($orderItem->product_id);
        $order = Order::find($orderItem->order_id);
        $counter =   $orderItem->count + 1 ;

        $price =$orderItem->total_sum  + $product->price;
        $order->update([
            'total_sum' => $order->total_sum + $product->price
        ]);
        $orderItem->update([
            'count' => $counter,
            'total_sum' => $price
        ]);

        $this->karzina(true, $order->id);
    }

    public function delete()
    {
        $order_item_id = $this->data->get('order');
        $order = Order::find($order_item_id);
        $orderItem = OrderItem::where('order_id' , $order_item_id)->delete();
        $order->update([
           'total_sum' => 0 
        ]);
        $this->order();
    }
    
    public function userOrderDelete()
    {  
        Telegraph::deleteMessage($this->messageId)->send();
        $order_item_id = $this->data->get('order');
        $order = Order::find($order_item_id);
        $orderItem = OrderItem::where('order_id' , $order_item_id)->delete();
        $order->delete();
    }
    public function delete_once()
    {    
        $order_item_id = $this->data->get('order_item-id');
        $orderItem = OrderItem::find($order_item_id);
        $order = Order::find($orderItem->order_id);
        $order->update([
            'total_sum' =>  $order->total_sum - $orderItem->total_sum
        ]);

        $orderItem->delete();

        $this->karzina(true, $order->id);
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

    public function updateCounter($orderItem, $counter, $product)
    {
        $inlineKeyboard = Keyboard::make()
        ->row([
            Button::make('➖')->action('minus')->param('order_item-id', $orderItem->id),
            Button::make($counter)->action(''),
            Button::make('➕')->action('plus')->param('order_item-id', $orderItem->id),
        ])
        ->row([
            Button::make('🗑 Дабавыт карзино')->action('add_karzina')->param('order_item-id', $orderItem->id)
        ])
        ->row([
            Button::make('⬅️ Назад')->action('back')->param('category_id',   $product->category_id),
            Button::make('🗑  Карзино')->action('karzina')->param('orderItem_id',  $product->id)
        ]);
        Telegraph::replaceKeyboard(
            messageId: $this->messageId, 
            newKeyboard: $inlineKeyboard
        )->send();
    }

    private function getOrder()
    {
        return Order::where('telegram_id', $this->chat->chat_id)->latest()->first() ?? null;
    } 
    
    private function updateUser($update):void
    {
        $user = $this->user();
        User::where('id', $user->id)->update($update);
    }

    private function typing()
    {
        $this->chat->action(ChatActions::TYPING)->send();
    }

      
}


