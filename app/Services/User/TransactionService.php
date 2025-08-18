<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\User;
use App\Helpers\FileHelper;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\ExpectationFailedException;
use App\DataTransferObjects\Models\TransactionModelData;

class TransactionService
{
    
    public function create(TransactionModelData $data, User $user): Transaction
    {
        $proofPath = null;

        if ($data->getProof()) {
            $proofPath = $this->uploadFile($data->getProof(), 'transactions');
        }
    
        $transactionData = $data->toArray();
    
        if ($proofPath) {
            $transactionData['proof'] = $proofPath;
        }

        logger($proofPath);

        return Transaction::query()->create($transactionData)->refresh();
    }

    private function uploadFile(UploadedFile $file, string $directory): string
    {
        $path = $file->storeAs($directory, (uniqid() . '.' . $file->extension()));

        throw_if($path === false, ExpectationFailedException::class, 'File could not be uploade');

        $path = Storage::url($path);
        $path = FileHelper::saveFileAndReturnPath($file);

        return $path;
    }


    public function swap(TransactionModelData $data, User $user): Transaction
    {
        // Validate that the user has sufficient balance before swapping
        if ($user->wallet->getBalance($data->getSwapFrom()) < $data->getAmount()) {
            throw new \Exception("Insufficient balance in {$data->getSwapFrom()}.");
        }

        // Create a transaction record
        $transaction = $user->storeTransaction(
            $data->getAmount(),
            $user->wallet->id,
            'App/Models/Wallet',
            'transfer',
            'approved',
            $data->getComment(),
            $data->getSwapFrom(),
            $data->getSwapTo(),
            Carbon::parse(now())->format('Y-m-d H:i:s')
        );

        // Perform the transfer by debiting and crediting the respective wallets
        $user->wallet->debit($data->getAmount(), $data->getSwapFrom(), "Transfer to {$data->getSwapTo()}");
        $user->wallet->credit($data->getAmount(), $data->getSwapTo(), "Received from {$data->getSwapFrom()}");

        return $transaction;
    }

    public function cancel(Transaction $transaction, User $user): Transaction
    {
        return DB::transaction(function () use ($transaction) {
            $transaction->update([
                'status' => 'cancelled',
            ]);
    
            return $transaction->fresh(); // Return updated instance
        });
    }
}
