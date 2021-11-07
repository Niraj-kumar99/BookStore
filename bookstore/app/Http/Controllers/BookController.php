<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Book;
use App\Models\User;
use JWTAuth;
use Auth;
use Exception;
use Validator;


class BookController extends Controller
{
    public function addBook(Request $request) {

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'Book_name' => 'required|string|between:2,100',
            'Book_Description' => 'required|string|between:5,2000',
            'Book_Author' => 'required|string|between:5,300',
            'Book_Image' => 'required|string|between:5,800',
            'Price' => 'required',
            'Quantity' => 'required',
            ]);

        if($validator->fails())
        {
            Log::info('minimun letters for title is 2 and for description is 5');
            return response()->json($validator->errors()->toJson(), 400);
        }

        try
        {
            $currentUser = JWTAuth::parseToken()->authenticate();

            if ($currentUser)
            {
                $user_id = User::select('id')
                    ->where([['usertype','=','admin'],['id','=',$currentUser->id]])
                    ->get();
            }
            if(count($user_id)==0)
            {
                return response()->json([
                    'message' => 'You are not a ADMIN....'
                ],404);
            }

            //check is book is alredy in table or not
            $book = Book::where('Book_name',$request->Book_name)->first();
            if($book)
            {
                return response()->json([
                    'message' => 'Book is already in store......'
                ],401);
            }
            //if the book is not is store then add it..
            $book = new Book;
            $book->Book_name = $request->input('Book_name');
            $book->Book_Description = $request->input('Book_Description');
            $book->Book_Author = $request->input('Book_Author');
            $book->Book_Image = $request->input('Book_Image');
            $book->Price = $request->input('Price');
            $book->Quantity = $request->input('Quantity');
            $book->user_id = $currentUser->id;
            $book->save();
            
        }
        catch(Exception $e) 
        {
            Log::info('book creation failed',['user_id'=>$currentUser,'book_id'=>$request->id]);
            return response()->json([
                'message' => 'Something went wrong ... Check Bearer Token..'
            ],201);
        }
        Log::info('book created',['user_id'=>$currentUser,'book_id'=>$request->id]);
            return response()->json([
                'message' => ' Created.......'
            ],201);
            
    }

    public function deleteBookByBookId(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if($validator->fails())
        {
            Log::info('bookId is a required field');
            return response()->json($validator->errors()->toJson(), 404);
        }

        $id = $request->input('id');
        $currentUser = JWTAuth::parseToken()->authenticate();

        if ($currentUser)
            {
                $user_id = User::select('id')
                    ->where([['usertype','=','admin'],['id','=',$currentUser->id]])
                    ->get();
            }

        if(count($user_id)==0)
        {
            return response()->json([
                'message' => 'You are not a ADMIN....'
            ],404);
        }

        $books = $currentUser->books()->find($id);

        if(!$books)
        {
            Log::info('book you are searching is not present for deletion..');
            return response()->json([
                'message' => 'no book found'
            ],400);
        }

        if($books->delete())
        {
            Log::info('book deleted',['admin_id'=>$currentUser,'book_id'=>$request->id]);
            return response()->json([
                'message' => ' deleted'
            ],201);
        }
    }

    
    public function updateBookByBookId(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'Book_name' => 'required|string|between:2,100',
            'Book_Description' => 'required|string|between:5,2000',
            'Book_Author' => 'required|string|between:5,300',
            'Book_Image' => 'required|string|between:5,800',
            'Price' => 'required',
        ]);
        if($validator->fails())
        {
            Log::info('Updation failed');
            return response()->json($validator->errors()->toJson(), 400);
        }
        try
        {
            $id = $request->input('id');
            $currentUser = JWTAuth::parseToken()->authenticate();
            if($currentUser)
            {
                $user_id = User::select('id')
                    ->where([['usertype','=','admin'],['id','=',$currentUser->id]])
                    ->get();
            }
            if(count($user_id)==0)
            {
                return response()->json([
                    'message' => 'You are not a ADMIN so u can not perform updation....'
                ],404);
            }
            $book = $currentUser->books()->find($id);

            if(!$book)
            {
                return response()->json([
                    'message' => 'Book not Found'
                ], 404);
            }

            $book->fill($request->all());

            if($book->save())
            {
                return response()->json([
                    'message' => 'Book updated Sucessfully'
                ], 201);
            }
        }
        catch(Exception $e)
        {
            return response()->json([
                'message' => 'Invalid authorization token'
            ], 404);
        }
    
    }

    public function addStockByBookId(Request $request) {
        $validator = Validator::make($request->all(), [
            'id'=>'required',
            'Quantity'=>'required|integer|min:1'
        ]);

        if($validator->fails())
        {
            return response()->json($validator->errors()->toJson(), 400);
        }

        try
        {
            $currentUser = JWTAuth::parseToken()->authenticate();
            if ($currentUser)
            {
                $user_id = User::select('id')
                    ->where([['usertype','=','admin'],['id','=',$currentUser->id]])
                    ->get();
            }
            if(count($user_id)==0)
            {
                return response()->json([
                    'message' => 'You are not a ADMIN....'
                ],404);
            }
            $book = Book::find($request->id);

            if(!$book)
            {
                return response()->json([
                    'message' => 'Could not found Book with that id'
                ], 404);
            }
            $book->Quantity += $request->Quantity;
            $book->save();
            return response()->json([
                'message' => 'Book Stock updated Successfully'
            ], 201);
        }
        catch(Exception $e)
        {
            return response()->json([
                'message' => 'Unable to update Stocks wrong Brearer Token...'
            ], 201);
        }
    }

    public function getAllBooks() {
        $book = Book::all();
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

}
