<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Imports\ProductImport;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('ImportTemplate')
                ->label('Import Template')
                ->color('danger')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('attachment')
                        ->label('Upload Template Product')
                        ->required()
                        ->storeFiles()
                ])
                ->action(function (array $data) {
                    // Perbaiki path file
                    $file = storage_path('app/public/' . $data['attachment']);

                    try {
                        Excel::import(new ProductImport, $file);

                        Notification::make()
                            ->title('Product Import Success')
                            ->success()
                            ->send();
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Product Import Failed')
                            ->body($e->getMessage()) // Tambahkan pesan error agar tahu masalahnya
                            ->danger()
                            ->send();
                    }
                }),
            Action::make("Download Template")
                ->url(route('download-template'))
                ->icon('heroicon-s-arrow-down-tray')
                ->color('success'),
            CreateAction::make(),
        ];
    }
}
