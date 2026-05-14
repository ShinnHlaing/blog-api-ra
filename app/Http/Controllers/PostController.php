<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use App\Http\Requests\Post\StoreRequest;
use App\Http\Requests\Post\UpdateRequest;
use App\Models\Post;
use App\Http\Resources\PostResource;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Post::query()->with(['category', 'user']); //Eager load category and user relationships

            $query->when($request->has('search'), function ($q) use ($request) {
                $searchKey = $request['search'];
                $q->where(function ($subQuery) use ($searchKey) {
                    $subQuery->where('title', 'like', '%' . $searchKey . '%')
                        ->orWhere('body', 'like', '%' . $searchKey . '%')
                        ->orWhereHas('category', function ($catQuery) use ($searchKey) {
                            $catQuery->where('name', 'like', '%' . $searchKey . '%');
                        })
                        ->orWhereHas('user', function ($userQuery) use ($searchKey) {
                            $userQuery->where('name', 'like', '%' . $searchKey . '%');
                        });
                });
            });

            $perPage = $request->get('per_page', 10);
            $posts = $query->paginate($perPage);

            return response()->json(['message' => 'Posts retrieved successfully', 'data' => PostResource::collection($posts), 'total' => $posts->total(), 'current_page' => $posts->currentPage(), 'last_page' => $posts->lastPage(), 'per_page' => $posts->perPage()], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'internal server error'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        try {
            $post = Post::create($request->validated());

            return response()->json(['message' => 'Post created successfully', 'data' => PostResource::make($post)], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'internal server error'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $post = Post::find($id);
            if (!$post) {
                return response()->json(['message' => 'Post not found'], 404);
            }
            return response()->json(['message' => 'Post retrieved successfully', 'data' => PostResource::make($post)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'internal server error'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, string $id)
    {
        try {
            $post = Post::find($id);
            if (!$post) {
                return response()->json(['message' => 'Post not found'], 404);
            }

            $post->update($request->validated());

            return response()->json(['message' => 'Post updated successfully', 'data' => PostResource::make($post)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'internal server error'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $post = Post::find($id);
            if (!$post) {
                return response()->json(['message' => 'Post not found'], 404);
            }

            $post->delete();

            return response()->json(['message' => 'Post deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'internal server error'], 500);
        }
    }
}
