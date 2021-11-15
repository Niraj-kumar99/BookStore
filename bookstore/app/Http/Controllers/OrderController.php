<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use App\Models\Book;
use Illuminate\Http\Request;
use App\Http\Requests\SendEmailRequest;
use Validator;
use JWTAuth;

class OrderController extends Controller
{

    public function placeOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Book_name' => 'required',
            'Quantity' => 'required',
            //'order_id' => $this->generateUniqueOrderId(),
        ]);
        if($validator->fails())
        {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $currentUser = JWTAuth::parseToken()->authenticate();
        if($currentUser)
        {
            $get_book = Book::where('Book_name', '=', $request->input('Book_name'))->first();
            if($get_book == '')
            {
                return response()->json(['message' => 'We Do not have this book in the store...'],401);
            }
            $get_quantity = Book::select('Quantity')
                ->where([['books.user_id','=',$currentUser->id], ['books.Book_name', '=', $request->input('Book_name')]])
                ->get();
            
            if($get_quantity < $request->input('Quantity'))
            {
                return response()->json(['message' => 'This much stock is unavailable for the book'],401);
            }
            //getting bookID
            $get_bookid = Book::select('id')
                ->where([['books.Book_name', '=', $request->input('Book_name')]])
                ->value('id');

            //getting addressID
            $get_addressid = Address::select('id')
                ->where([['user_id', '=', $currentUser->id]])
                ->value('id');

            //get book name..    
            $get_BookName = Book::select('Book_name')
                ->where('Book_name', '=', $request->input('Book_name'))
                ->value('Book_name');

            //get book author ...
            $get_BookAuthor = Book::select('Book_Author')
                ->where('Book_name', '=', $request->input('Book_name'))
                ->value('Book_Author');

            //get book price
            $get_price = Book::select('Price')
                ->where([['books.Book_name', '=', $request->input('Book_name')]])
                ->value('Price');
            //calculate total price
            $total_price = $request->input('Quantity') * $get_price;


            //echo $get_book;
            //echo $get_quantity;
            //echo $get_bookid;
            //echo $get_addressid;
            //echo $get_price;
            //echo $total_price;
            //echo $get_BookName;
            //echo $get_BookAuthor;
            
            $order = Order::create([
                'user_id' => $currentUser->id,
                'book_id' => $get_bookid,
                'address_id' => $get_addressid,
                'order_id' => $this->generateUniqueOrderId(),
            ]); 
            
            $sendEmail = new SendEmailRequest();
            $sendEmail->sendEmailToUser($currentUser->email,$order->order_id,$get_BookName,$get_BookAuthor,$request->input('Quantity'),$total_price);
            
            return response()->json([
                'message' => 'Order Successfully Placed...',
                'OrderId' => $order->order_id,
                'Total_Price' => $total_price,
                'message1' => 'Mail also sent to the user....',
            ], 201); 
        }
        
        return response()->json([
            'message' => 'Failed to place order...',
        ], 201);
    }

    
    //get unique orderId...
    public function generateUniqueOrderId()
    {
        do {
            $orderid = random_int(100000, 999999);
        } while (Order::where("order_id", "=", $orderid)->first());
  
        return $orderid;
    }

}
