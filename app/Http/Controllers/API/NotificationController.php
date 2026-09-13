<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse { return response()->json(['message'=>'Notifications retrieved successfully.','data'=>$request->user()->notifications()->latest()->paginate(20),'unread_count'=>$request->user()->unreadNotifications()->count()]); }
    public function markRead(Request $request,string $notification): JsonResponse { $item=$request->user()->notifications()->findOrFail($notification); $item->markAsRead(); return response()->json(['message'=>'Notification marked as read.']); }
    public function markAllRead(Request $request): JsonResponse { $request->user()->unreadNotifications->markAsRead(); return response()->json(['message'=>'Notifications marked as read.']); }
}
