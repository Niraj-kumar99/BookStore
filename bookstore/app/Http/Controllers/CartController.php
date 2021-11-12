<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Cart;
use Illuminate\Support\Facades\Log;
use JWTAuth;
use Validator;

class CartController extends Controller
{
    public function addBookToCartByBookId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_id' => 'required|integer|min:1',
        ]);

        if($validator->fails())
        {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $currentUser = JWTAuth::parseToken()->authenticate();
        if ($currentUser)
        {
            $book_id = $request->input('book_id');
            
            $book_exist = Book::select('Quantity')->where([
                ['id','=',$book_id]
            ])->get();

            if(!$book_exist)
            {
                return response()->json([ 'message' => 'Book not Found'], 404);
            }
            else if (Book::find($book_id)->Quantity==0)
            {
                return response()->json([ 'message' => 'Book is in Out Of stock'], 404);
            }

            $book_cart = Cart::select('id')->where([
                ['status','=','cart'],
                ['book_id','=',$book_id],
                ['user_id','=',$currentUser ->id]
            ])->get();

            if(count($book_cart)!=0)
            {
                return response()->json([ 'message' => 'Book already exist in cart'], 404);
            }

            $cart = new Cart;
            $cart->book_id = $request->get('book_id');

            if($currentUser->carts()->save($cart))
            {
                return response()->json(['message' => 'Book added to Cart Sucessfully'], 201);
            }
            return response()->json(['message' => 'Book cannot be added to Cart'], 405);
        }
        return response()->json(['message' => 'Invalid authorization token'], 404);
    }

    public function deleteBookByCartId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if($validator->fails())
        {
            return response()->json($validator->errors()->toJson(), 400);
        }
        try
        {
            $id = $request->input('id');
            $currentUser = JWTAuth::parseToken()->authenticate();
            $book = $currentUser->carts()->find($id);
            if(!$book)
            {
                Log::error('Book Not Found',['id'=>$request->id]);
                return response()->json(['message' => 'Book not Found'], 404);
            }

            if($book->delete())
            {
                Log::info('book deleted',['user_id'=>$currentUser,'book_id'=>$request->id]);
                return response()->json(['message' => 'Book deleted Sucessfully'], 201);
            }
        }
        catch(Exception $e)
        {
            return response()->json(['message' => 'Invalid authorization token' ], 404);
        }
    }

    public function getAllBooksFromCart() {
        $book = Cart::all();
        if($book==[])
        {
            return response()->json([
                'message' => 'Books Unavailable.....'
            ], 201);
        }
        return response()->json([
            'books' => $book,
            'message' => 'All Books are here ......'
        ], 201);
    }
    
    //updating quantity with cart id
    public function updateBookQuantityInCart(Request $request) {
        $validator = Validator::make($request->all(), [
            'id'=>'required',
            'book_quantity'=>'required|integer|min:1'
        ]);
        if($validator->fails())
        {
            return response()->json($validator->errors()->toJson(), 400);
        }

        try
        {
            $currentUser = JWTAuth::parseToken()->authenticate();
            
            $cart = Cart::find($request->id);

            if(!$cart)
            {
                return response()->json([
                    'message' => 'Item Not found with this id'
                ], 404);
            }
            $cart->book_quantity += $request->book_quantity;
            $cart->save();
            return response()->json([
                'message' => 'Book Quantity updated Successfully'
            ], 201);
        }
        catch(Exception $e)
        {
            return response()->json([
                'message' => 'Unable to update Quantity wrong Brearer Token...'
            ], 201);
        }
    }
}
