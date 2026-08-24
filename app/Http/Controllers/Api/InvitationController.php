<?php

namespace App\Http\Controllers\Api;

use App\Actions\SendInvitation;
use App\Enums\ArticleStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InvitationController extends Controller
{
    public function store(Request $request,SendInvitation $sendInvitation)
    {
        if (! Auth::user()->hasPermissionTo('user.manage')) {
            return response()->json(['message' => 'You do not have permission to send invitations.'], 403);
        }

        $values = $request->validate([
            'email'    => ['required', 'email', 'min:10', 'max:255', Rule::unique('users', 'email')],
        ]);

        $existing = Invitation::where('email', $values['email'])
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Invitation already exist.',
                'invitation' => $existing,
            ], 409);
        }

        $invitation = $sendInvitation->handle($values['email']);

        return response()->json([
            'message' => 'invitation send successfully',
            'invitation' => $invitation
        ],200);
    }

    public function resend(Invitation $invitation,SendInvitation $sendInvitation)
    {
        if (! Auth::user()->hasPermissionTo('user.manage')) {
            return response()->json(['message' => 'You do not have permission to send invitations.'], 403);
        }

        if (User::onlyTrashed()->where('email', $invitation->email)->exists()) {
            return response()->json(['message' => 'This email is blocked.'], 422);
        }

        if ($invitation->status !== 'expired') {
            return response()->json(['message' => 'Only expired invitations can be resent.'], 422);
        }

        $invitation = $sendInvitation->handle($invitation->email);

        return response()->json([
            'message' => 'invitation resend successfully',
            'invitation' => $invitation,
        ], 200);
    }

    public function index(Request $request)
    {
        if (! Auth::user()->hasPermissionTo('user.manage')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $invitation = Invitation::query()
            ->when($request->search, function ($q) use ($request) {
                $search = $request->search;

                $q->where(function ($query) use ($search) {
                    $query
                        ->where('email', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 12));

        return response()->json($invitation);
    }

    public function show(Invitation $invitation)
    {
        if (! Auth::user()->hasPermissionTo('user.manage')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        return response()->json($invitation);
    }

    public function delete(Invitation $invitation)
    {
        if (! Auth::user()->hasPermissionTo('user.manage')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $invitation->delete();

        return response()->json(['message' => 'Invitation delete successfully.'],200);
    }
}
