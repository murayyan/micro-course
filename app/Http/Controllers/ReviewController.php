<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\MyCourse;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function create(Request $request)
    {
        $rules = [
            'user_id' => 'required|integer',
            'course_id' => 'required|integer',
            'rating' => 'required|integer',
            'note' => 'string',
        ];

        $data = $request->all();

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $courseId = $request->input('course_id');
        $course = Course::find($courseId);
        if (!$course) {
            return response()->json([
                'status' => 'error',
                'message' => 'course not found'
            ], 404);
        }

        $userId = $request->input('user_id');
        $user = getUser($userId);
        if ($user['status'] === 'error') {
            return response()->json([
                'status' => $user['status'],
                'message' => $user['message']
            ], $user['http_code']);
        }

        $isTakenCourse = MyCourse::where('course_id', '=', $courseId)
            ->where('user_id', '=', $userId)
            ->exists();

        if (!$isTakenCourse) {
            return response()->json([
                'status' => 'error',
                'message' => 'user isn\'t registered in this course'
            ], 409);
        }

        $isExistReview = Review::where('course_id', '=', $courseId)
            ->where('user_id', '=', $userId)
            ->exists();

        if ($isExistReview) {
            return response()->json([
                'status' => 'error',
                'message' => 'user already review this course'
            ], 409);
        }

        $review = Review::create($data);
        return response()->json(['status' => 'success', 'data' => $review]);
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'rating' => 'integer',
            'note' => 'string',
        ];

        $data = $request->except(['user_id', 'course_id']);

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'status' => 'error',
                'message' => 'review not found'
            ], 404);
        }

        $review->fill($data);
        $review->save();

        return response()->json(['status' => 'success', 'data' => $review]);
    }

    public function destroy($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'status' => 'error',
                'message' => 'review not found'
            ], 404);
        }

        $review->delete();

        return response()->json(['status' => 'success', 'data' => 'review deleted']);
    }
}
