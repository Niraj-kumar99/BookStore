<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageUploadController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'document' => 'required|mimes:pdf,png,jpg|max:9999',
        ]);

        $base_location = 'bookstoreniraj-bucket';

        // Handle File Upload
        if($request->hasFile('document')) {              
            //Using store(), the filename will be hashed. You can use storeAs() to specify a name.
            //To specify the file visibility setting, you can update the config/filesystems.php s3 disk visibility key,
            //or you can specify the visibility of the file in the second parameter of the store() method like:
            //$documentPath = $request->file('document')->store($base_location, ['disk' => 's3', 'visibility' => 'public']);
            
            $documentPath = $request->file('document')->storeAs($base_location, 's3');
          
        } else {
            return response()->json(['success' => false, 'message' => 'No file uploaded'], 400);
        }
    
        //We save new path
        $image = new Image();
        $image->path = $documentPath;
        $image->name = $request->name;
        $image->save();
       
        return response()->json(['success' => true, 'message' => 'Document successfully uploaded', 'document' => new DocumentResource($image)], 200);
    }
}
