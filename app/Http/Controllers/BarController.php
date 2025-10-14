<?php

namespace App\Http\Controllers;

use App\Models\Bar;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BarController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    try {
      $bar = Bar::first();

      // ⚠️ Bar::first() renvoie soit un objet, soit null — pas une collection !
      if (!$bar) {
        $response = [
          'status' => 404,
          'message' => 'Aucun bar trouvé',
          'data' => null
        ];
        return response()->json($response, 404);
      }

      $response = [
        'status' => 200,
        'message' => 'Success',
        'data' => $bar
      ];

      return response()->json($response, 200);

    } catch (Exception $error) {
      $response = [
        'status' => 500,
        'message' => $error->getMessage(),
      ];
      return response()->json($response, 500);
    }
  }


  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    //
    try {
      if (Bar::exists()) {
        $response = [
          'status' => 400,
          'message' => 'Le bar existe déjà',
        ];

        return response()->json($response, 400);
      }

      $validated = Validator::make($request->all(), [
        'name' => 'required|string',
        'address' => 'required|string',
        'phone' => 'required|numeric',
        'description' => 'nullable|string',
      ]);

      if ($validated->fails()) {
        $response = [
          'status' => 422,
          'message' => 'Validation failed',
          'errors' => $validated->errors(),
        ];

        return response()->json($response, 422);
      }

      $bar = new Bar();
      $bar->name = $request->name;
      $bar->address = $request->address;
      $bar->phone = $request->phone;
      $bar->description = $request->description;
      $bar->save();

      $response = [
        'status' => 201,
        'message' => 'Bar créé avec succès',
        'data' => $bar,
      ];

      return response()->json($response, 201);
    } catch (Exception $error) {
      $response = [
        'status' => 500,
        'message' => $error->getMessage(),
      ];

      return response()->json($response, 500);
    }
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(string $id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request)
  {
    try {
      $bar = Bar::first();

      if (!$bar) {
        return response()->json([
          'status' => 404,
          'message' => 'Aucun bar trouvé à modifier.',
        ], 404);
      }

      $validated = Validator::make($request->all(), [
        'name' => 'sometimes|string',
        'address' => 'sometimes|string',
        'phone' => 'sometimes|string',
        'description' => 'nullable|string',
      ]);

      if ($validated->fails()) {
        return response()->json([
          'status' => 422,
          'message' => 'Validation échouée',
          'errors' => $validated->errors(),
        ], 422);
      }

      $bar->update($validated->validated());

      return response()->json([
        'status' => 200,
        'message' => 'Bar modifié avec succès',
        'data' => $bar,
      ], 200);

    } catch (Exception $error) {
      return response()->json([
        'status' => 500,
        'message' => $error->getMessage(),
      ], 500);
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    //
  }
}
