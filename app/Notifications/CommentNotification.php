<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Comment $comment)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $isReply = ! is_null($this->comment->parent_id);

        return [
            'type' => $isReply ? 'reply' : 'comment',

            'message' => $isReply
                ? "{$this->comment->user->name} replied to your comment"
                : "{$this->comment->user->name} commented on your article",

            'article_id' => $this->comment->article_id,
            'comment_id' => $this->comment->id,
            'url' => route('filament.app.resources.articles.view', ['record' => $this->comment->article]).'#comment-'.$this->comment->id,
        ];
    }
}
