<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Book;
use App\Models\Admin;
use JWTAuth;
use Auth;
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
            $book = new Book;
            $book->Book_name = $request->input('Book_name');
            $book->Book_Description = $request->input('Book_Description');
            $book->Book_Author = $request->input('Book_Author');
            $book->Book_Image = $request->input('Book_Image');
            $book->Price = $request->input('Price');
            $book->Quantity = $request->input('Quantity');

            $currentUser = JWTAuth::parseToken()->authenticate();
            $book->user_id = $currentUser->id;
            
            
            $book->save();

            $value = Cache::remember('books', 300, function () {
                return DB::table('books')->get();
        });
        }
        catch(FailCreationException $e)
        {
            return back()->withErrors("Invalid Validation");
        }   

        Log::info('note created',['user_id'=>$book->admin_id]);
        return response()->json([ 
        'message' => 'Book added successfully'
        ],201);
            
    }

    public function delete_BookId(Request $request) {
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

    /*
    public function updateBook(Request $request) {
        $validator = Validator::make($request->all(), [
            
        ]
    } */
}
