<?php

namespace App\Http\Controllers;

use App\Models\FAD;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Constraint\Count;
use Ramsey\Uuid\Type\Integer;

use function PHPUnit\Framework\isNull;

class AdminFADController extends Controller
{
    // input: adminID, shopID
    // output: all food with images link 
    public function getFoodsAndDrinksAdmin(Request $request)
    {
        $foods = array();
        $drinks = array();
        $sizes = array();
        $toppings = array();
        $adminId = $request->adminId;
        $shop = DB::table('shop')->where('SHOP_OWNER_ID', '=', $adminId)->first();
        /* Type của thức ăn là 
        food: 1
        drink: 2
        topping: 3
        size: 4 */
        if ($shop) {
            $foodsAndDrinks = FAD::where([
                ['SHOP_ID', '=', $shop->SHOP_ID],
                ['IS_DELETED', '=', 0]
            ])->with(['image'])->orderBy('CATEGORY', 'ASC')->get();

            $foodsAndDrinks = $foodsAndDrinks->transform(function ($item) {
                // Thêm cột image_url từ bảng liên quan
                $item->IMAGE_URL = $item->image ? $item->image->URL : null;

                return $item;
            });

            foreach ($foodsAndDrinks as $foodAndDrink) {
                if ($foodAndDrink->CATEGORY === 1) {
                    $foods[] = $foodAndDrink;
                } elseif ($foodAndDrink->CATEGORY === 2) {
                    $drinks[] = $foodAndDrink;
                } elseif ($foodAndDrink->CATEGORY === 3) {
                    $toppings[] = $foodAndDrink;
                } else {
                    $sizes[] = $foodAndDrink;
                }
            }
        }

        return response()->json(
            [
                'data' => is_null($shop) ? null : [
                    'foods' => $foods,
                    'drinks' => $drinks,
                    'toppings' => $toppings,
                    'sizes' => $sizes
                ],
                'message' => is_null($shop) ? "Not Found" : 'Successful',
                'statusCode' => is_null($shop) ? 404 : 200,
            ]
        );
    }

    public function addFAD(Request $request)
    {
        try {
            $validatedReq = $request->validate([
                'fad_name' => 'required|string|max:191',
                'category' => 'required|integer',
                'tag' => 'integer|nullable',
                'quantity' => 'required|integer',
                'fad_price' => 'required|integer',
                'image_link' => 'required|string',
                'shop_id' => 'required|integer',
                'id_parentoftopping' => 'integer|nullable',
                'id_parentofsize' => 'integer|nullable',
                'description' => 'string|nullable|max:500',
            ]);

            // add image 
            if (!is_null($request->image_link)) {
                $createImage = Image::create([
                    'URL' => $request->image_link,
                    'USER_ID' => $request->admin_id,
                ]);
            }


            $foodAndDrink = FAD::create([
                'FAD_NAME' => $validatedReq['fad_name'],
                'CATEGORY' => $validatedReq['category'],
                'TAG' => $validatedReq['tag'],
                'QUANTITY' => $validatedReq['quantity'],
                'FAD_PRICE' => $validatedReq['fad_price'],
                'IMAGE_ID' => $createImage['IMAGE_ID'],
                'SHOP_ID' => $validatedReq['shop_id'],
                'ID_PARENTFADOFTOPPING' => $validatedReq['id_parentoftopping'],
                'ID_PARENTFADOFSIZE' => $validatedReq['id_parentofsize'],
                'DESCRIPTION' => $validatedReq['description'],
                'IS_DELETED' => 0,
                'DATE' => \Carbon\Carbon::now()
            ]);

            return response()->json(
                [
                    'data' => $foodAndDrink,
                    'message' => 'FAD successfully created',
                    'statusCode' => 201,
                ]
            );
        } catch (ValidationException $e) {
            // Handle validation failure
            return response()->json(
                [
                    'errors' => $e->errors(),
                    'message' => 'Validation failed',
                    'statusCode' => 422,
                ],
                422
            );
        }
    }

    public function getFAD(Request $request, $id)
    {
        $foodAndDrink = FAD::where('FAD_ID', $id)->where('IS_DELETED', 0)->with(['image'])->first();

        if (is_null($foodAndDrink)) {
            return response()->json(
                [
                    'data' => null,
                    'message' => 'Not found',
                    'statusCode' => 404,
                ]
            );
        }

        $foodAndDrink->IMAGE_URL = $foodAndDrink->image ? $foodAndDrink->image->URL : null;

        $toppingFAD = FAD::where([
            ['ID_PARENTFADOFTOPPING', '=', $id],
            ['IS_DELETED', '=', 0]
        ])->with(['image'])->get();

        $toppingFAD = $toppingFAD->transform(function ($item) {
            $item->IMAGE_URL = $item->image ? $item->image->URL : null;

            return $item;
        });
        return response()->json(
            [
                'data' => [
                    'FAD' => $foodAndDrink,
                    'topping' => $toppingFAD
                ],
                'message' => 'Get FAD successfully',
                'statusCode' => 200,
            ]
        );
    }

    public function searchFAD(Request $request)
    {
        $textQuery = $request->textQuery;

        $fads = FAD::where([
            ["SHOP_ID", "=", $request->shopId],
            ["FAD_NAME", "LIKE", "%$textQuery%"],
            ["IS_DELETED", "=", 0]
        ])->with(['image'])->get();

        $fads = $fads->transform(function ($item) {
            // Thêm cột image_url từ bảng liên quan
            $item->IMAGE_URL = $item->image ? $item->image->URL : null;

            return $item;
        });
        return response()->json([
            'statusCode' => 200,
            'fads' => $fads
        ], 200);
    }

    public function updateFAD(Request $request, $id)
    {
        $foodAndDrink = FAD::where('FAD_ID', $id)->where('IS_DELETED', 0)->with(['image'])->first();
        if (empty(get_object_vars($foodAndDrink))) {
            return response()->json(
                [
                    'data' => null,
                    'message' => 'Not found or maybe had been deleted',
                    'statusCode' => 404,
                ]
            );
        }
        try {
            $this->validate($request, [
                'fad_name' => 'required|string|max:191',
                'category' => 'required|integer',
                'quantity' => 'required|integer',
                'tag' => 'integer|nullable',
                'fad_price' => 'required|numeric',
                'shop_id' => 'required|integer',
                'image_link' => 'required|string',
                'id_parentoftopping' => 'integer|nullable',
                'id_parentofsize' => 'integer|nullable',
                'description' => 'string|nullable',
            ]);

            if (!is_null($request->image_link)) {
                if (strcmp($request->image_link, $foodAndDrink->image->URL) === 0) {
                    $image_id = $foodAndDrink->image->IMAGE_ID;
                } else {
                    $createImage = Image::create([
                        'URL' => $request->image_link,
                        'USER_ID' => $request->admin_id,
                    ]);
                    $image_id = $createImage['IMAGE_ID'];
                }
            }


            $foodAndDrink->update([
                'FAD_NAME' => $request['fad_name'],
                'CATEGORY' => $request['category'],
                'QUANTITY' => $request['quantity'],
                'TAG' => $request['tag'],
                'FAD_PRICE' => $request['fad_price'],
                'SHOP_ID' => $request['shop_id'],
                'IMAGE_ID' => $image_id,
                'ID_PARENTFADOFTSIZE' => $request['id_parentofsize'],
                'ID_PARENTFADOFTOPPING' => $request['id_parentoftopping'],
                'DESCRIPTION' => $request['description']
            ]);

            return response()->json([
                "data" => $foodAndDrink,
                "message" => "Update FAD successfully",
                "statusCode" => 200,
            ], 200);
        } catch (ValidationException $e) {
            // Handle validation failure
            return response()->json(
                [
                    'errors' => $e->errors(),
                    'message' => 'Validation failed',
                    'statusCode' => 422,
                ],
                422
            );
        }
    }

    public function deleteFAD(Request $request, $id)
    {
        $foodAndDrink = FAD::where('FAD_ID', $id)->where('IS_DELETED', 0)->first();
        if (is_null($foodAndDrink)) {
            return response()->json(
                [
                    'data' => null,
                    'message' => 'Not found',
                    'statusCode' => 404,
                ]
            );
        }

        $foodAndDrink->IS_DELETED = 1;
        $foodAndDrink->save();

        return response()->json(
            [
                'data' => $foodAndDrink,
                'message' => 'Delete FAD Successfully',
                'statusCode' => 200,
            ]
        );
    }
}
