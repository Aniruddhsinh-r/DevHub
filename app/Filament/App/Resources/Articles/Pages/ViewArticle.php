<?php

namespace App\Filament\App\Resources\Articles\Pages;

use App\Filament\App\Resources\Articles\ArticleResource;
use App\Models\Article;
use App\Models\View;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewArticle extends ViewRecord
{
    protected static string $resource = ArticleResource::class;

    public function mount(int|string $record): void {
        parent::mount($record);

        $view = View::firstOrCreate([
            'user_id' => auth()->id(),
            'article_id' => $this->record->id,
        ]);

        if ($view->wasRecentlyCreated) {
            $this->record->increment('view_count');
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->successNotificationTitle('Article edited successfully.'),
            DeleteAction::make()->successNotificationTitle('Article deleted successfully.'),
            Action::make('like')
                ->label(function (Article $record) {
                    $liked = $record->likes()
                        ->where('user_id', auth()->id())
                        ->exists();

                    return $liked
                        ? 'Unlike ' . $record->likes()->count()
                        : 'Like ' . $record->likes()->count();
                })
                ->icon(function (Article $record) {
                    return $record->likes()
                        ->where('user_id', auth()->id())
                        ->exists()
                            ? 'heroicon-s-heart'
                            : 'heroicon-o-heart';
                })
                ->color(function (Article $record) {
                    return $record->likes()
                        ->where('user_id', auth()->id())
                        ->exists()
                            ? 'danger'
                            : 'gray';
                })
                ->action(function (Article $record) {
                    $like = $record->likes()
                        ->where('user_id', auth()->id())
                        ->first();

                    if ($like) {
                        $like->delete();
                    } else {
                        $record->likes()->create([
                            'user_id' => auth()->id(),
                        ]);
                    }
                })
                ->visible(fn (Article $record) => auth()->user()->can('like', $record))
                ->extraAttributes([
                    'class' => 'rounded-xl',
                ]),

            Action::make('bookmark')
                ->label(fn (Article $record) =>
                    $record->isBookmarkedByMe()
                        ? 'Saved'
                        : 'Bookmark'
                )
                ->icon(fn (Article $record) =>
                    $record->isBookmarkedByMe()
                        ? 'heroicon-s-bookmark'
                        : 'heroicon-o-bookmark'
                )
                ->color(fn (Article $record) =>
                    $record->isBookmarkedByMe()
                        ? 'warning'
                        : 'gray'
                )
                ->action(function (Article $record) {
                    $record->bookmarks()->toggle(auth()->id());
                })
                ->visible(fn (Article $record) => auth()->user()->can('bookmark', $record))
                ->extraAttributes([
                    'class' => 'rounded-xl',
                ]),
        ];
    }
}
