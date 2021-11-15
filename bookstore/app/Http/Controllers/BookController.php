<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Book;
use App\Models\User;
use JWTAuth;
use Auth;
use Exception;
use Validator;


class BookController extends Controller
{
    /*
     * Function add a new book with  
     * proper Book_name, Book_Description, Book_Author, Book_Image 
     * Book_Image will be stored in aws S3 bucket and bucket will generate 
     * a image link and that link will be stored in mysql database and admin bearer token
     * must be passed because only admin can add or remove books .
    */
    public function addBook(Request $request) {

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'Book_name' => 'required|string|between:2,100',
            'Book_Description' => 'required|string|between:5,2000',
            'Book_Author' => 'required|string|between:5,300',
            'Book_Image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'Price' => 'required',
            'Quantity' => 'required',
            ]);

        if($validator->fails())
        {
            Log::info('minimun letters for title is 2 and for description is 5');
            return response()->json($validator->errors()->toJson(), 400);
        }

        //try
        //{
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
            $imageName = time().'.'.$request->Book_Image->extension();  
            $path = Storage::disk('s3')->put('images', $request->Book_Image);
            $pathurl = Storage::disk('s3')->url($path);
            //if the book is not is store then add it..
            $book = new Book;
            $book->Book_name = $request->input('Book_name');
            $book->Book_Description = $request->input('Book_Description');
            $book->Book_Author = $request->input('Book_Author');
            $book->Book_Image = $pathurl;
            $book->Price = $request->input('Price');
            $book->Quantity = $request->input('Quantity');
            $book->user_id = $currentUser->id;
            $book->save();
            
        //}
        //catch(Exception $e) 
        //{
            //Log::info('book creation failed');
            //return response()->json([
                //'message' => 'Something went wrong ... Check Bearer Token..'
            //],201);
        //}
        Log::info('book created',['user_id'=>$currentUser,'book_id'=>$request->id]);
            return response()->json([
                'message' => ' Created.......'
            ],201);
            
    }

    public function updateBookByBookId(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'Book_name' => 'required|string|between:2,100',
            'Book_Description' => 'required|string|between:5,2000',
            'Book_Author' => 'required|string|between:5,300',
            'Book_Image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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
            
            if($request->Book_Image)
            {
                $path = str_replace(env('AWS_URL_PATH'),'',$book->Book_Image);
            
                if(Storage::disk('s3')->exists($path)) {
                    Storage::disk('s3')->delete($path);
                }
                $path = Storage::disk('s3')->put('images', $request->Book_Image);
                $pathurl = Storage::disk('s3')->url($path);
                $book->Book_Image = $pathurl;
                }

            $book->fill($request->except('Book_Image'));
            if($book->save())
            {
                return response()->json([
                    'message' => 'Book updation Done....'
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

    public function deleteBookByBookId(Request $request)
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
            $currentAdmin = JWTAuth::parseToken()->authenticate();
            
            $admin = User::select('id')->where([
                ['usertype','=','admin'],
                ['id','=',$currentAdmin->id]
            ])->get();
                
            if(count($admin)==0)
            {
                return response()->json(['message' => 'Unauthorised'], 403);
            }

            $book_id = Book::find($request->id);

            if(!$book_id)
            {
                return response()->json(['message' => 'Book not Found'], 404);
            }

            $path = str_replace(env('AWS_URL_PATH'),'',$book_id->Book_Image);
            
            if(Storage::disk('s3')->exists($path)) {
                Storage::disk('s3')->delete($path);
                if($book_id->delete())
                {
                    return response()->json(['message' => 'Book deleted Sucessfully'], 201);
                }
            }
            return response()->json(['message' => 'File image was not deleted'], 402);   
        }
        catch(Exception $e)
        {
            return response()->json(['message' => 'Invalid authorization token' ], 404);
        }
    }

    public function searchEnteredKeyWord(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'search' => 'required|string'
        ]);

        if($validator->fails())
        {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $searchKey = $request->input('search');
        $currentUser = JWTAuth::parseToken()->authenticate();

        if ($currentUser) 
        {
            $userbooks = Book::leftJoin('carts', 'carts.book_id', '=', 'books.id')
            ->select('books.id','books.Book_name','books.Book_Description','books.Book_Author','books.Book_Image','books.Price','books.Quantity')
            ->Where('books.Book_name', 'like','%'.$searchKey.'%')
            ->orWhere('books.Book_Description', 'like','%'.$searchKey.'%')
            ->orWhere('books.Book_Author', 'like','%'.$searchKey.'%')
            ->orWhere('books.Price', 'like','%'.$searchKey.'%')
            ->get();

            if ($userbooks == '[]')
            {
                return response()->json(['message' => 'No results'], 404); 
            }
            return response()->json([
                'message' => 'Serch done Successfully',
                'books' => $userbooks
            ], 201);   
        }
        return response()->json(['message' => 'Invalid authorisation token'],403);
    }

    //Ascending order...
    public function sortOnPriceLowToHigh() {

        $currentUser = JWTAuth::parseToken()->authenticate();

        if ($currentUser)
        {
            $book = Book::orderBy('books.Price')
                ->get();
        }
        if($book=='[]')
        {
            return response()->json(['message' => 'Books not found'], 404);
        }
        return response()->json([
            'books' => $book,
            'message' => 'These much books are in store .....'
        ], 201);
    }
    //Descending order
    public function sortOnPriceHighToLow() {

        $currentUser = JWTAuth::parseToken()->authenticate();

        if ($currentUser)
        {
            $book = Book::orderBy('books.Price', 'desc')
                ->get();
        }
        if($book=='[]')
        {
            return response()->json(['message' => 'Books not found'], 404);
        }
        return response()->json([
            'books' => $book,
            'message' => 'These much books are in store .....'
        ], 201);
    }

    public function paginationBook()
    {
        $allBooks = Book::paginate(5); 

        return response()->json([
            'message' => 'Pagination aplied to all Books',
            'books' =>  $allBooks,
        ], 201);
    }
}
