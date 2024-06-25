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
    public function getFoodsAndDrinksAdmin(Request $request)
    {
        $foods = array();
        $drinks = array();
        $adminId = $request->adminId;
        $shop = DB::table('shop')->where('SHOP_OWNER_ID', '=', $adminId)->first();
        // TYPE của thức ăn sẽ là DRINK và FOOD
        if ($shop) {
            $foodsAndDrinks = FAD::where([
                ['SHOP_ID', '=', $shop->SHOP_ID],
                ['IS_DELETED', '=', 0],
                ['FAD_TYPE', '<>', 'TOPPING']
            ])->orderBy('FAD_TYPE', 'ASC')->get();

            foreach ($foodsAndDrinks as $foodAndDrink) {
                if ($foodAndDrink->FAD_TYPE == 'DRINK') {
                    $drinks[] = $foodAndDrink;
                } elseif ($foodAndDrink->FAD_TYPE == 'FOOD') {
                    $foods[] = $foodAndDrink;
                }
            }
        }

        return response()->json(
            [
                'data' => is_null($shop) ? null : [
                    'foods' => $foods,
                    'drinks' => $drinks
                ],
                'message' => is_null($shop) ? "Not Found" : 'Successful',
                'statusCode' => 200,
            ]
        );
    }

    public function addFAD(Request $request)
    {
        try {
            $validatedReq = $request->validate([
                'fad_name' => 'required|string|max:191',
                'fad_type' => 'required|string|max:191',
                'fad_price' => 'required|integer',
                'image_id' => 'string|nullable',
                'shop_id' => 'required|integer',
                'id_parentoftopping' => 'integer|nullable',
                'description' => 'string|nullable|max:500',
            ]);

            $foodAndDrink = FAD::create([
                'FAD_NAME' => $validatedReq['fad_name'],
                'FAD_TYPE' => $validatedReq['fad_type'],
                'FAD_PRICE' => $validatedReq['fad_price'],
                'IMAGE_ID' => $validatedReq['image_id'],
                'SHOP_ID' => $validatedReq['shop_id'],
                'ID_PARENTFADOFTOPPING' => $validatedReq['id_parentoftopping'],
                'DESCRIPTION' => $validatedReq['description'],
                'IS_DELETED' => 0,
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
        $foodAndDrink = FAD::find($id)->where('IS_DELETED', 0);

        if (empty(get_object_vars($foodAndDrink)) || is_null($foodAndDrink)) {
            return response()->json(
                [
                    'data' => null,
                    'message' => 'Not found',
                    'statusCode' => 404,
                ]
            );
        }

        $toppingFAD = FAD::where([
            ['ID_PARENTFADOFTOPPING', '=', $id],
            ['IS_DELETED', '=', 0]
        ])->get();

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
        ])->get();
        $fadsImageId = $fads->pluck('IMAGE_ID');
        $images = Image::whereIn('IMAGE_ID', $fadsImageId)->get();

        return response()->json([
            'statusCode' => 200,
            'fads' => $fads,
            'fadsImage' => $images
        ], 200);
    }

    public function updateFAD(Request $request, $id)
    {
        $foodAndDrink = FAD::find($id)->where('IS_DELETED', 0);
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
                'fad_type' => 'required|string|max:191',
                'fad_price' => 'required|numeric',
                'shop_id' => 'integer',
                'image_id' => 'string|nullable',
                'id_parentoftopping' => 'integer|nullable',
                'description' => 'string|nullable',
            ]);

            $foodAndDrink->update([
                'FAD_NAME' => $request['fad_name'],
                'FAD_TYPE' => $request['fad_type'],
                'FAD_PRICE' => $request['fad_price'],
                'IMAGE_ID' => $request['image_id'],
                'SHOP_ID' => $request['shop_id'],
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
        $foodAndDrink = FAD::find($id)->where('IS_DELETED', 0)->first();
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
