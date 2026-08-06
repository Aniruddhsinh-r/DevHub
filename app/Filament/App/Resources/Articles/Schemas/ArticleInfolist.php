<?php

namespace App\Filament\App\Resources\Articles\Schemas;

use App\Models\Article;
use App\Models\Comment;
use App\Enums\ArticleStatus;
use App\Enums\UserRole;
use App\Notifications\CommentNotification;
use Filament\Actions\Action;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use App\Filament\App\Resources\Users\UserResource;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ArticleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([

                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'lg' => 3,
                        ])
                        ->schema([
                            Group::make([
                                TextEntry::make('title')
                                    ->size('lg')
                                    ->weight('bold'),
                                TextEntry::make('excerpt')
                                    ->color('gray')
                                    ->size('lg')
                                    ->extraAttributes(['class' => 'italic border-l-4 border-primary-400 pl-4'])
                                    ->columnSpanFull(),
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('user.name')
                                            ->badge()
                                            ->size('lg')
                                            ->url(fn ($record) => $record->user_id === auth()->id()
                                                ? null : (
                                                    $record->user->hasAnyRole([
                                                        UserRole::ADMIN->value,
                                                        UserRole::SUPERADMIN->value,
                                                    ])
                                                        ? null
                                                        : UserResource::getUrl('view', [
                                                            'record' => $record->user->uuid,
                                                        ])
                                                )
                                            )
                                            ->openUrlInNewTab(false),
                                        TextEntry::make('status')
                                            ->size('lg')
                                            ->badge()
                                            ->color(fn (ArticleStatus $state): string => match ($state) {
                                                ArticleStatus::DRAFT => 'warning',
                                                ArticleStatus::PUBLISHED => 'success',
                                                ArticleStatus::SCHEDULED => 'gray',
                                            }),
                                        TextEntry::make('category.name')
                                            ->label('Category')
                                            ->size('lg')
                                            ->badge()
                                            ->color('info'),
                                        TextEntry::make('views')
                                            ->label('Views')
                                            ->state(fn ($record) => $record->views()->count())
                                            ->numeric()
                                            ->icon('heroicon-m-eye'),
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan([
                                'default' => 1,
                                'lg' => 2,
                            ]),
                            ImageEntry::make('cover_path')
                                ->hiddenLabel()
                                ->maxWidth('300px')
                                ->height('200px')
                                ->extraImgAttributes(['class' => 'w-full h-full object-cover rounded-lg'])
                                ->placeholder('No cover image')
                                ->columnSpan([
                                    'default' => 1,
                                    'lg' => 1,
                                ]),
                        ]),
                        TextEntry::make('body')
                            ->label('Content')
                            ->size('lg'),
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 4,
                        ])
                        ->schema([
                            TextEntry::make('created_at')
                                ->label('Created At')
                                ->dateTime('d M Y, H:i')
                                ->placeholder('-'),

                            TextEntry::make('updated_at')
                                ->label('Updated At')
                                ->dateTime('d M Y, H:i')
                                ->placeholder('-'),

                            TextEntry::make('published_at')
                                ->label('Published At')
                                ->dateTime('d M Y, H:i')
                                ->placeholder('Not published yet'),

                            TextEntry::make('deleted_at')
                                ->label('Deleted At')
                                ->dateTime('d M Y, H:i')
                                ->color('danger')
                                ->visible(fn (Article $record): bool => $record->trashed()),
                        ])
                        ->columnSpanFull(),
                    ]),
                static::commentsSection(),
            ]);
    }

    protected static function commentsSection(): Section
    {
        return Section::make('Comments')
            ->columnSpanFull()
            ->visible(fn (?Article $record) =>
                auth()->user()?->hasRole(UserRole::AUTHOR)
                && $record->status === ArticleStatus::PUBLISHED
            )
            ->description(fn (Article $record) => $record->comments()->count() . ' comments')
            ->headerActions([
                Action::make('postComment')
                    ->label('Add Comment')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        Textarea::make('body')->label('Your comment')->required()->maxLength(5000)->rows(3),
                    ])
                    ->action(function (array $data, Article $record) {
                        if (! Auth::user()?->hasRole(UserRole::AUTHOR)) {
                            Notification::make()->title('Only authors can comment.')->warning()->send();
                            return;
                        }
                        if ($record->status !== ArticleStatus::PUBLISHED) {
                            Notification::make()->title('Comments are only allowed on published articles.')->warning()->send();
                            return;
                        }
                        $comment = Comment::create([
                            'user_id' => Auth::id(),
                            'article_id' => $record->id,
                            'parent_id' => null,
                            'body' => $data['body'],
                        ]);
                        if ($record->user_id !== Auth::id()) {
                            $record->user->notify(new CommentNotification($comment));
                        }
                        Notification::make()->title('Comment posted')->success()->send();
                    }),
            ])
            ->schema([
                RepeatableEntry::make('topLevelComments')
                    ->hiddenLabel()
                    ->columns(1)
                    ->extraAttributes(['class' => 'space-y-4'])
                    ->schema(static::commentEntrySchema(1))
                    ->columnSpanFull(),
            ]);
    }

    protected static function commentEntrySchema(int $level): array
    {
        return [
            Group::make([
                Grid::make(2)
                    ->schema([
                        TextEntry::make('user.name')
                            ->hiddenLabel()
                            ->icon('heroicon-m-user-circle')
                            ->iconColor('gray')
                            ->weight('bold')
                            ->size('sm')
                            ->columnSpan(1),
                        TextEntry::make('created_at')
                            ->hiddenLabel()
                            ->since()
                            ->color('gray')
                            ->size('xs')
                            ->columnSpan(1)
                            ->extraAttributes(['class' => 'text-right flex justify-end items-center']),
                    ])
                    ->columnSpanFull()
                    ->extraAttributes(['class' => '!gap-0 items-center -mb-2']),

                TextEntry::make('body')
                    ->hiddenLabel()
                    ->color('gray')
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'text-sm text-gray-200 !mt-0 !pt-0']),

                Actions::make([
                    Action::make('reply_' . $level)
                        ->label('Reply')
                        ->link()
                        ->size('xs')
                        ->icon('heroicon-m-arrow-uturn-left')
                        ->color('warning')
                        ->modalHeading(fn (Comment $record) => "Reply to {$record->user->name}")
                        ->form([
                            Textarea::make('body')->label('Your reply')->required()->maxLength(5000)->rows(3),
                        ])
                        ->action(function (Comment $record, array $data, \Livewire\Component $livewire) use ($level): void {
                            if (! Auth::user()?->hasRole(UserRole::AUTHOR)) {
                                Notification::make()->title('Only authors can reply.')->warning()->send();
                                return;
                            }
                            $body = $data['body'];

                            if ($level >= 3) {
                                $parentId = $record->parent_id;
                                $body = "@{$record->user->name} {$body}";
                            } else {
                                $parentId = $record->id;
                            }

                            $comment = Comment::create([
                                'user_id' => Auth::id(),
                                'article_id' => $record->article_id,
                                'parent_id' => $parentId,
                                'body' => $body,
                            ]);

                            if ($record->user_id !== Auth::id()) {
                                $record->user->notify(new CommentNotification($comment));
                            }

                            Notification::make()->title('Reply posted')->success()->send();
                            $livewire->dispatch('$refresh');
                        }),
                ])->extraAttributes(['class' => '!mt-0 !pt-0']),
            ])
                ->columnSpanFull()
                ->extraAttributes(['class' => 'p-2 rounded-lg border-b border-gray-200 dark:border-gray-800 !gap-y-1']),

            ...($level < 3 ? [
                RepeatableEntry::make('replies')
                    ->hiddenLabel()
                    ->columns(1)
                    ->schema(static::commentEntrySchema($level + 1))
                    ->extraAttributes(['class' => 'pl-4 sm:pl-6 border-l-2 border-gray-200 dark:border-gray-700 mt-2 space-y-3'])
                    ->visible(fn ($record) => $record->replies->isNotEmpty()),
            ] : []),
        ];
    }

}
