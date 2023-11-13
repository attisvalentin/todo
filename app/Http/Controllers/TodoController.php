<?php

namespace App\Http\Controllers;

use PDOException;
use App\Models\Todo;
use Illuminate\Http\Request;
use Mews\Purifier\Facades\Purifier;
use Illuminate\Support\Facades\Validator;

class TodoController extends Controller
{
    // all items handling, contains filtering, pagination and default list
    public function items(Request $request) {
        switch($request) {
            case $request->query->has('completed') or $request->query->has('name'):
                return $this->getItemsByNameStatus($request);
                break;
            case $request->query->has('page') && $request->query->has('per_page'):
                return $this->getPaginatedItems($request);
                break;
            default:
                return $this->getAllItems();
        }
    }

    
    private function getAllItems() {
        try
        {
            $items = Todo::take(25)->cursorPaginate(25);

            if($items->isEmpty())
            {
                return response()->json(['message' => 'No items found'], 404);
            }

            return response()->json($items);
        }
        catch(PDOException $error)
        {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }
    
    private function getItemsByNameStatus($request) {
        $completed = $request->query('completed');
        $name = $request->query('name');

        try
        {
            if($completed != null && !$name)
            {
                $items = Todo::where('completed', $completed)->get();
            } 
            else if ($completed == null && $name)
            {
                $items = Todo::where('name', 'LIKE', "%{$name}%")->get();
            } 
            else if ($completed != null && $name)
            {
                $items = Todo::where('completed', $completed)->Where('name', 'LIKE', "%{$name}%")->get();
            }
            else
            {
                return response()->json(['message' => 'No items found'], 404);
            }

            if($items->isEmpty() || $items == null)
            {
                return response()->json(['message' => 'No items found'], 404);
            }

            return response()->json($items);
        }
        catch(PDOException $error) 
        {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }
    
    private function getPaginatedItems($request) {
        $page = $request->query('page');
        $per_page = $request->query('per_page') ? $request->query('per_page') : 25;

        try
        {
            $items = Todo::simplePaginate($per_page, ['*'], 'page', $page);

            if($items->isEmpty())

            {
                return response()->json(['message' => 'No items found'], 404);
            }

            return response()->json($items);
        }
        catch(PDOException $error)
        {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    // get one item by id
    public function getItem($id) {
        try
        {
            $item = Todo::find($id);

            if($item == null)
            {
                return response()->json(['message' => 'Item not found'], 404);
            }

            return response()->json($item);
        }
        catch(PDOException $error)
        {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    // create new item
    public function create(Request $request) {
        $validateInput = $request->all();
        $validator = $this->validator($validateInput);

        //in purifier.php file the AutoFormat.AutoParagraph is setted to false.
        //This is because the purifier added <p> tags to the text.
        //if its needed to add <p> tags to the text, then the AutoFormat.AutoParagraph should be setted to true.
        $name = Purifier::clean($request->name); 
        $description = Purifier::clean($request->description);

        if (!empty($validator))
        {
            return response()->json([$validator], 403);
        }

        try
        {
            $item = new Todo();
            $item->name = $name;
            $item->description = $description;
            $item->save();

            return response()->json($item, 201);

        }
        catch(PDOException $error)
        {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    // update item
    public function update(Request $request, $id) {
        try
        {
            $item = Todo::find($id);
            if($item == null)
            {
                return response()->json(['message' => 'Item not found'], 404);
            }

            $name = Purifier::clean($request->name);
            $description = Purifier::clean($request->description);

            $validateInput = 
            [
                'name' => $name ? $name : $item->name,
                'description' => $description ? $description : $item->description
            ];

            $validator = $this->validator($validateInput);

            if (!empty($validator))
            {
                return response()->json([$validator], 403);
            }

            $item->name = $name;
            $item->description = $description;
            $item->completed = $request->completed;
            $item->save();

            return response()->json($item);

        }
        catch(PDOException $error)
        {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    // delete item
    public function delete($id) {
        try
        {
            $item = Todo::find($id);
            if($item == null)
            {
                return response()->json(['message' => 'Item not found'], 404);
            }

            $item->delete();

            return response()->json(['message' => 'Item deleted']);

        }
        catch(PDOException $error)
        {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    private function validator($validateInput) {
        $validaterules = 
        [
            'name' => 'required|max:80',
            'description' => 'max:750'
        ];

        $validator = Validator::make($validateInput, $validaterules);

        if ($validator->fails())
        {
            return $validator->errors();
        }
    }
}
