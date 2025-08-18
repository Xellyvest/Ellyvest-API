<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Models;

use Illuminate\Http\UploadedFile;
use App\Contracts\Auth\HasTransactionPin;

class TransactionModelData
{
    private string $userId;
    private float $amount;
    private string $transactableId;
    private string $transactableType;
    private string $type;
    private string $status;
    public ?string $swap_from = null;
    public ?string $swap_to = null;
    private ?string $comment;
    private ?array $payment_method = null;
    protected UploadedFile|string|null $proof = null;

    public function setUserId(string $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getAmount(): float|null
    {
        return $this->amount;
    }

    public function setTransactableId(string $transactableId): self
    {
        $this->transactableId = $transactableId;
        return $this;
    }

    public function setTransactableType(string $transactableType): self
    {
        $this->transactableType = $transactableType;
        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getType(): string|null
    {
        return $this->type;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getComment(): string|null
    {
        return $this->comment;
    }


    public function setSwapFrom(?string $swap_from): self
    {
        $this->swap_from = $swap_from;
        return $this;
    }

    public function getSwapFrom(): string|null
    {
        return $this->swap_from;
    }

    public function setSwapTo(?string $swap_to): self
    {
        $this->swap_to = $swap_to;
        return $this;
    }

    public function getSwapTo(): string|null
    {
        return $this->swap_to;
    }

    public function setPaymentMethod(?array $paymentMethod): self
    {
        $this->payment_method = $paymentMethod;
        return $this;
    }

    public function getPaymentMethod(): ?array
    {
        return $this->payment_method;
    }

    public function setProof(UploadedFile|string|null $proof): self
    {
        $this->proof = $proof;
        return $this;
    }

    public function getProof(): UploadedFile|string|null
    {
        return $this->proof;
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'amount' => $this->amount,
            'transactable_id' => $this->transactableId,
            'transactable_type' => $this->transactableType,
            'type' => $this->type,
            'swap_from' => $this->swap_from,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'comment' => $this->comment,
        ];
    }
}
